<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Chaquetas\Mail\QuoteMail;
use App\Modules\Chaquetas\Models\JacketType;
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
        // Combo 2 = (189.900 + 149.900) − 3% = 329.600 ; + forro 20.000
        $this->assertSame(349600, $quote->summary()['unit_total']);
        $this->assertEquals(349600 * 40, (float) $quote->estimated_price);
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
}
