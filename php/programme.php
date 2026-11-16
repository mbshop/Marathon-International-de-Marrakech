<?php
declare(strict_types=1);

require __DIR__ . '/inc/bootstrap.php';

$schedule = mim_schedule();
$days = mim_programme_days();
$dayKeys = [4 => 'jeu', 5 => 'ven', 6 => 'sam', 7 => 'dim'];
$tousKey = $dayKeys[(int) mim_day($schedule['tous_date'])->format('N')] ?? 'sam';

$page = 'programme';
$GLOBALS['page'] = $page;
$navPos = mim_nav_position($page);

$weekday = static fn (string $date): string => mim_l10n_weekday((int) mim_day($date)->format('N'));
$dayShort = static fn (string $date): string => mim_l10n_weekday_dot($date, false);
$filterDay = static fn (string $date): string => t('sched.filter_day', [
    'wd' => $dayShort($date),
    'day' => mim_l10n_date($date, '{day}'),
]);
$schedDay = static fn (string $date): string => t('sched.day', [
    'wd' => $dayShort($date),
    'date' => mim_day($date)->format('d.m'),
]);
$raceTitle = static fn (string $race, string $distance): string => t('sched.race_title', [
    'name' => t($race, [], 'common'),
    'distance' => mim_l10n_decimal($distance),
    'unit' => t('rail.unit', [], 'common'),
]);

$brand = t('brand_short', [], 'common');
$title = t('title', ['brand' => $brand]);
$description = t('description', [
    'brand' => $brand,
    'village_weekday' => $weekday($days['jeu']),
    'village_day' => $filterDay($days['jeu']),
    'race_weekday' => $weekday($schedule['marathon_date']),
    'marathon_time' => mim_l10n_hour($schedule['marathon_start']),
    'semi_time' => mim_l10n_hour($schedule['semi_start']),
]);
$ogDescription = t('og_description');

require __DIR__ . '/inc/header.php';
?>
      <header class="page-head" id="top">
        <div class="wrap">
          <div class="page-head-top">
            <p class="tag"><?= p('head.tag', ['no' => $navPos['no'], 'name' => t('nav.programme', [], 'common')]) ?></p>
            <p class="page-head-index"><?= p('head.index', ['current' => $navPos['no'], 'total' => $navPos['total']]) ?></p>
          </div>
          <h1><?= p('head.title') ?></h1>
          <p class="sec-lede"><?= t('head.lede', ['village_weekday' => $weekday($days['jeu']), 'last_weekday' => $weekday($days['sam'])]) ?></p>
        </div>
      </header>

      <section class="section" aria-labelledby="sched-title">
        <div class="wrap">
          <div class="page-toolbar">
            <h2 id="sched-title" class="sr-only"><?= p('sched.title') ?></h2>
            <p><?= p('sched.lede') ?></p>
            <div class="filters" role="group" aria-label="<?= p('sched.filters_aria') ?>">
              <button class="filter is-active" data-day="all" aria-pressed="true" type="button"><?= p('sched.filter_all') ?></button>
              <button class="filter" data-day="jeu" aria-pressed="false" type="button"><?= e($filterDay($days['jeu'])) ?></button>
              <button class="filter" data-day="ven" aria-pressed="false" type="button"><?= e($filterDay($days['ven'])) ?></button>
              <button class="filter" data-day="sam" aria-pressed="false" type="button"><?= e($filterDay($days['sam'])) ?></button>
              <button class="filter" data-day="dim" aria-pressed="false" type="button"><?= e($filterDay($days['dim'])) ?></button>
            </div>
          </div>

          <ol class="schedule">
            <li class="sched-row" data-day="jeu">
              <span class="sched-day"><?= e($schedDay($days['jeu'])) ?></span>
              <span class="sched-time"><?= p('sched.hours_evening') ?></span>
              <div class="sched-body">
                <h3><?= p('sched.village.title') ?></h3>
                <p><?= p('sched.village.text') ?></p>
              </div>
            </li>
            <li class="sched-row" data-day="ven">
              <span class="sched-day"><?= e($schedDay($days['ven'])) ?></span>
              <span class="sched-time"><?= p('sched.hours_day') ?></span>
              <div class="sched-body">
                <h3><?= p('sched.bibs.title') ?></h3>
                <p><?= p('sched.bibs.text') ?></p>
              </div>
            </li>
            <li class="sched-row" data-day="<?= e($tousKey) ?>">
              <span class="sched-day"><?= e($schedDay($schedule['tous_date'])) ?></span>
              <span class="sched-time"><?= e(mim_time($schedule['tous_start'])) ?></span>
              <div class="sched-body">
                <h3><?= e($raceTitle('race.tous', '5')) ?></h3>
                <p><?= p('sched.tous.text') ?></p>
              </div>
            </li>
            <li class="sched-row" data-day="sam">
              <span class="sched-day"><?= e($schedDay($days['sam'])) ?></span>
              <span class="sched-time"><?= p('sched.hours_day') ?></span>
              <div class="sched-body">
                <h3><?= p('sched.closing.title') ?></h3>
                <p><?= t('sched.closing.text', ['closing_weekday' => $weekday($days['sam']), 'race_weekday' => $weekday($schedule['marathon_date'])]) ?></p>
              </div>
            </li>
            <li class="sched-row is-key" data-day="dim">
              <span class="sched-day"><?= e($schedDay($schedule['marathon_date'])) ?></span>
              <span class="sched-time"><?= e(mim_time($schedule['marathon_start'])) ?></span>
              <div class="sched-body">
                <h3><?= e($raceTitle('race.marathon', '42.195')) ?></h3>
                <p><?= t('sched.marathon.text') ?></p>
              </div>
            </li>
            <li class="sched-row is-key" data-day="dim">
              <span class="sched-day"><?= e($schedDay($schedule['semi_date'])) ?></span>
              <span class="sched-time"><?= e(mim_time($schedule['semi_start'])) ?></span>
              <div class="sched-body">
                <h3><?= e($raceTitle('race.semi', '21.097')) ?></h3>
                <p><?= t('sched.semi.text') ?></p>
              </div>
            </li>
          </ol>
        </div>
      </section>

      <a class="page-next" href="<?= e($navPos['next']) ?>.php" aria-label="<?= p('next.aria', ['name' => t('nav.' . $navPos['next'], [], 'common')]) ?>">
        <div class="wrap page-next-inner">
          <div>
            <p class="page-next-label"><?= p('next.label', ['no' => $navPos['nextNo']]) ?></p>
            <p class="page-next-title"><?= p('nav.' . $navPos['next'], [], 'common') ?></p>
          </div>
          <span class="page-next-go" aria-hidden="true">→</span>
        </div>
      </a>
<?php require __DIR__ . '/inc/footer.php'; ?>
