@php
    use App\Modules\Chaquetas\Models\SeoPage;
    use App\Modules\Chaquetas\Models\Setting;
    use Illuminate\Support\Str;

    /*
    | SEO Engine (estilo Rank Math)
    | Variables opcionales que puede recibir este partial:
    |   $seoKey                -> clave para overrides globales por página (SeoPage)
    |   $seoModel              -> instancia Post o Page (usa sus campos meta_*)
    |   $article               -> instancia Post -> emite schema BlogPosting
    |   $breadcrumbs           -> array [['name'=>.., 'url'=>..], ...]
    |   $seoTitleOverride / $seoDescriptionOverride / $ogImageOverride / $canonical
    */

    $key   = $seoKey ?? 'home';
    $sp    = SeoPage::for($key);
    $model = $seoModel ?? null;
    $site  = Setting::get('brand_name', 'Glauren');
    $defOg = Setting::get('seo_og_image', 'https://chaquetasdeprom.com/wp-content/uploads/2025/11/chaqueta-de-promocion-1.jpeg');

    // --- Título ---
    $title = $seoTitleOverride
        ?? ($model && method_exists($model, 'metaTitle') ? $model->metaTitle().' · '.$site : null)
        ?? ($sp->title ?? null)
        ?? (Setting::get('seo_default_title') ?: 'Chaquetas de Prom 2026 en Colombia · '.$site);

    // --- Descripción ---
    $desc = $seoDescriptionOverride
        ?? ($model && method_exists($model, 'metaDescription') ? $model->metaDescription() : null)
        ?? ($sp->description ?? null)
        ?? (Setting::get('seo_default_description') ?: 'Diseña tu chaqueta de prom en 3D, sigue su fabricación con fotos reales y págala en línea. Más de 10 años vistiendo promociones en Colombia.');
    $desc = trim(preg_replace('/\s+/', ' ', $desc));

    // --- Palabra clave / keywords ---
    $focus = $model->focus_keyword ?? null;
    $kw    = $focus ?: (($sp->keywords ?? null) ?: Setting::get('seo_keywords', 'chaquetas de prom, chaquetas prom, diseñar chaquetas prom online, diseños de chaquetas de prom, chaquetas prom 2026'));

    // --- Imagen social ---
    $og = $ogImageOverride
        ?? ($model && method_exists($model, 'ogImage') ? $model->ogImage() : null)
        ?? ($sp->og_image ?? null)
        ?? $defOg;

    $canon   = $canonical ?? url()->current();
    $noindex = $model->noindex ?? ($sp->noindex ?? false);

    $tel    = Setting::get('contact_phone', '3246750207');
    $addr   = Setting::get('contact_address', 'Calle 48k 5a-27 Sur, Bogotá');
    $sameAs = array_values(array_filter([
        Setting::get('social_facebook', 'https://www.facebook.com/glaurenztienda'),
        Setting::get('social_tiktok', 'https://www.tiktok.com/@chaquetaprom'),
        Setting::get('social_instagram', 'https://www.instagram.com/chaquetaprom_/'),
    ]));

    $home  = url('/');
    $orgId = $home.'#organization';
    $webId = $home.'#website';
    $pageId = $canon.'#webpage';

    // ===== Nodos del @graph =====
    $graph = [];

    // Organización + Negocio local
    $graph[] = array_filter([
        '@type' => ['Organization', 'LocalBusiness'],
        '@id'   => $orgId,
        'name'  => $site,
        'url'   => $home,
        'logo'  => ['@type' => 'ImageObject', 'url' => $defOg],
        'image' => $defOg,
        'telephone'  => $tel,
        'priceRange' => '$$',
        'address' => ['@type' => 'PostalAddress', 'streetAddress' => $addr, 'addressLocality' => 'Bogotá', 'addressRegion' => 'Cundinamarca', 'addressCountry' => 'CO'],
        'areaServed' => ['Bogotá', 'Medellín', 'Cali', 'Bucaramanga', 'Manizales', 'Cundinamarca', 'Colombia'],
        'sameAs' => $sameAs ?: null,
        'openingHoursSpecification' => ['@type' => 'OpeningHoursSpecification', 'dayOfWeek' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'], 'opens' => '08:00', 'closes' => '18:00'],
    ]);

    // Sitio web
    $graph[] = [
        '@type' => 'WebSite',
        '@id'   => $webId,
        'url'   => $home,
        'name'  => $site,
        'inLanguage' => 'es-CO',
        'publisher'  => ['@id' => $orgId],
    ];

    // Migas (breadcrumbs)
    $hasCrumbs = ! empty($breadcrumbs) && is_iterable($breadcrumbs);
    if ($hasCrumbs) {
        $items = [];
        $i = 1;
        foreach ($breadcrumbs as $b) {
            $items[] = array_filter(['@type' => 'ListItem', 'position' => $i++, 'name' => $b['name'], 'item' => $b['url'] ?? null]);
        }
        $graph[] = ['@type' => 'BreadcrumbList', '@id' => $canon.'#breadcrumb', 'itemListElement' => $items];
    }

    // Tipo de página (WebPage / AboutPage / ...)
    $pageType = ($model->schema_type ?? null) && in_array($model->schema_type, ['AboutPage', 'ContactPage', 'CollectionPage', 'FAQPage'])
        ? $model->schema_type : 'WebPage';

    $webPageNode = array_filter([
        '@type' => $pageType,
        '@id'   => $pageId,
        'url'   => $canon,
        'name'  => $title,
        'description' => $desc,
        'inLanguage'  => 'es-CO',
        'isPartOf'    => ['@id' => $webId],
        'about'       => ['@id' => $orgId],
        'primaryImageOfPage' => $og ? ['@type' => 'ImageObject', 'url' => $og] : null,
        'breadcrumb'  => $hasCrumbs ? ['@id' => $canon.'#breadcrumb'] : null,
    ]);
    $graph[] = $webPageNode;

    // Artículo / BlogPosting (noticias-blog)
    if (! empty($article) && is_object($article)) {
        $graph[] = array_filter([
            '@type'    => 'BlogPosting',
            '@id'      => $canon.'#article',
            'isPartOf' => ['@id' => $pageId],
            'mainEntityOfPage' => ['@id' => $pageId],
            'headline' => Str::limit($article->title, 110, ''),
            'description'   => $desc,
            'image'         => $og ? [$og] : null,
            'datePublished' => optional($article->published_at)->toIso8601String(),
            'dateModified'  => optional($article->updated_at ?? $article->published_at)->toIso8601String(),
            'author'    => [
                '@type'       => 'Person',
                'name'        => method_exists($article, 'authorName') ? $article->authorName() : $site,
                'jobTitle'    => method_exists($article, 'authorRole') ? $article->authorRole() : null,
                'worksFor'    => ['@id' => $orgId],
            ],
            'publisher' => ['@id' => $orgId],
            'wordCount' => str_word_count(strip_tags((string) ($article->body ?? ''))),
            'inLanguage' => 'es-CO',
        ]);
    }

    // Producto / Servicio (páginas de producto)
    if ($model && in_array($model->schema_type ?? null, ['Product', 'Service'], true)) {
        if ($model->schema_type === 'Product') {
            $graph[] = array_filter([
                '@type' => 'Product',
                '@id'   => $canon.'#product',
                'name'  => $model->title,
                'description' => $desc,
                'image' => $og ?: null,
                'brand' => ['@type' => 'Brand', 'name' => $site],
                'category' => 'Chaquetas y busos de promoción',
            ]);
        } else {
            $graph[] = array_filter([
                '@type' => 'Service',
                '@id'   => $canon.'#service',
                'name'  => $model->title,
                'description' => $desc,
                'image' => $og ?: null,
                'provider'   => ['@id' => $orgId],
                'areaServed' => 'Colombia',
                'serviceType' => 'Confección de chaquetas y busos de promoción',
            ]);
        }
    }

    // FAQ
    if (isset($faqs) && $faqs->count()) {
        $graph[] = ['@type' => 'FAQPage', '@id' => $canon.'#faq', 'mainEntity' => $faqs->map(fn ($f) => [
            '@type' => 'Question', 'name' => $f->question,
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => strip_tags($f->answer)],
        ])->values()->all()];
    }

    $ld = ['@context' => 'https://schema.org', '@graph' => array_values($graph)];
    $j  = fn ($d) => json_encode($d, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
@endphp
<title>{{ $title }}</title>
<meta name="description" content="{{ $desc }}">
<meta name="keywords" content="{{ $kw }}">
<link rel="canonical" href="{{ $canon }}">
<meta name="robots" content="{{ $noindex ? 'noindex,nofollow' : 'index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1' }}">
<meta property="og:type" content="{{ !empty($article) ? 'article' : 'website' }}">
<meta property="og:title" content="{{ $title }}">
<meta property="og:description" content="{{ $desc }}">
<meta property="og:url" content="{{ $canon }}">
<meta property="og:site_name" content="{{ $site }}">
<meta property="og:locale" content="es_CO">
@if($og)<meta property="og:image" content="{{ $og }}">@endif
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $title }}">
<meta name="twitter:description" content="{{ $desc }}">
@if($og)<meta name="twitter:image" content="{{ $og }}">@endif
<script type="application/ld+json">{!! $j($ld) !!}</script>
