<?php
declare(strict_types=1);

$page = $page ?? 'index';
$GLOBALS['page'] = $page;
$navPages = mim_nav_pages();
$lang = mim_lang();
$langDir = mim_lang_dir();
$schedule = mim_schedule();
$ogTitle = $ogTitle ?? $title;
$ogDescription = $ogDescription ?? $description;
$ogImage = $ogImage ?? 'assets/menara-hero.jpg';
$showLangGate = mim_needs_lang_choice();
$mimPageScript = basename((string) ($_SERVER['SCRIPT_NAME'] ?? 'index.php'));

$mimScheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$mimHost = (string) ($_SERVER['HTTP_HOST'] ?? '');
$mimBase = $mimScheme . '://' . $mimHost;
/* Chaque langue garde sa propre URL canonique : une seule adresse « neutre » fusionnerait les quatre versions. */
$mimPageBase = mim_page_url();
$mimCanonical = mim_page_url(true);
$mimEventName = 'Marathon International de Marrakech';
$mimEvent = [
    '@context' => 'https://schema.org',
    '@type' => 'SportsEvent',
    'name' => $mimEventName,
    'alternateName' => 'MIM — ' . strip_tags(mim_edition_label($schedule['edition'])),
    'inLanguage' => $lang,
    'sport' => 'Running',
    'description' => t('seo.event_description', ['name' => $mimEventName, 'edition' => (string) $schedule['edition']], 'common'),
    'startDate' => mim_target_iso(),
    'eventStatus' => 'https://schema.org/EventScheduled',
    'location' => [
        '@type' => 'Place',
        'name' => t('seo.venue', [], 'common'),
        'address' => [
            '@type' => 'PostalAddress',
            'addressLocality' => 'Marrakech',
            'addressRegion' => 'Marrakech-Safi',
            'addressCountry' => 'MA',
        ],
    ],
    'organizer' => [
        '@type' => 'Organization',
        'name' => $mimEventName,
        'url' => 'https://www.marathonmarrakech.ma/',
    ],
    'subEvent' => [
        [
            '@type' => 'SportsEvent',
            'name' => t('seo.sub_event_semi', ['name' => $mimEventName], 'common'),
            'sport' => 'Running',
            'startDate' => mim_dt($schedule['semi_date'], $schedule['semi_start'])->format('c'),
        ],
        [
            '@type' => 'SportsEvent',
            'name' => t('seo.sub_event_tous', ['name' => $mimEventName], 'common'),
            'sport' => 'Running',
            'startDate' => mim_dt($schedule['tous_date'], $schedule['tous_start'])->format('c'),
        ],
    ],
];

/* Les données structurées n’ont de sens qu’avec des URL absolues. */
$mimJsonFlags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
$mimPageUrl = $mimCanonical;

if ($mimPageUrl !== '') {
    $mimEvent['image'] = mim_absolute($ogImage);
    $mimEvent['eventAttendanceMode'] = 'https://schema.org/OfflineEventAttendanceMode';
}

$mimEventJson = json_encode($mimEvent, $mimJsonFlags);

$mimBreadcrumbJson = '';
if ($mimPageUrl !== '' && $page !== 'index' && isset($navPages[$page])) {
    $mimBreadcrumbJson = json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'inLanguage' => $lang,
        'itemListElement' => [
            [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => t('nav.index', [], 'common'),
                'item' => rtrim(dirname($mimPageBase), '/') . '/index.php',
            ],
            [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => t('nav.' . $page, [], 'common'),
                'item' => $mimPageUrl,
            ],
        ],
    ], $mimJsonFlags);
}
?>
<!doctype html>
<html lang="<?= e($lang) ?>" dir="<?= e($langDir) ?>">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= e($title) ?></title>
    <meta name="description" content="<?= e($description) ?>" />
    <meta name="theme-color" content="#14121A" />
    <meta name="robots" content="max-image-preview:large" />
    <?php if ($mimCanonical !== ''): ?>
      <link rel="canonical" href="<?= e($mimCanonical) ?>" />
      <?php foreach (array_keys(MIM_LOCALES) as $altLocale): ?>
        <link rel="alternate" hreflang="<?= e($altLocale) ?>" href="<?= e($mimPageBase) ?>?lang=<?= e($altLocale) ?>" />
      <?php endforeach; ?>
      <link rel="alternate" hreflang="x-default" href="<?= e($mimPageBase) ?>" />
    <?php endif; ?>
    <meta property="og:type" content="website" />
    <meta property="og:locale" content="<?= e(MIM_LOCALES[$lang]['og']) ?>" />
    <?php foreach (MIM_LOCALES as $altCode => $altLocale): ?>
      <?php if ($altCode !== $lang): ?>
        <meta property="og:locale:alternate" content="<?= e($altLocale['og']) ?>" />
      <?php endif; ?>
    <?php endforeach; ?>
    <meta property="og:site_name" content="<?= e($mimEventName) ?>" />
    <meta property="og:title" content="<?= e($ogTitle) ?>" />
    <meta property="og:description" content="<?= e($ogDescription) ?>" />
    <?php if ($mimHost !== ''): ?>
      <meta property="og:url" content="<?= e($mimCanonical) ?>" />
      <meta property="og:image" content="<?= e(mim_absolute($ogImage)) ?>" />
      <meta property="og:image:alt" content="<?= e($ogDescription) ?>" />
      <meta name="twitter:card" content="summary_large_image" />
      <meta name="twitter:image:alt" content="<?= e($ogDescription) ?>" />
      <meta name="twitter:title" content="<?= e($ogTitle) ?>" />
      <meta name="twitter:description" content="<?= e($ogDescription) ?>" />
      <meta name="twitter:image" content="<?= e(mim_absolute($ogImage)) ?>" />
    <?php else: ?>
      <meta property="og:image" content="<?= e($ogImage) ?>" />
    <?php endif; ?>
    <script type="application/ld+json"><?= $mimEventJson ?></script>
    <?php if ($mimBreadcrumbJson !== ''): ?>
      <script type="application/ld+json"><?= $mimBreadcrumbJson ?></script>
    <?php endif; ?>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <?= $extraHead ?? '' ?>
    <link
      href="https://fonts.googleapis.com/css2?family=Big+Shoulders+Display:wght@600;700;800;900&family=IBM+Plex+Mono:wght@400;500;600&family=IBM+Plex+Sans:wght@400;500;600<?php if ($lang === 'ar'): ?>&family=IBM+Plex+Sans+Arabic:wght@400;500;600;700<?php endif; ?>&display=swap"
      rel="stylesheet"
    />
    <link rel="stylesheet" href="<?= e(mim_asset('styles.css')) ?>" />
    <link rel="icon" type="image/png" sizes="96x96" href="assets/favicon.png" />
    <link rel="apple-touch-icon" href="assets/apple-touch-icon.png" />
  </head>
  <body>
    <a class="skip" href="#main"><?= p('ui.skip', [], 'common') ?></a>

    <div class="read-progress" aria-hidden="true"><span data-progress></span></div>

    <?php if ($showLangGate): ?>
      <div class="lang-gate" role="dialog" aria-modal="true" aria-labelledby="lang-gate-title">
        <div class="lang-gate-card">
          <p class="tag"><?= p('lang.choose_hint', [], 'common') ?></p>
          <h2 id="lang-gate-title"><?= p('lang.choose_title', [], 'common') ?></h2>
          <p class="lang-gate-lede"><?= p('lang.choose_lede', [], 'common') ?></p>
          <ul class="lang-gate-list">
            <?php foreach (MIM_LOCALES as $code => $locale): ?>
              <li>
                <a class="lang-gate-option<?= $code === 'fr' ? ' is-suggested' : '' ?>" href="<?= e($mimPageScript) ?>?lang=<?= e($code) ?>" hreflang="<?= e($code) ?>" lang="<?= e($code) ?>">
                  <span class="lang-gate-native"><?= e($locale['native']) ?></span>
                  <span class="lang-gate-code"><?= e($locale['short']) ?></span>
                </a>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>
    <?php endif; ?>

    <header class="masthead">
      <div class="mast-inner">
        <a class="logo-plate" href="index.php" aria-label="<?= p('brand', [], 'common') ?>">
          <img src="assets/logo.png" alt="<?= p('brand', [], 'common') ?>" width="500" height="143" />
        </a>

        <nav class="nav" id="nav" aria-label="<?= p('ui.nav_main', [], 'common') ?>">
          <?php foreach ($navPages as $key => $number): ?>
            <a href="<?= e($key) ?>.php"<?= $page === $key ? ' aria-current="page"' : '' ?>><span><?= e($number) ?></span><?= p('nav.' . $key, [], 'common') ?></a>
          <?php endforeach; ?>
        </nav>

        <div class="mast-tools">
          <div class="lang-switch" role="group" aria-label="<?= p('lang.label', [], 'common') ?>">
            <?php foreach (MIM_LOCALES as $code => $locale): ?>
              <a href="<?= e(mim_lang_url($code)) ?>" hreflang="<?= e($code) ?>" lang="<?= e($code) ?>"<?= $code === $lang ? ' aria-current="true"' : '' ?>><span class="lang-switch-code"><?= e($locale['short']) ?></span><span class="sr-only"><?= e($locale['native']) ?></span></a>
            <?php endforeach; ?>
          </div>

          <a class="btn btn-signal mast-cta" href="inscriptions.php"><?= p('ui.register', [], 'common') ?></a>
        </div>

        <button class="burger" type="button" aria-expanded="false" aria-controls="nav">
          <span class="sr-only"><?= p('ui.open_menu', [], 'common') ?></span>
          <span aria-hidden="true"></span>
        </button>
      </div>
    </header>

    <aside class="km-rail" aria-hidden="true">
      <div class="rail-line">
        <ol class="rail-ticks">
          <li style="--at: 0%">0</li>
          <li style="--at: 23.7%"><?= e(mim_l10n_decimal('10')) ?></li>
          <li style="--at: 50%"><?= e(mim_l10n_decimal('21.1')) ?></li>
          <li style="--at: 71.1%"><?= e(mim_l10n_decimal('30')) ?></li>
          <li style="--at: 100%"><?= e(mim_l10n_decimal('42.195')) ?></li>
        </ol>
        <span class="rail-dot" data-rail-dot></span>
      </div>
      <p class="rail-readout"><span data-km><?= e(mim_l10n_decimal('0.0')) ?></span><span class="rail-unit"><?= p('rail.unit', [], 'common') ?></span></p>
    </aside>

    <main id="main">
