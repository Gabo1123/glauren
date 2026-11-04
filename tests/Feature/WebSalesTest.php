<?php

namespace Tests\Feature;

use App\Modules\Chaquetas\Mail\AdminLeadMail;
use App\Modules\Chaquetas\Mail\QuoteMail;
use App\Modules\Chaquetas\Models\Lead;
use App\Modules\Chaquetas\Models\Quote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WebSalesTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_shows_quick_quote_form_and_prices(): void
    {
        $this->get('/')->assertOk()
            ->assertSee('Cotiza en 1 minuto')->assertSee(route('cotiza.store'), false)
            ->assertSee('$202.900')->assertSee('$169.900')->assertSee('Combos chaqueta + buso desde')
            ->assertSee(route('empresas'), false);
    }

    public function test_quick_quote_creates_lead_and_shows_full_quote(): void
    {
        Mail::fake();
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

        $res = $this->post(route('cotiza.store'), [
            'name' => 'Laura Gómez', 'phone' => '300 123 4567', 'email' => 'laura@test.co',
            'school' => 'Colegio Montreal', 'students' => 38, 'interest' => 'beisbolera', 'accept' => 1,
        ]);
        $quote = Quote::firstOrFail();
        $res->assertRedirect($quote->publicUrl().'?nueva=1');
        $this->assertSame(38, (int) $quote->quantity);
        $this->assertSame('Colegio Montreal', $quote->school);
        $this->assertSame('cotiza-1min', Lead::first()->source);
        Mail::assertSent(QuoteMail::class);
        Mail::assertSent(AdminLeadMail::class);

        $this->get($quote->publicUrl().'?nueva=1')->assertOk()->assertSee('¡Listo, Laura!')->assertSee('<div class="sheet">', false);

        // Mismo celular: no duplica
        $this->post(route('cotiza.store'), ['name' => 'Laura', 'phone' => '+57 3001234567', 'school' => 'Colegio Montreal', 'students' => 40, 'accept' => 1]);
        $this->assertSame(1, Lead::count());
        $this->assertSame(1, Quote::count());
        $this->assertSame(40, (int) $quote->fresh()->quantity);

        // Bots (honeypot) no crean nada
        $this->post(route('cotiza.store'), ['website' => 'spam', 'name' => 'x', 'phone' => '3110000000', 'school' => 'x', 'students' => 1, 'accept' => 1]);
        $this->assertSame(1, Lead::count());
    }

    public function test_quick_quote_validation_returns_to_form(): void
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
        $this->from('/')->post(route('cotiza.store'), ['_qq' => 1, 'name' => 'A', 'phone' => 'abc', 'school' => 'X', 'students' => 10])
            ->assertRedirect('/')->assertSessionHasErrors(['phone', 'accept']);
    }

    public function test_business_page_creates_request_for_the_advisor(): void
    {
        Mail::fake();
        Storage::fake('public');
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

        $this->get(route('empresas'))->assertOk()->assertSee('Tu equipo, ')->assertSee('1 chaqueta adicional de obsequio por cada 25 unidades');

        $this->post(route('empresas.store'), [
            'company' => 'Constructora Andes', 'nit' => '900123', 'city' => 'Bogotá', 'name' => 'Marta Ruiz', 'role' => 'Compras',
            'phone' => '3109998877', 'email' => 'marta@andes.co', 'garments' => ['Chaquetas', 'Busos'], 'units' => 60,
            'message' => 'Azul oscuro', 'logo' => UploadedFile::fake()->image('logo.png', 300, 300), 'accept' => 1,
        ])->assertRedirect(route('empresas'))->assertSessionHas('sent');

        $q = Quote::firstOrFail();
        $this->assertTrue($q->isBusiness());
        $this->assertSame('Constructora Andes', $q->school);
        $this->assertSame([], $q->businessLines());
        $this->assertStringContainsString('Chaquetas, Busos', $q->notes);
        Storage::disk('public')->assertExists($q->config['request']['logo']);
        Mail::assertSent(AdminLeadMail::class);
        Mail::assertNotSent(QuoteMail::class);           // el asesor arma los valores antes de enviarla

        $this->get(route('empresas'))->assertSee('¡Recibimos tu solicitud!');
    }
}
