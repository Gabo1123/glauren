<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Chaquetas\Models\Contract;
use App\Modules\Chaquetas\Models\Quote;
use App\Modules\Chaquetas\Models\School;
use App\Modules\Chaquetas\Models\Setting;
use App\Modules\Chaquetas\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AppApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Storage::fake('public');
    }

    private function tokenFor(string $email, string $pass = 'secreto123'): string
    {
        return $this->postJson('/api/v1/login', ['email' => $email, 'password' => $pass, 'device' => 'iPhone de prueba'])
            ->assertOk()->json('token');
    }

    public function test_admin_login_and_sales_flow(): void
    {
        User::create(['name' => 'Gabo', 'email' => 'gabo@glauren.co', 'password' => bcrypt('secreto123'), 'role' => 'admin']);
        $this->postJson('/api/v1/login', ['email' => 'gabo@glauren.co', 'password' => 'mal'])->assertStatus(422);
        $this->getJson('/api/v1/admin/hoy')->assertStatus(401);

        $t = $this->tokenFor('gabo@glauren.co');
        $h = ['Authorization' => 'Bearer '.$t];
        $this->getJson('/api/v1/admin/yo', $h)->assertOk()->assertJsonPath('user.role', 'admin');
        $this->getJson('/api/v1/admin/catalogo', $h)->assertOk()->assertJsonCount(9, 'combos');

        $q = $this->postJson('/api/v1/admin/cotizaciones', ['customer_name' => 'Laura', 'customer_phone' => '3001234567', 'school' => 'Colegio Montreal', 'quantity' => 40, 'combo' => 2], $h)
            ->assertCreated()->assertJsonPath('unit_total', 342200)->json();
        $this->assertStringContainsString('wa.me/573001234567', $q['whatsapp_url']);

        $this->postJson("/api/v1/admin/cotizaciones/{$q['id']}/enviada", [], $h)->assertOk()->assertJsonPath('status', 'enviada');
        $this->putJson("/api/v1/admin/cotizaciones/{$q['id']}/cliente", ['customer_name' => 'Laura Gómez', 'customer_phone' => '3001234567', 'client' => ['decides' => 'La rectora']], $h)
            ->assertOk()->assertJsonPath('client.decides', 'La rectora');
        $this->putJson("/api/v1/admin/cotizaciones/{$q['id']}/cliente", ['customer_phone' => '1'], $h)->assertStatus(422);
        $this->postJson("/api/v1/admin/cotizaciones/{$q['id']}/seguimiento", ['follow_up_at' => today()->toDateString(), 'note' => 'Llamar a la rectora'], $h)->assertOk();
        $this->getJson('/api/v1/admin/hoy', $h)->assertOk()->assertJsonPath('counts.call_today', 1);
        $this->getJson('/api/v1/admin/cotizaciones?q=Montreal', $h)->assertOk()->assertJsonCount(1, 'data');

        $this->postJson("/api/v1/admin/cotizaciones/{$q['id']}/etapa", ['stage' => 'ganada', 'school_name' => 'Colegio Montreal'], $h)
            ->assertOk()->assertJsonPath('status', 'cerrado');
        $this->assertSame(1, School::count());

        // Un estudiante con token de admin no sirve en rutas de colegio
        $this->getJson('/api/v1/colegio/contratos', $h)->assertStatus(401);
        $this->postJson('/api/v1/admin/salir', [], $h)->assertOk();
        $this->getJson('/api/v1/admin/hoy', $h)->assertStatus(401);
    }

    public function test_admin_two_factor_on_app(): void
    {
        config(['services.whatsapp.token' => 'x', 'services.whatsapp.phone_id' => '1']);
        Setting::put('admin_2fa_enabled', '1');
        Setting::put('admin_2fa_phone', '3001112233');
        $sent = null;
        $this->mock(\App\Modules\Chaquetas\Services\WhatsAppService::class, function ($m) use (&$sent) {
            $m->shouldReceive('configured')->andReturn(true);
            $m->shouldReceive('normalize')->andReturn('573001112233');
            $m->shouldReceive('sendText')->andReturnUsing(function ($to, $msg) use (&$sent) { $sent = $msg; return ['ok' => true]; });
        });
        User::create(['name' => 'Gabo', 'email' => 'gabo@glauren.co', 'password' => bcrypt('secreto123'), 'role' => 'admin']);

        $r = $this->postJson('/api/v1/login', ['email' => 'gabo@glauren.co', 'password' => 'secreto123'])->assertOk()->assertJsonPath('needs_2fa', true)->json();
        preg_match('/\*(\d{6})\*/', $sent, $m);
        $this->postJson('/api/v1/login/verificar', ['challenge' => $r['challenge'], 'code' => '000000'])->assertStatus(422);
        $this->postJson('/api/v1/login/verificar', ['challenge' => $r['challenge'], 'code' => $m[1]])->assertOk()->assertJsonStructure(['token']);
    }

    public function test_measures_payments_and_photos(): void
    {
        $admin = User::create(['name' => 'Gabo', 'email' => 'gabo@glauren.co', 'password' => bcrypt('secreto123'), 'role' => 'admin']);
        $school = School::create(['name' => 'Colegio Sol', 'slug' => 'sol', 'active' => true, 'payment_mode' => 'estudiante']);
        $c = Contract::create(['school_id' => $school->id, 'code' => 'CTR-1', 'year' => 2027, 'production_status' => 'toma_medidas', 'price_per_unit' => 300000]);
        $s = Student::create(['contract_id' => $c->id, 'name' => 'Ana', 'document' => '111', 'payment_status' => 'en_revision', 'payment_proof' => 'x.jpg']);
        $h = ['Authorization' => 'Bearer '.$this->tokenFor('gabo@glauren.co')];

        $this->getJson('/api/v1/admin/contratos', $h)->assertOk()->assertJsonPath('data.0.measured', 0);
        $this->putJson("/api/v1/admin/contratos/{$c->id}/alumnos/{$s->id}/medidas", ['size' => 'm', 'chest' => 88.5, 'talle_length' => 42], $h)
            ->assertOk()->assertJsonPath('size', 'M')->assertJsonPath('measured', true);
        $this->postJson("/api/v1/admin/contratos/{$c->id}/alumnos", ['document' => '222', 'name' => 'Beto'], $h)->assertCreated();
        $this->postJson("/api/v1/admin/contratos/{$c->id}/alumnos", ['document' => '222', 'name' => 'Beto'], $h)->assertStatus(422);
        $this->post("/api/v1/admin/contratos/{$c->id}/fotos", ['photos' => [UploadedFile::fake()->image('a.jpg')], 'phase' => 'proceso'], $h + ['Accept' => 'application/json'])
            ->assertOk()->assertJsonCount(1, 'photos');
        $this->postJson("/api/v1/admin/contratos/{$c->id}/etapa", ['production_status' => 'en_produccion'], $h)->assertOk()->assertJsonPath('stage', 'en_produccion');

        $this->getJson('/api/v1/admin/pagos', $h)->assertOk()->assertJsonCount(1, 'students');
        $this->postJson("/api/v1/admin/pagos/{$s->id}/aprobar", [], $h)->assertOk()->assertJsonPath('payment_status', 'pagado');
        $this->postJson("/api/v1/admin/contratos/{$c->id}/anticipo", ['action' => 'pedir', 'amount' => 1000000], $h)->assertOk()->assertJsonPath('status', 'pendiente');
        $this->getJson('/api/v1/admin/cartera', $h)->assertOk()->assertJsonPath('rows.0.school', 'Colegio Sol');
    }

    public function test_school_and_student_apps(): void
    {
        Setting::put('contratista_cuenta', 'Bancolombia 123');
        Setting::put('pay_nequi', '3001234567');
        $user = User::create(['name' => 'Rectora', 'email' => 'rectora@sol.co', 'password' => bcrypt('secreto123'), 'role' => 'colegio']);
        $school = School::create(['name' => 'Colegio Sol', 'slug' => 'sol', 'user_id' => $user->id, 'active' => true, 'payment_mode' => 'estudiante']);
        $c = Contract::create(['school_id' => $school->id, 'code' => 'CTR-2', 'year' => 2027, 'production_status' => 'aprobado', 'contract_status' => 'enviado',
            'terms' => 'x', 'deposit_amount' => 2000000, 'price_per_unit' => 300000]);
        $other = Contract::create(['school_id' => School::create(['name' => 'Otro', 'slug' => 'otro', 'active' => true, 'payment_mode' => 'estudiante'])->id, 'code' => 'CTR-3', 'year' => 2027, 'production_status' => 'aprobado']);

        $h = ['Authorization' => 'Bearer '.$this->tokenFor('rectora@sol.co')];
        $this->getJson('/api/v1/colegio/contratos', $h)->assertOk()->assertJsonPath('school', 'Colegio Sol');
        $this->getJson("/api/v1/colegio/contratos/{$other->id}", $h)->assertStatus(403);
        $this->postJson("/api/v1/colegio/contratos/{$c->id}/alumnos", ['name' => 'Ana Ruiz', 'document' => '555', 'gender' => 'mujer'], $h)
            ->assertOk()->assertJsonPath('students.0.name', 'Ana Ruiz')->assertJsonPath('deposit.account', 'Bancolombia 123');
        $this->post("/api/v1/colegio/contratos/{$c->id}/anticipo", ['proof' => UploadedFile::fake()->image('pago.jpg')], $h + ['Accept' => 'application/json'])
            ->assertOk()->assertJsonPath('deposit.status', 'en_revision');

        // Estudiante: crea su PIN la primera vez
        $this->postJson('/api/v1/login/estudiante', ['document' => '555'])->assertOk()->assertJsonPath('needs_pin_create', true);
        $this->postJson('/api/v1/login/estudiante', ['document' => '555', 'pin' => '1234', 'pin2' => '9999'])->assertStatus(422);
        $tok = $this->postJson('/api/v1/login/estudiante', ['document' => '555', 'pin' => '1234', 'pin2' => '1234'])->assertOk()->json('token');
        $this->assertTrue(Hash::check('1234', Student::first()->pin));
        $this->postJson('/api/v1/login/estudiante', ['document' => '555'])->assertJsonPath('needs_pin', true);
        $this->postJson('/api/v1/login/estudiante', ['document' => '555', 'pin' => '0000'])->assertStatus(422);

        $e = ['Authorization' => 'Bearer '.$tok];
        $this->getJson('/api/v1/estudiante', $e)->assertOk()->assertJsonPath('school', 'Colegio Sol')->assertJsonPath('payment.methods.0.account', '3001234567');
        $this->postJson('/api/v1/estudiante/pago', ['method' => 'nequi'], $e)->assertStatus(422);
        $this->post('/api/v1/estudiante/pago', ['method' => 'nequi', 'proof' => UploadedFile::fake()->image('c.jpg')], $e + ['Accept' => 'application/json'])
            ->assertOk()->assertJsonPath('payment.status', 'en_revision');
        $this->getJson('/api/v1/admin/hoy', $e)->assertStatus(401);
    }
}
