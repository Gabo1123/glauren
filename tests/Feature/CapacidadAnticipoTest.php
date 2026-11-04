<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Chaquetas\Models\Contract;
use App\Modules\Chaquetas\Models\Quote;
use App\Modules\Chaquetas\Models\School;
use App\Modules\Chaquetas\Models\Setting;
use App\Modules\Chaquetas\Models\Student;
use App\Modules\Chaquetas\Support\ProductionCapacity;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CapacidadAnticipoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ValidateCsrfToken::class);
        Mail::fake();
    }

    protected function tearDown(): void
    {
        \App\Modules\Chaquetas\Models\Setting::flushCache();   // los ajustes se guardan en memoria
        parent::tearDown();
    }

    private function admin(): User
    {
        return User::create(['name' => 'Admin', 'email' => 'admin@test.co', 'password' => bcrypt('x'), 'role' => 'admin']);
    }

    private function contract(string $name, int $students, string $combo, $date, string $status = 'en_produccion'): Contract
    {
        $s = School::create(['name' => $name, 'slug' => \Str::slug($name), 'active' => true, 'payment_mode' => 'estudiante']);
        $c = Contract::create(['school_id' => $s->id, 'code' => 'CTR-'.$students, 'year' => 2027, 'production_status' => $status,
            'delivery_date' => $date, 'default_combo' => $combo, 'price_per_unit' => 300000]);
        for ($i = 0; $i < $students; $i++) {
            Student::create(['contract_id' => $c->id, 'name' => 'E'.$i, 'document' => $students.'0'.$i, 'combo' => $combo]);
        }

        return $c;
    }

    public function test_capacity_plan_warns_when_month_is_full(): void
    {
        Setting::put('monthly_capacity', '100');
        $month = now()->addMonth()->startOfMonth()->addDays(10);
        $a = $this->contract('Colegio Andino', 30, 'chaqueta_buzo', $month);          // 60 prendas
        $this->contract('Liceo Sur', 20, 'chaqueta', $month, 'cotizado');            // 20 tentativas

        $row = ProductionCapacity::plan(3)[$month->format('Y-m')];
        $this->assertSame(60, $row['confirmed']);
        $this->assertSame(20, $row['tentative']);
        $this->assertSame(20, $row['free']);

        $check = ProductionCapacity::check($month, 30);
        $this->assertTrue($check['over']);
        $this->assertSame(110, $check['after']);
        $this->assertNotNull($check['suggest']);
        // Excluye el propio contrato
        $this->assertFalse(ProductionCapacity::check($month, 60, $a->id)['over']);

        $admin = $this->admin();
        $this->actingAs($admin)->get(route('admin.capacity.index'))->assertOk()->assertSee('Colegio Andino · 60')->assertSee('80')->assertSee('20 libres');
        $this->actingAs($admin)->put(route('admin.capacity.save'), ['monthly_capacity' => 70, 'production_days' => 30, 'deposit_percent' => 40])->assertRedirect();
        $this->assertSame('70', Setting::get('monthly_capacity'));
        $this->actingAs($admin)->get(route('admin.contracts.show', $a))->assertOk()->assertSee('Ese mes está sobrevendido');
        $this->actingAs($admin)->get(route('admin.contracts.create'))->assertOk()->assertSee('Cupo del taller');

        $q = Quote::create(['customer_name' => 'Ana', 'customer_phone' => '3000000000', 'quantity' => 30, 'estimated_price' => 0, 'items' => ['combo' => 2]]);
        $this->assertSame(60, ProductionCapacity::quoteUnits($q));
        $this->actingAs($admin)->get(route('admin.quotes.show', $q))->assertOk()->assertSee('¿Hay cupo?');
    }

    public function test_school_deposit_flow(): void
    {
        Storage::fake('public');
        Setting::put('contratista_cuenta', 'Bancolombia ahorros 123-456');
        Setting::put('contract_template', 'Anticipo: {anticipo} ({anticipo_letras})');
        Setting::put('contact_notify_email', 'yo@glauren.co');
        $user = User::create(['name' => 'Rector', 'email' => 'r@t.co', 'password' => bcrypt('x'), 'role' => 'colegio']);
        $school = School::create(['name' => 'Colegio Sol', 'slug' => 'sol', 'user_id' => $user->id, 'active' => true, 'payment_mode' => 'estudiante']);
        $c = Contract::create(['school_id' => $school->id, 'code' => 'CTR-A', 'year' => 2027, 'production_status' => 'aprobado',
            'contract_status' => 'firmado', 'signed_at' => now(), 'terms' => 'Anticipo: {anticipo} ({anticipo_letras})', 'contract_total' => 10000000]);
        $admin = $this->admin();

        $this->actingAs($admin)->put(route('admin.contracts.deposit', $c), ['deposit_amount' => 3000000])->assertRedirect();
        $c->refresh();
        $this->assertSame('pendiente', $c->depositStatus());
        $this->assertStringContainsString('Tres millones de pesos', $c->renderedContract());

        $this->actingAs($user)->get(route('portal.contract', $c))->assertOk()->assertSee('Anticipo para iniciar la producción')->assertSee('Bancolombia ahorros 123-456')->assertSee('$3.000.000');
        $this->actingAs($user)->post(route('portal.deposit', $c), ['proof' => UploadedFile::fake()->image('pago.jpg')])->assertRedirect();
        $c->refresh();
        $this->assertSame('en_revision', $c->depositStatus());
        Mail::assertSent(\App\Modules\Chaquetas\Mail\SimpleMail::class, fn ($m) => $m->hasTo('yo@glauren.co'));

        $this->actingAs($admin)->get(route('admin.contracts.show', $c))->assertOk()->assertSee('Ver comprobante')->assertSee('Aprobar $3.000.000');
        $this->actingAs($admin)->get(route('admin.sales.cartera'))->assertOk()->assertSee('en revisión');

        $this->actingAs($admin)->put(route('admin.contracts.deposit', $c), ['paid' => 3000000, 'method' => 'Transferencia'])->assertRedirect();
        $c->refresh();
        $this->assertSame('pagado', $c->depositStatus());
        $this->actingAs($user)->get(route('portal.contract', $c))->assertOk()->assertSee('Anticipo de $3.000.000 recibido');
        $this->actingAs($admin)->get(route('admin.sales.cartera'))->assertOk()->assertSee('incl. anticipo $3.000.000');
    }

    public function test_contract_from_quote_suggests_deposit(): void
    {
        Setting::put('deposit_percent', '30');
        $admin = $this->admin();
        $quote = Quote::create(['customer_name' => 'Laura', 'customer_phone' => '3001234567', 'school' => 'Colegio Montreal',
            'quantity' => 40, 'estimated_price' => 0, 'items' => ['combo' => 2, 'products' => [], 'extras' => ['forro']]]);
        // 362.200 × 40 = 14.488.000 → 30% = 4.346.400 → redondeado a mil
        $this->actingAs($admin)->get(route('admin.contracts.create', ['quote_id' => $quote->id]))->assertOk()
            ->assertSee('name="deposit_amount" id="depAmt" class="form-control" value="4346000"', false);
    }
}
