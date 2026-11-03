<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Chaquetas\Mail\QuoteMail;
use App\Modules\Chaquetas\Models\JacketType;
use App\Modules\Chaquetas\Models\Lead;
use App\Modules\Chaquetas\Models\School;
use App\Modules\Chaquetas\Models\Quote;
use App\Modules\Chaquetas\Models\Setting;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuoteCrmTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create(['name' => 'Admin', 'email' => 'admin@test.co', 'password' => bcrypt('x'), 'role' => 'admin']);
    }

    public function test_quotes_get_consecutive_numbers_and_public_link(): void
    {
        $a = Quote::create(['customer_name' => 'Ana', 'customer_phone' => '3000000000', 'quantity' => 1, 'estimated_price' => 0]);
        $b = Quote::create(['customer_name' => 'Beto', 'customer_phone' => '3000000001', 'quantity' => 1, 'estimated_price' => 0]);

        $this->assertSame(65044, $a->number);
        $this->assertSame(65045, $b->number);
        $this->assertSame(32, strlen($a->public_token));
        $this->assertSame('COT-65044', $a->code());
    }

    public function test_admin_creates_quote_with_combo_and_extras(): void
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

        $this->actingAs($this->admin())->post(route('admin.quotes.store'), [
            'customer_name'  => 'Laura Gómez',
            'customer_phone' => '3001234567',
            'school'         => 'Colegio Montreal',
            'quantity'       => 40,
            'combo'          => 2,
            'extras'         => ['forro'],
        ])->assertRedirect();

        $quote = Quote::firstOrFail();
        // Combo 2 = (202.900 + 149.900) − 3% = 342.200 ; + forro 20.000
        $this->assertSame(362200, $quote->summary()['unit_total']);
        $this->assertEquals(362200 * 40, (float) $quote->estimated_price);
        $this->assertSame('manual', $quote->source);
        $this->assertNotNull($quote->follow_up_at);
    }

    public function test_customer_view_marks_quote_as_seen(): void
    {
        $quote = Quote::create(['customer_name' => 'Ana', 'customer_phone' => '3000000000', 'quantity' => 30,
            'items' => ['combo' => 1], 'estimated_price' => 0, 'status' => 'enviada']);

        $this->get(route('cotizacion.show', $quote->public_token))
            ->assertOk()
            ->assertSee('COT-'.$quote->number)
            ->assertSee('Combo 1');

        $quote->refresh();
        $this->assertSame('vista', $quote->status);
        $this->assertSame(1, $quote->view_count);
        $this->assertNotNull($quote->viewed_at);
    }

    public function test_admin_viewing_public_link_does_not_count(): void
    {
        $quote = Quote::create(['customer_name' => 'Ana', 'customer_phone' => '3000000000', 'quantity' => 1, 'estimated_price' => 0]);

        $this->actingAs($this->admin())->get(route('cotizacion.show', $quote->public_token))->assertOk();

        $this->assertSame(0, $quote->fresh()->view_count);
    }

    public function test_send_marks_as_sent_and_opens_whatsapp(): void
    {
        $quote = Quote::create(['customer_name' => 'Pedro Ruiz', 'customer_phone' => '311 000 0000', 'quantity' => 1, 'estimated_price' => 0]);

        $this->actingAs($this->admin())->get(route('admin.quotes.send', $quote))
            ->assertRedirectContains('https://wa.me/573110000000');

        $quote->refresh();
        $this->assertSame('enviada', $quote->status);
        $this->assertNotNull($quote->sent_at);
    }

    public function test_admin_pages_render(): void
    {
        $admin = $this->admin();
        $quote = Quote::create(['customer_name' => 'Ana', 'customer_phone' => '3000000000', 'quantity' => 1, 'estimated_price' => 0, 'follow_up_at' => today()]);

        $this->actingAs($admin)->get(route('admin.quotes.index'))->assertOk()->assertSee('por llamar hoy');
        $this->actingAs($admin)->get(route('admin.quotes.create'))->assertOk();
        $this->actingAs($admin)->get(route('admin.quotes.show', $quote))->assertOk()->assertSee($quote->publicUrl());
        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk()->assertSee('Tasa de cierre');
    }

    public function test_web_quote_with_email_is_sent_automatically(): void
    {
        Mail::fake();
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
        $type = JacketType::create(['name' => 'Beisbolera', 'slug' => 'beisbolera', 'base_price' => 169900, 'patch_price' => 0, 'active' => true]);

        $res = $this->postJson(route('quote.store'), [
            'jacket_type_id' => $type->id, 'customer_name' => 'Sara Díaz', 'customer_phone' => '3001112233',
            'customer_email' => 'sara@example.com', 'quantity' => 35, 'estimated_price' => 5946500,
        ])->assertOk()->assertJson(['ok' => true, 'email_sent' => true]);

        $quote = Quote::firstOrFail();
        $this->assertSame($quote->publicUrl(), $res->json('quote_url'));
        $this->assertStringContainsString(rawurlencode($quote->publicUrl()), $res->json('whatsapp_url'));
        Mail::assertSent(QuoteMail::class, fn ($m) => $m->hasTo('sara@example.com') && $m->quote->is($quote));
        $this->assertSame('enviada', $quote->status);
        $this->assertNotNull($quote->sent_at);
    }

    public function test_web_quote_without_email_still_gives_whatsapp_button(): void
    {
        Mail::fake();
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
        $type = JacketType::create(['name' => 'Buso', 'slug' => 'buso', 'base_price' => 149900, 'patch_price' => 0, 'active' => true]);

        $this->postJson(route('quote.store'), [
            'jacket_type_id' => $type->id, 'customer_name' => 'Juan', 'customer_phone' => '3001112233',
            'quantity' => 1, 'estimated_price' => 149900,
        ])->assertOk()->assertJson(['ok' => true, 'email_sent' => false])->assertJsonStructure(['whatsapp_url', 'quote_url', 'quote_number']);

        Mail::assertNothingSent();
        $this->assertSame('nuevo', Quote::first()->status);
    }

    public function test_quote_email_renders_and_admin_can_resend(): void
    {
        Mail::fake();
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
        $quote = Quote::create(['customer_name' => 'Ana Ruiz', 'customer_phone' => '3000000000', 'customer_email' => 'ana@example.com',
            'quantity' => 30, 'items' => ['combo' => 2], 'estimated_price' => 0]);

        $html = (new QuoteMail($quote))->render();
        $this->assertStringContainsString((string) $quote->number, $html);
        $this->assertStringContainsString($quote->publicUrl(), $html);

        $this->actingAs($this->admin())->post(route('admin.quotes.email', $quote))->assertRedirect();
        Mail::assertSent(QuoteMail::class, fn ($m) => $m->hasTo('ana@example.com'));
    }

    public function test_whatsapp_with_custom_message_marks_sent_and_logs_history(): void
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
        $admin = $this->admin();
        $quote = Quote::create(['customer_name' => 'Laura Gómez', 'customer_phone' => '3001234567', 'school' => 'Montreal',
            'quantity' => 30, 'estimated_price' => 0]);

        $msg = 'Hola Laura, tu cotización '.$quote->code().' está aquí: '.$quote->publicUrl();
        $this->actingAs($admin)->post(route('admin.quotes.whatsapp', $quote), ['message' => $msg, 'save_default' => 1])
            ->assertRedirect('https://wa.me/573001234567?text='.rawurlencode($msg));

        $quote->refresh();
        $this->assertSame('enviada', $quote->status);
        $this->assertNotNull($quote->sent_at);
        $this->assertTrue($quote->follow_up_at->isSameDay(today()->addDays(2)));
        // La plantilla guardada vuelve a usar variables
        $this->assertSame('Hola Laura, tu cotización {numero} está aquí: {enlace}', Setting::get('quote_whatsapp_message'));

        $types = $quote->activities()->pluck('type')->all();
        $this->assertContains('creada', $types);
        $this->assertContains('whatsapp', $types);
        $this->assertContains('estado', $types);
    }

    public function test_default_whatsapp_message_has_customer_data(): void
    {
        Setting::put('quote_whatsapp_message', null); // sin plantilla guardada → mensaje por defecto
        $quote = Quote::create(['customer_name' => 'Laura Gómez', 'customer_phone' => '3001234567', 'school' => 'Montreal',
            'quantity' => 30, 'estimated_price' => 0]);

        $text = $quote->whatsappMessage();
        $this->assertStringContainsString('Laura', $text);
        $this->assertStringContainsString($quote->code(), $text);
        $this->assertStringContainsString($quote->publicUrl(), $text);
        $this->assertStringContainsString('de Montreal', $text);
    }

    public function test_pdf_note_and_history_on_detail_page(): void
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
        $admin = $this->admin();
        $quote = Quote::create(['customer_name' => 'Ana', 'customer_phone' => '3000000000', 'quantity' => 1, 'estimated_price' => 0]);
        Quote::create(['customer_name' => 'Ana', 'customer_phone' => '+57 300 000 0000', 'quantity' => 5, 'estimated_price' => 0]);

        $this->actingAs($admin)->get(route('admin.quotes.pdf', $quote))->assertRedirect($quote->publicUrl().'?imprimir=1');
        $this->actingAs($admin)->post(route('admin.quotes.note', $quote), ['note' => 'Llamé, piden 45 estudiantes'])->assertRedirect();

        $this->actingAs($admin)->get(route('admin.quotes.show', $quote))->assertOk()
            ->assertSee('Historial de seguimiento')
            ->assertSee('Llamé, piden 45 estudiantes')
            ->assertSee('Otras cotizaciones de este cliente')
            ->assertSee('Abrir WhatsApp y marcar como enviada');

        $this->get(route('cotizacion.show', $quote->public_token).'?imprimir=1')->assertOk()->assertSee('window.print()', false);
    }

    public function test_customer_view_is_logged_once_per_hour(): void
    {
        $quote = Quote::create(['customer_name' => 'Ana', 'customer_phone' => '3000000000', 'quantity' => 1, 'estimated_price' => 0]);
        $this->get(route('cotizacion.show', $quote->public_token));
        $this->get(route('cotizacion.show', $quote->public_token));

        $this->assertSame(1, $quote->activities()->where('type', 'vista')->count());
        $this->assertSame(2, $quote->fresh()->view_count);
    }

    public function test_lead_gets_follow_up_and_general_email_without_prices(): void
    {
        Mail::fake();
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
        $admin = $this->admin();
        $lead = Lead::create(['name' => 'Carlos Pérez', 'email' => 'carlos@example.com', 'phone' => '3105556677', 'school' => 'Liceo Freire', 'source' => 'web']);

        $this->actingAs($admin)->get(route('admin.leads.index'))->assertOk()->assertSee('Enviar cotización');

        $res = $this->actingAs($admin)->post(route('admin.leads.quote', $lead));
        $quote = Quote::where('lead_id', $lead->id)->firstOrFail();
        $res->assertRedirect(route('admin.quotes.show', $quote));
        $this->assertTrue($quote->isGeneral());
        $this->assertSame('lead', $quote->source);

        // Volver a abrir no duplica
        $this->actingAs($admin)->post(route('admin.leads.quote', $lead));
        $this->assertSame(1, Quote::where('lead_id', $lead->id)->count());

        $html = (new QuoteMail($quote))->render();
        $this->assertStringContainsString('Tu cotización ya está lista en línea', $html);
        $this->assertStringNotContainsString('Valor por estudiante', $html);
        $this->assertStringNotContainsString('119.000', $html);

        $this->actingAs($admin)->post(route('admin.quotes.email', $quote))->assertRedirect();
        Mail::assertSent(QuoteMail::class, fn ($m) => $m->hasTo('carlos@example.com'));
        $this->actingAs($admin)->get(route('admin.leads.index'))->assertOk()->assertSee($quote->code());
    }

    public function test_convert_quote_into_school_and_start_contract(): void
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
        $admin = $this->admin();
        $quote = Quote::create(['customer_name' => 'Laura Gómez', 'customer_phone' => '3001234567', 'customer_email' => 'laura@example.com',
            'school' => 'Colegio Montreal', 'quantity' => 40, 'estimated_price' => 0]);

        $this->actingAs($admin)->post(route('admin.quotes.convert', $quote), ['school_name' => 'Colegio Montreal', 'city' => 'Bogotá'])
            ->assertRedirect();

        $school = School::firstOrFail();
        $this->assertSame('Colegio Montreal', $school->name);
        $this->assertSame('Laura Gómez', $school->contact_name);
        $quote->refresh();
        $this->assertSame($school->id, $quote->school_id);
        $this->assertSame('cerrado', $quote->status);

        $this->actingAs($admin)->get(route('admin.contracts.create', ['school_id' => $school->id]))->assertOk();
        $this->actingAs($admin)->get(route('admin.quotes.show', $quote))->assertOk()->assertSee('¡Cliente ganado!');

        // El cliente ve que su contrato ya está en marcha
        $this->get(route('cotizacion.show', $quote->public_token))->assertOk()->assertSee('Estado de tu proceso')->assertSee('Contrato firmado');
    }

    public function test_client_page_is_the_same_15_sheets_as_the_pdf(): void
    {
        $quote = Quote::create(['customer_name' => 'Ana Ruiz', 'customer_phone' => '3000000000', 'school' => 'Colegio Montreal',
            'quantity' => 30, 'estimated_price' => 0]);

        $res = $this->get(route('cotizacion.show', $quote->public_token))->assertOk();
        $html = $res->getContent();

        $this->assertSame(15, substr_count($html, '<div class="sheet">'));
        $res->assertSee('Ana Ruiz')->assertSee('Colegio Montreal')
            ->assertSee('>'.$quote->number.'<', false)
            ->assertSee('/img/cotizacion/disenos/beisbolera-9.jpg', false)
            ->assertSee('/img/cotizacion/productos/estructurada.jpg', false)
            ->assertSee('$202.900')->assertSee('$342.200')->assertSee('$119.000')
            ->assertSee('Descargar en PDF');
        $this->assertStringNotContainsString('65043', $html);
        $this->assertStringNotContainsString('/_blob/', $html);
        $this->assertStringNotContainsString('%%', $html);
        $this->assertStringNotContainsString('{{', $html);

        foreach (glob(public_path('img/cotizacion').'/**/*') ?: [] as $f) {
            $this->assertFileExists($f);
        }
        preg_match_all('#/img/cotizacion/[^"\')\s]+#', $html, $m);
        foreach (array_unique($m[0]) as $src) {
            $this->assertFileExists(public_path(ltrim($src, '/')), $src);
        }
    }

    public function test_admin_creates_contact_manually_and_goes_to_its_quote(): void
    {
        Mail::fake();
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
        $admin = $this->admin();

        $this->actingAs($admin)->get(route('admin.leads.index'))->assertOk()->assertSee('Nuevo contacto');
        $this->actingAs($admin)->get(route('admin.leads.create'))->assertOk();

        $res = $this->actingAs($admin)->post(route('admin.leads.store'), [
            'name' => 'Pedro Pérez', 'phone' => '310 555 1234', 'email' => 'pedro@test.co',
            'school' => 'Colegio San José', 'quantity' => 35, 'combo' => 2, 'send_email' => 1,
        ]);

        $lead = Lead::firstOrFail();
        $quote = Quote::firstOrFail();
        $res->assertRedirect(route('admin.quotes.show', $quote));
        $this->assertSame('manual', $lead->source);
        $this->assertSame($lead->id, $quote->lead_id);
        $this->assertSame(35, (int) $quote->quantity);
        $this->assertSame(342200, $quote->summary()['unit_total']);
        Mail::assertSent(QuoteMail::class);

        // Mismo celular: no duplica, abre la cotización que ya existe.
        $this->actingAs($admin)->post(route('admin.leads.store'), [
            'name' => 'Pedro otra vez', 'phone' => '+57 3105551234', 'quantity' => 1,
        ])->assertRedirect(route('admin.quotes.show', $quote));
        $this->assertSame(1, Lead::count());
        $this->assertSame(1, Quote::count());

        $this->actingAs($admin)->get(route('admin.leads.index'))->assertOk()->assertSee($quote->code());
    }

    public function test_step_by_step_moves_the_client_from_sale_to_delivery(): void
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
        $admin = $this->admin();
        $quote = Quote::create(['customer_name' => 'Laura Gómez', 'customer_phone' => '3001234567', 'school' => 'Colegio Montreal',
            'quantity' => 40, 'estimated_price' => 0]);
        $stage = fn ($s) => $this->actingAs($admin)->post(route('admin.quotes.stage', $quote), ['stage' => $s]);

        $this->actingAs($admin)->get(route('admin.quotes.show', $quote))->assertOk()
            ->assertSee('Paso a paso del cliente')->assertSee('Haz el primer contacto');

        $stage('contactado')->assertRedirect(route('admin.quotes.show', $quote));
        $this->assertSame('contactado', $quote->fresh()->status);
        $stage('enviada');
        $this->assertNotNull($quote->fresh()->sent_at);
        $this->actingAs($admin)->get(route('admin.quotes.show', $quote))->assertSee('Esperando que la abra');

        // Sin colegio no se puede pasar a producción
        $stage('en_produccion')->assertSessionHas('warn');

        $stage('perdida');
        $this->assertSame('cancelado', $quote->fresh()->status);
        $stage('reabrir');
        $this->assertSame('negociando', $quote->fresh()->status);
        $this->actingAs($admin)->get(route('admin.quotes.show', $quote))->assertSee('Cierra el trato');

        // Gana → colegio → contrato → producción
        $this->actingAs($admin)->post(route('admin.quotes.convert', $quote), ['school_name' => 'Colegio Montreal']);
        $quote->refresh();
        $this->assertSame('ganada', $quote->journeyStage());
        $stage('contrato')->assertRedirect(route('admin.contracts.create', ['school_id' => $quote->school_id]));

        $contract = \App\Modules\Chaquetas\Models\Contract::create(['school_id' => $quote->school_id, 'code' => 'CTR-TEST-1',
            'production_status' => 'cotizado', 'contract_status' => 'firmado', 'signed_at' => now()]);
        $this->assertSame('contrato', $quote->journeyStage());
        $this->actingAs($admin)->get(route('admin.quotes.show', $quote))->assertOk()->assertSee('Empieza: toma de medidas');

        $stage('toma_medidas');
        $this->assertSame('toma_medidas', $contract->fresh()->production_status);
        $this->actingAs($admin)->get(route('admin.quotes.show', $quote))->assertSee('Avanzar a en producción');
        $stage('en_produccion');
        $this->assertSame('en_produccion', $quote->fresh()->journeyStage());
        $this->assertTrue($quote->activities()->where('description', 'like', 'Producción: En producción%')->exists());

        // El cliente lo ve en su enlace
        $this->get(route('cotizacion.show', $quote->public_token))->assertOk();
        $steps = $quote->fresh()->customerSteps();
        $this->assertTrue($steps[4]['current']);
    }

    public function test_business_quote_with_photos_features_and_gifts(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        Mail::fake();
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
        $admin = $this->admin();

        $this->actingAs($admin)->get(route('admin.business.index'))->assertOk()->assertSee('Cotizaciones para empresas');
        $this->actingAs($admin)->get(route('admin.business.create'))->assertOk()->assertSee('Prendas que cotizas');

        $res = $this->actingAs($admin)->post(route('admin.business.store'), [
            'company_name' => 'Constructora Andes S.A.S.', 'company_nit' => '900123456-7', 'company_city' => 'Bogotá',
            'contact_name' => 'Marta Ruiz', 'contact_role' => 'Compras', 'contact_phone' => '3109998877', 'contact_email' => 'marta@andes.co',
            'send_email' => 1,
            'lines' => [
                ['garment' => 'Chaqueta', 'name' => 'Chaqueta corporativa antifluido', 'features' => "Tela Lafayette antifluido\n- Logo bordado en el pecho\n\nTallas S a XXL",
                 'qty' => 40, 'unit_price' => '189.900', 'photo' => \Illuminate\Http\UploadedFile::fake()->image('chaqueta.jpg', 600, 600)],
                ['garment' => 'Camiseta polo', 'name' => 'Polo con logo', 'features' => 'Piqué', 'qty' => 15, 'unit_price' => '45000'],
            ],
        ]);

        $quote = Quote::firstOrFail();
        $res->assertRedirect(route('admin.quotes.show', $quote));
        $this->assertTrue($quote->isBusiness());
        $this->assertSame('Constructora Andes S.A.S.', $quote->school);
        $lines = $quote->businessLines();
        $this->assertCount(2, $lines);
        $this->assertSame(['Tela Lafayette antifluido', 'Logo bordado en el pecho', 'Tallas S a XXL'], $lines[0]['features']);
        $this->assertSame(189900, $lines[0]['unit_price']);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($lines[0]['photo']);
        $t = $quote->businessTotals();
        $this->assertSame(55, $t['units']);
        $this->assertSame(40 * 189900 + 15 * 45000, $t['total']);
        $this->assertSame(2, $t['gift_jackets']);
        $this->assertEquals($t['total'], (float) $quote->estimated_price);
        Mail::assertSent(QuoteMail::class, fn ($m) => $m->content()->view === 'emails.quote-empresa');
        $this->assertStringContainsString('Constructora Andes', (new QuoteMail($quote))->render());

        // Panel con datos de empresa, sin "convertir en colegio"
        $this->actingAs($admin)->get(route('admin.quotes.show', $quote))->assertOk()
            ->assertSee('Constructora Andes')->assertSee('Polo con logo')->assertDontSee('Conviértelo en colegio')
            ->assertSee('para Constructora Andes S.A.S.', false);

        // Página del cliente: hojas de empresa con foto, características, valores y obsequios
        $html = $this->get(route('cotizacion.show', $quote->public_token))->assertOk()
            ->assertSee('DOTACIÓN')->assertSee('Chaqueta corporativa antifluido')->assertSee('Logo bordado en el pecho')
            ->assertSee('$189.900')->assertSee('1 chaqueta adicional de obsequio por cada 25 unidades')
            ->assertSee('Tu pedido recibe 2 chaquetas de obsequio')->assertSee('Telas y cremalleras', false)
            ->assertSee($lines[0]['photo_url'], false)->getContent();
        $this->assertSame(6, substr_count($html, '<div class="sheet">'));

        // Editar: quitar la foto y cambiar valor
        $this->actingAs($admin)->put(route('admin.business.update', $quote), [
            'company_name' => 'Constructora Andes S.A.S.', 'contact_name' => 'Marta Ruiz', 'contact_phone' => '3109998877',
            'lines' => [
                ['name' => 'Chaqueta corporativa', 'qty' => 10, 'unit_price' => '200.000', 'photo_path' => $lines[0]['photo'], 'remove_photo' => 1],
                ['name' => 'Ruta inventada', 'qty' => 1, 'unit_price' => '1', 'photo_path' => 'otra/ruta.jpg'],
            ],
        ])->assertRedirect(route('admin.quotes.show', $quote));
        $quote->refresh();
        $this->assertNull($quote->businessLines()[0]['photo']);
        $this->assertNull($quote->businessLines()[1]['photo']);
        \Illuminate\Support\Facades\Storage::disk('public')->assertMissing($lines[0]['photo']);
        $this->assertSame(0, $quote->businessTotals()['gift_jackets']);

        // Paso a paso: termina en Ganada
        $this->actingAs($admin)->post(route('admin.quotes.stage', $quote), ['stage' => 'negociando']);
        $this->actingAs($admin)->get(route('admin.quotes.show', $quote))->assertSee('Marcar como ganada');
        $this->actingAs($admin)->post(route('admin.quotes.stage', $quote), ['stage' => 'ganada']);
        $this->assertSame('cerrado', $quote->fresh()->status);
        $this->assertSame(6, count($quote->fresh()->journey()));
        $this->actingAs($admin)->get(route('admin.business.index'))->assertOk()->assertSee('Constructora Andes');
    }
}
