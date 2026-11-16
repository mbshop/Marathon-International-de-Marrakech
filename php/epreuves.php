<?php
declare(strict_types=1);

require __DIR__ . '/inc/bootstrap.php';

$schedule = mim_schedule();

$page = 'epreuves';
$GLOBALS['page'] = $page;
$navPos = mim_nav_position($page);

$marathonHour = mim_l10n_hour($schedule['marathon_start']);
$semiHour = mim_l10n_hour($schedule['semi_start']);
$marathonKm = mim_l10n_decimal('42.195') . ' km';
$semiKm = mim_l10n_decimal('21.097') . ' km';

$title = t('title');
$description = t('description', [
    'marathon_km' => $marathonKm,
    'marathon_time' => $marathonHour,
    'semi_km' => $semiKm,
    'semi_time' => $semiHour,
]);
$ogDescription = t('og_description');

$raceWhen = static fn (string $date, string $time): string => p('row.when', [
    'date' => mim_l10n_weekday_dot($date, false) . ' ' . mim_day($date)->format('d.m'),
    'time' => mim_time($time),
]);

require __DIR__ . '/inc/header.php';
?>
      <header class="page-head" id="top">
        <div class="wrap">
          <div class="page-head-top">
            <p class="tag"><?= p('head.kicker', ['no' => $navPos['no'], 'nav' => t('nav.epreuves', [], 'common')]) ?></p>
            <p class="page-head-index"><?= p('head.index', ['cur' => $navPos['no'], 'total' => $navPos['total']]) ?></p>
          </div>
          <h1><?= t('head.title') ?></h1>
          <p class="sec-lede">
            <?= p('head.lede') ?>
          </p>
        </div>
      </header>

      <section class="section" aria-labelledby="board-title">
        <h2 id="board-title" class="sr-only"><?= p('board.title') ?></h2>
        <div class="wrap">
          <div class="board">
            <div class="board-list" role="tablist" aria-label="<?= p('board.list_aria') ?>">
              <button
                class="board-row is-active"
                role="tab"
                id="tab-marathon"
                aria-controls="race-detail"
                aria-selected="true"
                data-race="marathon"
                type="button"
              >
                <span class="row-when"><?= $raceWhen($schedule['marathon_date'], $schedule['marathon_start']) ?></span>
                <span class="row-dist"><?= e(mim_l10n_decimal('42.195')) ?></span>
                <span class="row-name"><?= p('race.marathon', [], 'common') ?></span>
                <span class="row-go" aria-hidden="true">↗</span>
              </button>
              <button
                class="board-row"
                role="tab"
                id="tab-semi"
                aria-controls="race-detail"
                aria-selected="false"
                tabindex="-1"
                data-race="semi"
                type="button"
              >
                <span class="row-when"><?= $raceWhen($schedule['semi_date'], $schedule['semi_start']) ?></span>
                <span class="row-dist"><?= e(mim_l10n_decimal('21.097')) ?></span>
                <span class="row-name"><?= p('race.semi', [], 'common') ?></span>
                <span class="row-go" aria-hidden="true">↗</span>
              </button>
              <button
                class="board-row"
                role="tab"
                id="tab-tous"
                aria-controls="race-detail"
                aria-selected="false"
                tabindex="-1"
                data-race="tous"
                type="button"
              >
                <span class="row-when"><?= $raceWhen($schedule['tous_date'], $schedule['tous_start']) ?></span>
                <span class="row-dist"><?= e(mim_l10n_decimal('5')) ?></span>
                <span class="row-name"><?= p('race.tous', [], 'common') ?></span>
                <span class="row-go" aria-hidden="true">↗</span>
              </button>
            </div>

            <article
              class="board-detail"
              id="race-detail"
              role="tabpanel"
              aria-labelledby="tab-marathon"
              tabindex="0"
            >
              <div class="detail-head">
                <p class="detail-kicker" data-race-field="kicker"><?= p('js.races.marathon.kicker', [], 'common') ?></p>
                <h3 class="detail-name" data-race-field="name"><?= p('js.races.marathon.name', [], 'common') ?></h3>
                <p class="detail-blurb" data-race-field="blurb"></p>
              </div>

              <dl class="detail-facts">
                <div><dt><?= p('board.dist') ?></dt><dd data-race-field="distance"><?= p('js.races.marathon.distance', [], 'common') ?></dd></div>
                <div><dt><?= p('board.start') ?></dt><dd data-race-field="start"><?= e(mim_race_start('marathon')) ?></dd></div>
                <div><dt><?= p('board.limit') ?></dt><dd data-race-field="limit"><?= p('js.races.marathon.limit', [], 'common') ?></dd></div>
                <div><dt><?= p('board.splits') ?></dt><dd data-race-field="splits"><?= p('js.races.marathon.splits', [], 'common') ?></dd></div>
              </dl>

              <div class="detail-rule" data-race-rule>
                <p class="detail-rule-label"><?= p('regulation.label') ?></p>
                <p class="detail-rule-text" data-race-field="rule"><?= p('js.races.marathon.rule', [], 'common') ?></p>
              </div>

              <ul class="detail-notes" data-race-field="notes"></ul>

              <p class="detail-foot">
                <?= t('board.foot') ?>
              </p>
            </article>
          </div>
        </div>
      </section>

      <section class="section section-bone-2" aria-labelledby="during-title">
        <div class="wrap during">
          <header class="sec-head during-head">
            <p class="tag"><?= p('during.kicker') ?></p>
            <h2 id="during-title"><?= p('during.title') ?></h2>
            <p class="sec-lede"><?= p('during.lede') ?></p>
          </header>

          <ol class="during-list">
            <?php foreach ((array) mim_text('during.items') as $mimDuring): ?>
              <li><?= e($mimDuring) ?></li>
            <?php endforeach; ?>
          </ol>
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
