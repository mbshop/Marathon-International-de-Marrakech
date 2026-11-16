<?php
declare(strict_types=1);

require __DIR__ . '/inc/bootstrap.php';

/**
 * Plan de site XML : les 12 pages publiques dans les quatre langues, plus les photos
 * publiées de la galerie. Les URL doivent être absolues : sans hôte, le fichier est vide.
 */

header('Content-Type: application/xml; charset=UTF-8');
header('X-Robots-Tag: noindex');

$mimXml = static fn (string $value): string => htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');

/** @return array{0: string, 1: string} [changefreq, priority] */
function mim_sitemap_weight(string $key): array
{
    if ($key === 'index') {
        return ['daily', '1.0'];
    }
    if ($key === 'inscriptions') {
        return ['weekly', '0.9'];
    }
    if (in_array($key, ['epreuves', 'parcours', 'programme', 'primes', 'village', 'galerie', 'infos'], true)) {
        return ['weekly', '0.7'];
    }

    return ['yearly', '0.3'];
}

/** Date de dernière touche, fichier de page et catalogues compris, en ISO 8601. */
function mim_sitemap_lastmod(string $script, string $key, string $locale): string
{
    /* Une seule lecture du stockage de configuration pour tout le plan de site. */
    static $configStamps = null;

    if ($configStamps === null) {
        $configStamps = [
            mim_store_updated('schedule'),
            mim_store_updated('site'),
            mim_store_updated('gallery'),
        ];
    }

    $stamps = [
        (int) @filemtime(MIM_ROOT . '/' . $script),
        (int) @filemtime(MIM_ROOT . '/lang/' . $locale . '/' . $key . '.php'),
        (int) @filemtime(MIM_ROOT . '/lang/fr/' . $key . '.php'),
        $configStamps[0],
        $configStamps[1],
    ];

    if ($key === 'galerie') {
        $stamps[] = $configStamps[2];
    }

    $last = max($stamps);

    return $last > 0 ? date('c', $last) : '';
}

$pages = [];
foreach (MIM_NAV as $key) {
    $pages[$key . '.php'] = $key;
}
foreach (mim_legal_pages() as $key => $script) {
    $pages[$script] = $key;
}

$photos = mim_gallery_photos();
$locales = array_keys(MIM_LOCALES);
$origin = mim_origin();

echo '<?xml version="1.0" encoding="UTF-8"?>', "\n";

if ($origin === '') {
    echo '<!-- Host inconnu : le plan de site exige des URL absolues. -->', "\n";
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>', "\n";
    exit;
}

echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">', "\n";

foreach ($pages as $script => $key) {
    [$freq, $prio] = mim_sitemap_weight($key);

    foreach ($locales as $locale) {
        $base = $origin . '/' . $script;
        $lastmod = mim_sitemap_lastmod($script, $key, $locale);

        echo "  <url>\n";
        echo '    <loc>' . $mimXml($base . '?lang=' . $locale) . "</loc>\n";

        if ($lastmod !== '') {
            echo '    <lastmod>' . $lastmod . "</lastmod>\n";
        }

        foreach ($locales as $alt) {
            echo '    <xhtml:link rel="alternate" hreflang="' . $alt . '" href="' . $mimXml($base . '?lang=' . $alt) . "\" />\n";
        }
        echo '    <xhtml:link rel="alternate" hreflang="x-default" href="' . $mimXml($base) . "\" />\n";

        echo '    <changefreq>' . $freq . "</changefreq>\n";
        echo '    <priority>' . $prio . "</priority>\n";

        /* Les photos ne sont déclarées que sous l’URL de la galerie. */
        if ($key === 'galerie') {
            foreach (array_slice($photos, 0, 100, true) as $photo) {
                $title = trim((string) ($photo['alt'][$locale] ?? '')) !== '' ? $photo['alt'][$locale] : $photo['alt']['fr'];
                $caption = trim((string) ($photo['caption'][$locale] ?? '')) !== '' ? $photo['caption'][$locale] : $photo['caption']['fr'];

                echo "    <image:image>\n";
                echo '      <image:loc>' . $mimXml($origin . '/' . $photo['file']) . "</image:loc>\n";
                if ($photo['thumb'] !== '') {
                    echo '      <image:thumbnail>' . $mimXml($origin . '/' . $photo['thumb']) . "</image:thumbnail>\n";
                }
                if ($title !== '') {
                    echo '      <image:title>' . $mimXml($title) . "</image:title>\n";
                }
                if ($caption !== '') {
                    echo '      <image:caption>' . $mimXml($caption) . "</image:caption>\n";
                }
                echo "    </image:image>\n";
            }
        }

        echo "  </url>\n";
    }
}

echo '</urlset>', "\n";
