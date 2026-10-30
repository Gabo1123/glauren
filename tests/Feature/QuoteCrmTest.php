<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Chaquetas\Models\Quote;
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
}
