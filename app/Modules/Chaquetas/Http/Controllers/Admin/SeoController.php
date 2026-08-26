<?php

namespace App\Modules\Chaquetas\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Chaquetas\Models\Faq;
use App\Modules\Chaquetas\Models\SeoPage;
use App\Modules\Chaquetas\Models\Setting;
use Illuminate\Http\Request;

class SeoController extends Controller
{
    public function index()
    {
        $pages = SeoPage::orderBy('id')->get();
        $faqs = Faq::orderBy('sort')->get();
        $globals = [
            'seo_default_title'       => 'Título por defecto',
            'seo_default_description' => 'Descripción por defecto',
            'seo_keywords'            => 'Palabras clave (separadas por coma)',
            'seo_og_image'            => 'Imagen para compartir (URL)',
            'home_seo_content'        => 'Contenido SEO del home (HTML)',
        ];
        $values = Setting::map();

        return view('admin.seo.index', compact('pages', 'faqs', 'globals', 'values'));
    }

    public function updateGlobals(Request $request)
    {
        foreach (['seo_default_title', 'seo_default_description', 'seo_keywords', 'seo_og_image', 'home_seo_content'] as $k) {
            Setting::put($k, $request->input($k));
        }

        return back()->with('ok', 'SEO global actualizado.');
    }

    public function updatePage(Request $request, SeoPage $seoPage)
    {
        $seoPage->update($request->validate([
            'title'       => 'nullable|string|max:190',
            'description' => 'nullable|string|max:320',
            'keywords'    => 'nullable|string|max:255',
            'og_image'    => 'nullable|string|max:255',
            'noindex'     => 'nullable|boolean',
        ]) + ['noindex' => $request->boolean('noindex')]);

        return back()->with('ok', 'SEO de la página actualizado.');
    }
}
