<?php
declare(strict_types=1);

require __DIR__ . '/inc/bootstrap.php';

$page = 'galerie';
$GLOBALS['page'] = $page;
$navPos = mim_nav_position($page);

$albums = mim_gallery_albums();
$photoCount = 0;
$flat = [];

/* Un indice global sur toutes les photos, quelle que soit l’album : la visionneuse navigue d’un album à l’autre. */
foreach ($albums as $albumKey => $album) {
    foreach ($album['photos'] as $file => $photo) {
        $photo['index'] = $photoCount;
        $albums[$albumKey]['photos'][$file] = $photo;
        $flat[$file] = $photo;
        $photoCount++;
    }
}

$title = t('title');
$description = t('description');
$ogDescription = t('og_description');

/* Le partage social montre la première photo plutôt que l’affiche du site. */
$firstPhoto = $photoCount === 0 ? null : reset($flat);
$ogImage = $firstPhoto === null ? 'assets/menara-hero.jpg' : $firstPhoto['file'];

/* Les données structurées d’images exigent des URL absolues : rien sans hôte. */
$extraHead = '';

if ($firstPhoto !== null && mim_origin() !== '') {
    $mimGalleryJson = json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'ImageGallery',
        'name' => $title,
        'description' => $description,
        'url' => mim_page_url(true),
        'inLanguage' => mim_lang(),
        'associatedMedia' => array_values(array_map(
            static function (array $photo): array {
                $item = [
                    '@type' => 'ImageObject',
                    'contentUrl' => mim_absolute($photo['file']),
                    'name' => mim_gallery_alt($photo),
                    'width' => $photo['width'],
                    'height' => $photo['height'],
                ];
                if ($photo['thumb'] !== '') {
                    $item['thumbnailUrl'] = mim_absolute($photo['thumb']);
                }
                $caption = mim_gallery_text($photo, 'caption');
                if ($caption !== '') {
                    $item['caption'] = $caption;
                }
                if ($photo['credit'] !== '') {
                    $item['creditText'] = $photo['credit'];
                }
                if ($photo['taken'] !== '') {
                    $item['dateCreated'] = $photo['taken'];
                }

                return $item;
            },
            array_slice($flat, 0, 100, true)
        )),
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

    $extraHead = '<script type="application/ld+json">' . $mimGalleryJson . '</script>';
}

require __DIR__ . '/inc/header.php';
?>
      <header class="page-head" id="top">
        <div class="wrap">
          <div class="page-head-top">
            <p class="tag"><?= p('head.tag', ['no' => $navPos['no'], 'label' => t('nav.galerie', [], 'common')]) ?></p>
            <p class="page-head-index"><?= p('head.index', ['cur' => $navPos['no'], 'total' => $navPos['total']]) ?></p>
          </div>
          <h1><?= p('head.title') ?></h1>
          <p class="sec-lede"><?= t('head.lede') ?></p>
          <p class="gal-meta">
            <?= $photoCount > 0
              ? p('head.meta', ['count' => mim_l10n_decimal((string) $photoCount), 'albums' => mim_l10n_decimal((string) count($albums))])
              : p('head.meta_none') ?>
          </p>
        </div>
      </header>

<?php if (!$albums): ?>
      <section class="section" aria-labelledby="gal-empty-title">
        <div class="wrap">
          <h2 class="block-title" id="gal-empty-title"><?= p('empty.title') ?></h2>
          <p class="sec-lede"><?= t('empty.text') ?></p>
          <a class="btn btn-signal" href="inscriptions.php"><?= p('empty.cta') ?></a>
        </div>
      </section>
<?php else: ?>
      <nav class="gal-index" aria-label="<?= p('index.aria') ?>">
        <div class="wrap gal-index-inner">
          <p class="gal-index-title"><?= p('index.title') ?></p>
          <ul class="gal-index-list">
            <?php foreach ($albums as $album): ?>
              <li>
                <a href="#album-<?= (int) $album['edition'] ?>">
                  <span class="gal-index-edition"><?= mim_edition_label((int) $album['edition']) ?></span>
                  <span class="gal-index-count"><?= p('album.count', ['count' => mim_l10n_decimal((string) count($album['photos']))]) ?></span>
                </a>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
      </nav>

      <?php foreach ($albums as $album): ?>
        <?php
        $photos = array_values($album['photos']);
        $lead = array_shift($photos);
        $leadRatio = $lead['height'] > 0 ? $lead['width'] / $lead['height'] : 1;

        /* La planche de tête garde sa proportion, bornée pour ne pas déborder d’un écran. */
        $leadAspect = number_format(min(2.4, max(1.2, $leadRatio)), 3, '.', '');
        $leadCaption = mim_gallery_text($lead, 'caption');
        $leadAlt = mim_gallery_alt($lead);
        ?>
        <section class="section gal-album" id="album-<?= (int) $album['edition'] ?>" aria-labelledby="gal-album-<?= (int) $album['edition'] ?>">
          <div class="wrap">
            <div class="gal-album-head">
              <h2 class="block-title" id="gal-album-<?= (int) $album['edition'] ?>">
                <?= mim_edition_label((int) $album['edition']) ?>
              </h2>
              <p class="gal-album-count"><?= p('album.count', ['count' => mim_l10n_decimal((string) count($album['photos']))]) ?></p>
              <span class="gal-album-rule" aria-hidden="true"></span>
            </div>

            <figure class="gal-lead">
              <a
                class="gal-link gal-link-lead"
                href="<?= e($lead['file']) ?>"
                style="--ar: <?= $leadAspect ?>"
                data-gal-index="<?= (int) $lead['index'] ?>"
                data-caption="<?= e($leadCaption) ?>"
                data-credit="<?= e($lead['credit']) ?>"
                data-edition="<?= e(strip_tags(mim_edition_label((int) $lead['edition']))) ?>"
                data-taken="<?= $lead['taken'] === '' ? '' : e(mim_l10n_date_long($lead['taken'])) ?>"
                aria-label="<?= e(t('item.open', ['index' => mim_l10n_decimal((string) ($lead['index'] + 1)), 'total' => mim_l10n_decimal((string) $photoCount), 'alt' => $leadAlt])) ?>"
              >
                <img
                  src="<?= e($lead['thumb'] !== '' ? $lead['thumb'] : $lead['file']) ?>"
                  alt="<?= e($leadAlt) ?>"
                  width="<?= (int) $lead['width'] ?>"
                  height="<?= (int) $lead['height'] ?>"
                  <?= $lead['index'] === 0 ? 'loading="eager" fetchpriority="high"' : 'loading="lazy" decoding="async"' ?>
                />
                <span class="gal-num" aria-hidden="true"><?= e(mim_l10n_decimal(str_pad((string) ($lead['index'] + 1), 2, '0', STR_PAD_LEFT))) ?></span>
              </a>
              <?php if ($leadCaption !== '' || $lead['credit'] !== '' || $lead['taken'] !== ''): ?>
                <figcaption class="gal-lead-cap">
                  <p class="gal-lead-title"><?= e($leadCaption) ?></p>
                  <?php
                  $leadMeta = [];
                  if ($lead['credit'] !== '') {
                      $leadMeta[] = t('photo.credit') . ' ' . $lead['credit'];
                  }
                  if ($lead['taken'] !== '') {
                      $leadMeta[] = t('photo.date') . ' ' . mim_l10n_date_long($lead['taken']);
                  }
                  ?>
                  <?php if ($leadMeta): ?>
                    <p class="gal-lead-meta"><?= e(implode(' · ', $leadMeta)) ?></p>
                  <?php endif; ?>
                </figcaption>
              <?php endif; ?>
            </figure>

            <?php if ($photos): ?>
              <ul class="gal-grid">
                <?php foreach ($photos as $photo): ?>
                  <?php
                  $alt = mim_gallery_alt($photo);
                  $caption = mim_gallery_text($photo, 'caption');
                  $ratio = $photo['height'] > 0 ? $photo['width'] / $photo['height'] : 1;

                  /* Une seule grande vignette par album : c’est elle qui donne le rythme de la page. */
                  if ($photo['index'] === $lead['index'] + 1 && $ratio > 1.05 && $ratio < 2.2) {
                      $shape = ' is-hero';
                  } else {
                      $shape = $ratio >= 1.9 ? ' is-wide' : ($ratio <= 0.85 ? ' is-tall' : '');
                  }
                  ?>
                  <li class="gal-item<?= $shape ?>">
                    <figure class="gal-fig">
                      <a
                        class="gal-link"
                        href="<?= e($photo['file']) ?>"
                        data-gal-index="<?= (int) $photo['index'] ?>"
                        data-caption="<?= e($caption) ?>"
                        data-credit="<?= e($photo['credit']) ?>"
                        data-edition="<?= e(strip_tags(mim_edition_label((int) $photo['edition']))) ?>"
                        data-taken="<?= $photo['taken'] === '' ? '' : e(mim_l10n_date_long($photo['taken'])) ?>"
                        aria-label="<?= e(t('item.open', ['index' => mim_l10n_decimal((string) ($photo['index'] + 1)), 'total' => mim_l10n_decimal((string) $photoCount), 'alt' => $alt])) ?>"
                      >
                        <img
                          src="<?= e($photo['thumb'] !== '' ? $photo['thumb'] : $photo['file']) ?>"
                          alt="<?= e($alt) ?>"
                          width="<?= (int) $photo['width'] ?>"
                          height="<?= (int) $photo['height'] ?>"
                          loading="lazy"
                          decoding="async"
                        />
                        <span class="gal-num" aria-hidden="true"><?= e(mim_l10n_decimal(str_pad((string) ($photo['index'] + 1), 2, '0', STR_PAD_LEFT))) ?></span>
                      </a>
                      <?php if ($caption !== ''): ?>
                        <figcaption class="gal-cap">
                          <span class="gal-cap-text"><?= e($caption) ?></span>
                          <?php if ($photo['credit'] !== ''): ?>
                            <span class="gal-cap-credit"><?= e(t('photo.credit')) ?> <?= e($photo['credit']) ?></span>
                          <?php endif; ?>
                        </figcaption>
                      <?php endif; ?>
                    </figure>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php endif; ?>
          </div>
        </section>
      <?php endforeach; ?>

      <dialog
        class="gal-lightbox"
        id="gal-lightbox"
        aria-label="<?= p('lightbox.title') ?>"
        data-counter="<?= e(t('lightbox.counter')) ?>"
        data-credit-label="<?= e(t('photo.credit')) ?>"
        data-date-label="<?= e(t('photo.date')) ?>"
      >
        <div class="gal-lb-stage">
          <img class="gal-lb-img" src="" alt="" />
        </div>
        <div class="gal-lb-bar">
          <p class="gal-lb-counter" data-gal-counter></p>
          <p class="gal-lb-caption" data-gal-caption></p>
          <p class="gal-lb-meta" data-gal-meta></p>
          <div class="gal-lb-actions">
            <button class="gal-lb-btn" type="button" data-gal-prev>
              <span aria-hidden="true">←</span><span class="sr-only"><?= p('lightbox.prev') ?></span>
            </button>
            <button class="gal-lb-btn" type="button" data-gal-next>
              <span aria-hidden="true">→</span><span class="sr-only"><?= p('lightbox.next') ?></span>
            </button>
            <button class="gal-lb-btn gal-lb-close" type="button" data-gal-close>
              <span aria-hidden="true">×</span><span class="sr-only"><?= p('lightbox.close') ?></span>
            </button>
          </div>
        </div>
      </dialog>
<?php endif; ?>

      <a class="page-next" href="<?= e($navPos['next']) ?>.php" aria-label="<?= p('next.aria', ['label' => t('nav.' . $navPos['next'], [], 'common')]) ?>">
        <div class="wrap page-next-inner">
          <div>
            <p class="page-next-label"><?= p('next.label', ['no' => $navPos['nextNo']]) ?></p>
            <p class="page-next-title"><?= p('nav.' . $navPos['next'], [], 'common') ?></p>
          </div>
          <span class="page-next-go" aria-hidden="true">→</span>
        </div>
      </a>
<?php require __DIR__ . '/inc/footer.php'; ?>
