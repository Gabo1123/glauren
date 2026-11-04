<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Chaquetas\Models\Contract;
use App\Modules\Chaquetas\Models\Quote;
use App\Modules\Chaquetas\Models\School;
use App\Modules\Chaquetas\Models\Setting;
use App\Modules\Chaquetas\Models\Student;
use App\Modules\Chaquetas\Models\Testimonial;
use App\Modules\Chaquetas\Support\DeliverySurvey;
use App\Modules\Chaquetas\Support\NumberToWords;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ProcesoVentasTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ValidateCsrfToken::class);
        Mail::fake();
    }

    private function admin(): User
    {
        return User::create(['name' => 'Admin', 'email' => 'admin@test.co', 'password' => bcrypt('x'), 'role' => 'admin']);
    }

    private function csv(string $body): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('lista.csv', "\xEF\xBB\xBF".$body);
    }

    public function test_sizes_template_and_upload_update_students_by_document(): void
    {
        $school = School::create(['name' => 'Colegio Sol', 'slug' => 'sol', 'active' => true, 'payment_mode' => 'estudiante']);
        $c = Contract::create(['school_id' => $school->id, 'code' => 'CTR-T', 'year' => 2027, 'production_status' => 'toma_medidas']);
        Student::create(['contract_id' => $c->id, 'name' => 'Ana Ruiz', 'document' => '1010101010']);
        $admin = $this->admin();

        $res = $this->actingAs($admin)->get(route('admin.students.sizes.template', $c))->assertOk();
        $this->assertStringContainsString('Documento;Nombre;Apodo;Sexo;Talla;Pecho (cm)', $res->getContent());
        $this->assertStringContainsString('1010101010;Ana Ruiz', $res->getContent());

        $file = $this->csv("Documento;Nombre;Apodo;Sexo;Talla;Pecho (cm);Cintura (cm);Cadera (cm);Manga (cm);Talle (cm)\n"
            ."1.010.101.010;Ana Ruiz;Anita;F;m;88,5;70;94;58;42\n"
            ."2020202020;Juan Torres;;M;L;100;;;;\n"
            ."3030303030;;;;S;;;;;\n");
        $this->actingAs($admin)->post(route('admin.students.sizes.import', $c), ['file' => $file])
            ->assertRedirect()->assertSessionHas('ok', fn ($m) => str_contains($m, '1 alumno(s) actualizado(s), 1 nuevo(s)') && str_contains($m, '3030303030'));

        $ana = Student::where('document', '1010101010')->first();
        $this->assertSame('M', $ana->size);
        $this->assertEquals(88.5, (float) $ana->chest);
        $this->assertEquals(42, (float) $ana->talle_length);
        $this->assertSame('mujer', $ana->gender);
        $this->assertSame('Anita', $ana->nickname);
        $this->assertSame('L', Student::where('document', '2020202020')->first()->size);
        $this->actingAs($admin)->get(route('admin.contracts.show', $c))->assertOk()->assertSee('Formato de tallas');
    }

    public function test_school_uploads_student_list_from_excel(): void
    {
        $user = User::create(['name' => 'Rector', 'email' => 'r@t.co', 'password' => bcrypt('x'), 'role' => 'colegio']);
        $school = School::create(['name' => 'Colegio Sol', 'slug' => 'sol', 'user_id' => $user->id, 'active' => true, 'payment_mode' => 'estudiante']);
        $c = Contract::create(['school_id' => $school->id, 'code' => 'CTR-P', 'year' => 2027, 'production_status' => 'aprobado', 'contract_status' => 'enviado', 'terms' => 'x']);

        $this->actingAs($user)->get(route('portal.students.template', $c))->assertOk()->assertSee('Documento;Nombre completo;Apodo;Celular;Sexo (M/F)', false);
        $this->actingAs($user)->get(route('portal.contract', $c))->assertOk()->assertSee('Subir lista desde Excel');

        $file = $this->csv("Documento,Nombre completo,Apodo,Celular,Sexo\n111111,VALENTINA RÍOS,Vale,300 123 4567,F\n222222,Juan Pérez,,,m\n111111,Repetida,,,\n,Sin documento,,,\n");
        $this->actingAs($user)->post(route('portal.students.import', $c), ['file' => $file])->assertRedirect()
            ->assertSessionHas('ok', fn ($m) => str_contains($m, '2 alumno(s) agregado(s)') && str_contains($m, '1 ya estaban') && str_contains($m, 'filas 5'));
        $v = Student::where('document', '111111')->first();
        $this->assertSame('Valentina Ríos', $v->name);
        $this->assertSame('3001234567', $v->phone);
        $this->assertSame('mujer', $v->gender);

        $c->update(['contract_status' => 'firmado', 'signed_at' => now()]);
        $this->actingAs($user)->post(route('portal.students.import', $c), ['file' => $this->csv("documento;nombre\n333333;X\n")])->assertForbidden();
    }

    public function test_won_quote_opens_prefilled_contract(): void
    {
        $this->assertSame('Cinco millones quinientos ochenta y nueve mil pesos m/cte.', NumberToWords::pesos(5589000));
        $this->assertSame('Un millón de pesos m/cte.', NumberToWords::pesos(1000000));

        $admin = $this->admin();
        $quote = Quote::create(['customer_name' => 'Laura', 'customer_phone' => '3001234567', 'school' => 'Colegio Montreal',
            'quantity' => 40, 'estimated_price' => 0, 'items' => ['combo' => 2, 'products' => [], 'extras' => ['forro']]]);

        $res = $this->actingAs($admin)->post(route('admin.quotes.convert', $quote), ['school_name' => 'Colegio Montreal']);
        $school = School::firstOrFail();
        $res->assertRedirect(route('admin.contracts.create', ['school_id' => $school->id, 'quote_id' => $quote->id]));

        $this->actingAs($admin)->get(route('admin.contracts.create', ['school_id' => $school->id, 'quote_id' => $quote->id]))->assertOk()
            ->assertSee('Prellenado desde la cotización')
            ->assertSee('value="362200"', false)
            ->assertSee('value="14488000"', false)
            ->assertSee('Catorce millones cuatrocientos ochenta y ocho mil pesos')
            ->assertSee('Diseño y confección de 40 × Combo 2')
            ->assertSee('value="chaqueta_buzo" selected', false);
    }

    public function test_daily_digest_and_auto_followup(): void
    {
        $due = Quote::create(['customer_name' => 'Hoy', 'customer_phone' => '3000000001', 'quantity' => 1, 'estimated_price' => 0, 'status' => 'enviada', 'follow_up_at' => today()]);
        $seen = Quote::create(['customer_name' => 'Vio', 'customer_phone' => '3000000002', 'customer_email' => 'vio@test.co', 'quantity' => 1, 'estimated_price' => 0, 'status' => 'enviada']);
        $seen->forceFill(['status' => 'vista', 'viewed_at' => now()->subDays(3), 'sent_at' => now()->subDays(4)])->saveQuietly();
        Setting::put('contact_notify_email', 'yo@glauren.co');

        $this->artisan('ventas:recordatorio')->expectsOutputToContain('Resumen enviado: 2')->assertSuccessful();
        Mail::assertSent(\App\Modules\Chaquetas\Mail\SimpleMail::class, fn ($m) => $m->hasTo('yo@glauren.co') && count($m->rows) === 2);

        // Desactivado por defecto: no escribe a clientes
        $this->artisan('ventas:seguimiento')->expectsOutputToContain('desactivado')->assertSuccessful();
        Setting::put('auto_followup', '1');
        $this->artisan('ventas:seguimiento')->expectsOutputToContain('1 cotización(es)')->assertSuccessful();
        Mail::assertSent(\App\Modules\Chaquetas\Mail\SimpleMail::class, fn ($m) => $m->hasTo('vio@test.co') && str_contains($m->body, $seen->publicUrl()));
        $this->assertNotEmpty($seen->fresh()->config['auto_followup_at']);
        $this->assertTrue($seen->activities()->where('description', 'like', 'Seguimiento automático enviado por correo%')->exists());

        // Solo una vez
        $this->artisan('ventas:seguimiento')->expectsOutputToContain('0 cotización(es)')->assertSuccessful();
    }

    public function test_delivery_survey_creates_pending_testimonial(): void
    {
        Setting::put('google_review_url', 'https://g.page/r/glauren/review');
        $school = School::create(['name' => 'Colegio Sol', 'slug' => 'sol', 'active' => true, 'payment_mode' => 'estudiante', 'contact_name' => 'Marta Gil', 'contact_email' => 'marta@sol.co']);
        $c = Contract::create(['school_id' => $school->id, 'code' => 'CTR-E', 'year' => 2027, 'production_status' => 'listo']);

        $c->update(['production_status' => 'entregado']);
        Mail::assertSent(\App\Modules\Chaquetas\Mail\SimpleMail::class, fn ($m) => $m->hasTo('marta@sol.co') && str_contains($m->buttonUrl, '/encuesta/'.$c->id));

        $this->get('/encuesta/'.$c->id)->assertForbidden();   // sin firma no entra
        $url = DeliverySurvey::url($c);
        $this->get($url)->assertOk()->assertSee('¿Cómo te fue con tus chaquetas?');

        $this->post($url, ['rating' => 5, 'name' => 'Sofía', 'role' => 'Estudiante', 'text' => '¡Quedaron hermosas!', 'publish' => 1,
            'photos' => [UploadedFile::fake()->image('entrega.jpg', 800, 600)]])->assertRedirect($url);
        $t = Testimonial::firstOrFail();
        $this->assertFalse($t->active);
        $this->assertSame(5, $t->rating);
        $this->assertStringContainsString('Colegio Sol', $t->role);
        $this->assertNotNull($t->photo_path);
        $this->withSession(['survey_done' => 5])->get($url)->assertOk()->assertSee('https://g.page/r/glauren/review', false);
    }

    public function test_sales_dashboard_and_cartera_render(): void
    {
        $admin = $this->admin();
        $q = Quote::create(['customer_name' => 'Ana', 'customer_phone' => '3000000000', 'quantity' => 30, 'estimated_price' => 0, 'items' => ['combo' => 1]]);
        $q->update(['status' => 'cerrado']);
        $school = School::create(['name' => 'Colegio Luna', 'slug' => 'luna', 'active' => true, 'payment_mode' => 'estudiante']);
        $c = Contract::create(['school_id' => $school->id, 'code' => 'CTR-C', 'year' => 2027, 'production_status' => 'en_produccion', 'price_per_unit' => 300000]);
        Student::create(['contract_id' => $c->id, 'name' => 'A', 'document' => '1', 'payment_status' => 'pagado']);
        Student::create(['contract_id' => $c->id, 'name' => 'B', 'document' => '2', 'payment_status' => 'pendiente']);

        $this->actingAs($admin)->get(route('admin.sales.dashboard'))->assertOk()
            ->assertSee('¿Qué fuente vende más?')->assertSee('Configurador web')->assertSee('Automatizaciones');
        $this->actingAs($admin)->get(route('admin.sales.cartera'))->assertOk()
            ->assertSee('Colegio Luna')->assertSee('$300.000')->assertSee('50%');
        $this->assertStringContainsString('CTR-C;Colegio Luna;2027', $this->actingAs($admin)->get(route('admin.sales.cartera.export'))->getContent());

        $this->actingAs($admin)->put(route('admin.sales.automation'), ['auto_followup' => 1, 'auto_followup_days' => 3, 'google_review_url' => 'https://g.page/x'])->assertRedirect();
        $this->assertSame('1', Setting::get('auto_followup'));
        $this->assertSame('0', Setting::get('sales_digest'));
        $this->assertSame('3', Setting::get('auto_followup_days'));
    }

    public function test_client_card_is_editable_from_quote(): void
    {
        $admin = $this->admin();
        $lead = \App\Modules\Chaquetas\Models\Lead::create(['name' => 'Laura', 'phone' => '3001234567', 'source' => 'whatsapp']);
        $quote = Quote::create(['customer_name' => 'Laura', 'customer_phone' => '3001234567', 'quantity' => 30, 'estimated_price' => 0,
            'items' => ['combo' => 2], 'lead_id' => $lead->id]);

        $this->actingAs($admin)->get(route('admin.quotes.show', $quote))->assertOk()->assertSee('Ficha del cliente');
        $this->actingAs($admin)->put(route('admin.quotes.client', $quote), [
            'customer_name' => 'Laura Gómez', 'customer_phone' => '3001234567', 'customer_email' => 'laura@test.co',
            'school' => 'Colegio Montreal', 'quantity' => 42,
            'client' => ['course' => '11A y 11B', 'decides' => 'La rectora', 'budget' => '$320.000'],
            'contacts' => [['name' => 'Rectora Marta', 'role' => 'Rectora', 'phone' => '3109998877'], ['name' => '', 'phone' => '']],
        ])->assertRedirect();

        $q = $quote->fresh();
        $this->assertSame('Laura Gómez', $q->customer_name);
        $this->assertSame(42, (int) $q->quantity);
        $this->assertEquals(342200 * 42, (float) $q->estimated_price);
        $this->assertSame('La rectora', $q->client()['decides']);
        $this->assertCount(1, $q->client()['contacts']);
        $this->assertSame('Colegio Montreal', $lead->fresh()->school);
        $this->actingAs($admin)->get(route('admin.quotes.show', $quote))->assertOk()
            ->assertSee('Rectora Marta')->assertSee('wa.me/573109998877', false)->assertSee('11A y 11B');
    }
}
