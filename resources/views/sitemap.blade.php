<?php echo '<?xml version="1.0" encoding="UTF-8"?>'."\n"; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach($urls as $u)
    <url>
        <loc>{{ $u['loc'] }}</loc>
        @isset($u['lastmod'])<lastmod>{{ optional($u['lastmod'])->toAtomString() }}</lastmod>@endisset
        <changefreq>{{ $u['freq'] ?? 'monthly' }}</changefreq>
        <priority>{{ $u['priority'] ?? '0.5' }}</priority>
    </url>
@endforeach
</urlset>
