<?php
declare(strict_types=1);

require __DIR__ . '/inc/bootstrap.php';

$schedule = mim_schedule();
$days = mim_programme_days();

$page = 'village';
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
            <p class="tag"><?= p('head.tag', ['no' => $navPos['no'], 'label' => t('nav.village', [], 'common')]) ?></p>
            <p class="page-head-index"><?= p('head.index', ['cur' => $navPos['no'], 'total' => $navPos['total']]) ?></p>
          </div>
          <h1><?= p('head.title') ?></h1>
          <p class="sec-lede"><?= p('head.lede') ?></p>
        </div>
      </header>

      <section class="section" aria-labelledby="retrait-title">
        <div class="wrap">
          <h2 class="block-title" id="retrait-title"><?= p('retrait.title') ?></h2>
          <ul class="km-list">
            <li><span class="km"><?= e(mim_l10n_date_day_dot($days['jeu'], false)) ?></span><span><?= p('retrait.jeu') ?></span></li>
            <li><span class="km"><?= e(mim_l10n_date_day_dot($days['ven'], false)) ?></span><span><?= p('retrait.ven') ?></span></li>
            <li><span class="km"><?= e(mim_l10n_date_day_dot($days['sam'], false)) ?></span><span><?= p('retrait.sam') ?></span></li>
            <li><span class="km"><?= e(mim_l10n_date_day_dot($schedule['marathon_date'], false)) ?></span><span><?= t('retrait.dim') ?></span></li>
          </ul>

          <h2 class="block-title" id="sur-place-title"><?= p('sur_place.title') ?></h2>
          <div class="notes">
            <article class="note">
              <p class="note-label"><?= p('access.label') ?></p>
              <h3><?= p('access.title') ?></h3>
              <p><?= t('access.text') ?></p>
            </article>
            <article class="note">
              <p class="note-label"><?= p('start.label') ?></p>
              <h3><?= p('start.title') ?></h3>
              <p><?= p('start.text', ['marathon_time' => mim_l10n_hour($schedule['marathon_start']), 'semi_time' => mim_l10n_hour($schedule['semi_start'])]) ?></p>
            </article>
            <article class="note">
              <p class="note-label"><?= p('after.label') ?></p>
              <h3><?= p('after.title') ?></h3>
              <p><?= p('after.text') ?></p>
              <a class="link-arrow" href="https://www.marathonmarrakech.ma/results" target="_blank" rel="noopener noreferrer"><?= p('after.link') ?><span aria-hidden="true">↗</span></a>
            </article>
          </div>
        </div>
      </section>

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
