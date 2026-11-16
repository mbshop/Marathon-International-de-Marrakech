<?php
declare(strict_types=1);

require __DIR__ . '/inc/bootstrap.php';

$page = 'parcours';
$GLOBALS['page'] = $page;
$navPos = mim_nav_position($page);

$title = t('title');
$description = t('description');
$ogDescription = t('og_description');

require __DIR__ . '/inc/header.php';
?>
      <header class="page-head" id="top">
        <div class="wrap">
          <div class="page-head-top">
            <p class="tag"><?= p('head.kicker', ['no' => $navPos['no'], 'nav' => t('nav.parcours', [], 'common')]) ?></p>
            <p class="page-head-index"><?= p('head.index', ['cur' => $navPos['no'], 'total' => $navPos['total']]) ?></p>
          </div>
          <h1><?= p('head.title') ?></h1>
          <p class="sec-lede">
            <?= t('head.lede') ?>
          </p>
        </div>
      </header>

      <section class="section section-bone-2" aria-labelledby="reperes-title">
        <div class="wrap parcours">
          <div class="parcours-copy">
            <h2 class="block-title" id="reperes-title"><?= p('reperes.title') ?></h2>

            <ul class="km-list">
              <li><span class="km"><?= p('reperes.r1.term') ?></span><span><?= p('reperes.r1.text') ?></span></li>
              <li><span class="km"><?= p('reperes.r2.term') ?></span><span><?= p('reperes.r2.text') ?></span></li>
              <li><span class="km"><?= p('reperes.r3.term') ?></span><span><?= t('reperes.r3.text') ?></span></li>
              <li><span class="km"><?= p('reperes.r4.term') ?></span><span><?= p('reperes.r4.text') ?></span></li>
              <li><span class="km"><?= p('reperes.r5.term') ?></span><span><?= p('reperes.r5.text') ?></span></li>
            </ul>

            <div class="row-links">
              <a class="link-arrow" href="https://www.marathonmarrakech.ma/course" target="_blank" rel="noopener noreferrer"><?= p('links.plans') ?><span aria-hidden="true">↗</span></a>
              <a class="link-arrow" href="https://maps.app.goo.gl/mD3aqtaZFPVz8Lzq6" target="_blank" rel="noopener noreferrer"><?= p('links.zone') ?><span aria-hidden="true">↗</span></a>
            </div>
          </div>

          <figure class="parcours-fig">
            <img src="assets/medina-course.jpg" alt="<?= p('fig.alt', ['brand' => t('brand_short', [], 'common')]) ?>" width="1100" height="880" loading="lazy" />
            <figcaption><?= p('fig.caption') ?></figcaption>
          </figure>
        </div>
      </section>

      <a class="page-next" href="<?= e($navPos['next']) ?>.php" aria-label="<?= p('next.aria', ['nav' => t('nav.' . $navPos['next'], [], 'common')]) ?>">
        <div class="wrap page-next-inner">
          <div>
            <p class="page-next-label"><?= p('next.label', ['no' => $navPos['nextNo']]) ?></p>
            <p class="page-next-title"><?= p('nav.' . $navPos['next'], [], 'common') ?></p>
          </div>
          <span class="page-next-go" aria-hidden="true">→</span>
        </div>
      </a>
<?php require __DIR__ . '/inc/footer.php'; ?>
