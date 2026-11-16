<?php
declare(strict_types=1);

require __DIR__ . '/inc/bootstrap.php';

/*
 * robots.txt généré : l’URL du plan de site dépend de l’hôte réel du serveur,
 * et le nom de domaine n’est pas une information à recopier à la main dans un fichier.
 */

header('Content-Type: text/plain; charset=UTF-8');
header('X-Robots-Tag: noindex');

$lines = [
    'User-agent: *',
    'Disallow: /adminbtx.php',
    'Disallow: /reg-app.php',
    'Disallow: /data/',
    'Disallow: /inc/',
    'Allow: /',
    '',
];

$origin = mim_origin();

if ($origin !== '') {
    $lines[] = 'Sitemap: ' . $origin . '/sitemap.xml';
}

echo implode("\n", $lines) . "\n";
