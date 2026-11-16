<?php
declare(strict_types=1);

require __DIR__ . '/inc/bootstrap.php';

$schedule = mim_schedule();
$edition = (int) $schedule['edition'];

$page = 'primes';
$GLOBALS['page'] = $page;
$navPos = mim_nav_position($page);

$rank = static fn (int $n): string => t('table.rank', ['n' => (string) $n, 'sup' => mim_l10n_edition_sup($n)]);
$rankRange = static fn (int $from, int $to): string => t('table.rank_range', [
    'from' => (string) $from,
    'fromsup' => mim_l10n_edition_sup($from),
    'to' => (string) $to,
    'tosup' => mim_l10n_edition_sup($to),
]);

$brand = t('brand_short', [], 'common');
$title = t('title', ['brand' => $brand]);
$description = t('description', ['brand' => $brand]);
$ogDescription = t('og_description');

require __DIR__ . '/inc/header.php';
?>
      <header class="page-head" id="top">
        <div class="wrap">
          <div class="page-head-top">
            <p class="tag"><?= p('head.tag', ['no' => $navPos['no'], 'name' => t('nav.primes', [], 'common')]) ?></p>
            <p class="page-head-index"><?= p('head.index', ['current' => $navPos['no'], 'total' => $navPos['total']]) ?></p>
          </div>
          <h1><?= p('head.title') ?></h1>
          <p class="sec-lede"><?= t('head.lede', ['edition' => mim_edition_label($edition)]) ?></p>
        </div>
      </header>

      <section class="section section-ink" aria-labelledby="prizes-title">
        <div class="wrap">
          <div class="page-toolbar">
            <h2 id="prizes-title" class="sr-only"><?= p('prize.title') ?></h2>
            <p><?= p('prize.lede') ?></p>
            <div class="prize-switch" role="tablist" aria-label="<?= p('prize.switch_aria') ?>">
              <button class="switch-btn is-active" role="tab" aria-selected="true" data-table="marathon" type="button"><?= p('race.marathon', [], 'common') ?></button>
              <button class="switch-btn" role="tab" aria-selected="false" tabindex="-1" data-table="semi" type="button"><?= p('race.semi', [], 'common') ?></button>
            </div>
          </div>

          <div class="prize-panels">
            <div data-panel="marathon">
              <div class="table-wrap">
                <table>
                  <caption><?= p('table.marathon.caption') ?></caption>
                  <thead>
                    <tr><th scope="col"><?= p('table.col_rank') ?></th><th scope="col"><?= p('table.col_men') ?></th><th scope="col"><?= p('table.col_women') ?></th></tr>
                  </thead>
                  <tbody>
                    <tr><th scope="row"><?= $rank(1) ?></th><td><?= p('money.15000') ?> <?= t('table.cond_men_first') ?> / <?= p('money.12000') ?></td><td><?= p('money.15000') ?> <?= t('table.cond_women_first') ?> / <?= p('money.12000') ?></td></tr>
                    <tr><th scope="row"><?= $rank(2) ?></th><td><?= p('money.10000') ?> <?= t('table.cond_men_second') ?> / <?= p('money.8000') ?></td><td><?= p('money.10000') ?> <?= t('table.cond_women_second') ?> / <?= p('money.8000') ?></td></tr>
                    <tr><th scope="row"><?= $rank(3) ?></th><td><?= p('money.5000') ?></td><td><?= p('money.5000') ?></td></tr>
                    <tr><th scope="row"><?= $rank(4) ?></th><td><?= p('money.3000') ?></td><td><?= p('money.3000') ?></td></tr>
                    <tr><th scope="row"><?= $rank(5) ?></th><td><?= p('money.1000') ?></td><td><?= p('money.1000') ?></td></tr>
                    <tr><th scope="row"><?= $rank(6) ?></th><td><?= p('money.700') ?></td><td><?= p('money.700') ?></td></tr>
                    <tr><th scope="row"><?= $rank(7) ?></th><td><?= p('money.600') ?></td><td><?= p('money.600') ?></td></tr>
                    <tr><th scope="row"><?= $rank(8) ?></th><td><?= p('money.500') ?></td><td><?= p('money.500') ?></td></tr>
                    <tr><th scope="row"><?= $rank(9) ?></th><td><?= p('money.400') ?></td><td><?= p('money.400') ?></td></tr>
                    <tr><th scope="row"><?= $rank(10) ?></th><td><?= p('money.350') ?></td><td><?= p('money.350') ?></td></tr>
                    <tr><th scope="row"><?= $rank(11) ?></th><td><?= p('money.300') ?></td><td><?= p('money.300') ?></td></tr>
                    <tr><th scope="row"><?= $rankRange(12, 15) ?></th><td colspan="2"><?= p('money.200') ?></td></tr>
                  </tbody>
                </table>
              </div>
            </div>

            <div data-panel="semi" hidden>
              <div class="table-wrap">
                <table>
                  <caption><?= p('table.semi.caption') ?></caption>
                  <thead>
                    <tr><th scope="col"><?= p('table.col_rank') ?></th><th scope="col"><?= p('table.col_both') ?></th></tr>
                  </thead>
                  <tbody>
                    <tr><th scope="row"><?= $rank(1) ?></th><td><?= p('money.2000') ?></td></tr>
                    <tr><th scope="row"><?= $rank(2) ?></th><td><?= p('money.1500') ?></td></tr>
                    <tr><th scope="row"><?= $rank(3) ?></th><td><?= p('money.1000') ?></td></tr>
                    <tr><th scope="row"><?= $rank(4) ?></th><td><?= p('money.800') ?></td></tr>
                    <tr><th scope="row"><?= $rank(5) ?></th><td><?= p('money.700') ?></td></tr>
                    <tr><th scope="row"><?= $rank(6) ?></th><td><?= p('money.600') ?></td></tr>
                    <tr><th scope="row"><?= $rank(7) ?></th><td><?= p('money.500') ?></td></tr>
                    <tr><th scope="row"><?= $rank(8) ?></th><td><?= p('money.400') ?></td></tr>
                    <tr><th scope="row"><?= $rank(9) ?></th><td><?= p('money.300') ?></td></tr>
                    <tr><th scope="row"><?= $rank(10) ?></th><td><?= p('money.200') ?></td></tr>
                    <tr><th scope="row"><?= $rank(11) ?></th><td><?= p('money.150') ?></td></tr>
                    <tr><th scope="row"><?= $rankRange(12, 15) ?></th><td><?= p('money.100') ?></td></tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <h3 class="bonus-head"><?= p('bonus.head') ?></h3>
          <ul class="bonus-row">
            <li><span><?= p('bonus.men_2h07') ?></span><strong><?= p('money.1000') ?></strong></li>
            <li><span><?= p('bonus.men_2h06') ?></span><strong><?= p('money.1500') ?></strong></li>
            <li><span><?= p('bonus.men_2h05') ?></span><strong><?= p('money.2000') ?></strong></li>
            <li><span><?= p('bonus.women_2h28') ?></span><strong><?= p('money.700') ?></strong></li>
            <li><span><?= p('bonus.women_2h27') ?></span><strong><?= p('money.1000') ?></strong></li>
            <li><span><?= p('bonus.women_2h26') ?></span><strong><?= p('money.1500') ?></strong></li>
          </ul>

          <p class="src"><?= t('src') ?></p>
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
