<?php

namespace Tests\Feature;

use App\Modules\Chaquetas\Models\Post;
use App\Modules\Chaquetas\Models\Testimonial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoIaTest extends TestCase
{
    use RefreshDatabase;

    public function test_robots_allows_ai_crawlers_and_llms_txt_exists(): void
    {
        $r = $this->get('/robots.txt')->assertOk()->getContent();
        $this->assertStringContainsString("User-agent: GPTBot\nUser-agent: OAI-SearchBot", $r);
        $this->assertStringNotContainsString("User-agent: GPTBot\nDisallow: /\n", $r);
        $this->assertStringContainsString('Disallow: /cotizacion/', $r);

        $this->get('/llms.txt')->assertOk()
            ->assertSee("# G'Lauren", false)->assertSee('$202.900')->assertSee('25 a 35 días hábiles')
            ->assertSee(route('ciudades.show', 'medellin'));
    }

    public function test_structured_data_brand_logo_and_rating(): void
    {
        foreach ([5, 5, 4] as $i => $r) {
            Testimonial::create(['text' => 'Excelente', 'name' => 'Cliente '.$i, 'rating' => $r, 'active' => true, 'sort' => $i]);
        }
        $html = $this->get('/')->assertOk()->getContent();
        preg_match('#<script type="application/ld\+json">(.*?)</script>#s', $html, $m);
        $org = json_decode($m[1], true)['@graph'][0];
        $this->assertSame("G'Lauren", $org['name']);
        $this->assertStringEndsWith('/android-chrome-512x512.png', $org['logo']['url']);
        $this->assertStringNotContainsString('wp-content', $org['image']);
        $this->assertSame(3, $org['aggregateRating']['reviewCount']);
        $this->assertEquals(4.7, $org['aggregateRating']['ratingValue']);
        $this->assertSame('COP', $org['hasOfferCatalog']['itemListElement'][0]['priceCurrency']);
    }

    public function test_city_pages(): void
    {
        $this->get(route('ciudades.index'))->assertOk()->assertSee('Chaquetas de prom en Medellín');
        $this->get(route('ciudades.show', 'bogota'))->assertOk()
            ->assertSee('Chaquetas de prom en')->assertSee('Medidas en el colegio')->assertSee('con globos y fotografía')
            ->assertSee('"@type":"FAQPage"', false);
        $this->get(route('ciudades.show', 'medellin'))->assertOk()
            ->assertSee('Reunión virtual')->assertSee('Tallas estándar')->assertDontSee('En el colegio, con globos y fotografía');
        $this->get('/chaquetas-de-prom/marte')->assertNotFound();
        $this->get('/sitemap.xml')->assertOk()->assertSee(route('ciudades.show', 'cali'));
    }

    public function test_blog_guides_are_created_as_drafts(): void
    {
        $this->assertSame(3, Post::whereIn('slug', [
            'cuanto-cuesta-una-chaqueta-de-prom-en-colombia', 'chaqueta-beisbolera-o-estructurada-cual-elegir', 'cuando-pedir-las-chaquetas-de-grado',
        ])->where('active', false)->count());
    }

    public function test_business_landing_and_old_page_redirect(): void
    {
        $this->get('/p/dotaciones-empresariales')->assertRedirect('/empresas')->assertStatus(301);
        $this->get('/empresas')->assertOk()
            ->assertSee('Dotación de ley')->assertSee('30')->assertSee('Chalecos')->assertSee('Delantales')
            ->assertSee('Visita el taller')->assertSee('google.com/maps', false)->assertSee('"@type":"FAQPage"', false)
            ->assertSee('class="fab"', false);
    }
}
