<?php
declare(strict_types=1);

require __DIR__ . '/inc/bootstrap.php';

$schedule = mim_schedule();
$edition = (int) $schedule['edition'];
$marathonStart = mim_time($schedule['marathon_start']);
$semiStart = mim_time($schedule['semi_start']);
$marathonHour = mim_l10n_hour($schedule['marathon_start']);
$semiHour = mim_l10n_hour($schedule['semi_start']);
$dateDot = mim_l10n_date_dot($schedule['marathon_date']);
$dateLong = mim_l10n_date_long($schedule['marathon_date']);

$page = 'index';
$GLOBALS['page'] = $page;
$title = t('title', ['date' => $dateDot, 'start' => $marathonStart]);
$description = t('description', [
    'edition' => (string) $edition,
    'date_long' => $dateLong,
    'marathon_time' => $marathonHour,
    'semi_time' => $semiHour,
]);
$ogTitle = t('og_title', ['date' => $dateDot]);
$ogDescription = t('og_description', ['edition' => (string) $edition, 'date_long' => $dateLong]);
$extraHead = '<link rel="preconnect" href="https://www.youtube-nocookie.com" />';

require __DIR__ . '/inc/header.php';
?>
      <!-- ================= HERO ================= -->
      <section class="hero" id="top" aria-labelledby="hero-title">
        <div class="hero-media" aria-hidden="true">
          <img
            class="hero-poster"
            src="assets/menara-hero.jpg"
            alt=""
            width="1600"
            height="914"
            fetchpriority="high"
          />
          <div class="hero-video-frame">
            <iframe
              id="hero-video"
              src="https://www.youtube-nocookie.com/embed/ldq_j2rlkAs?autoplay=1&mute=1&loop=1&playlist=ldq_j2rlkAs&controls=0&playsinline=1&rel=0&modestbranding=1&iv_load_policy=3&disablekb=1&enablejsapi=1"
              title="<?= p('hero.video_title') ?>"
              tabindex="-1"
              allow="autoplay; encrypted-media; picture-in-picture"
              allowfullscreen
              referrerpolicy="strict-origin-when-cross-origin"
            ></iframe>
          </div>
        </div>

        <div class="hero-body">
          <p class="tag"><span><?= t('ui.edition_of', ['n' => (string) $edition, 'sup' => mim_l10n_edition_sup($edition)], 'common') ?></span></p>
          <h1 id="hero-title">
            <span class="hero-date"><?= e($dateDot) ?></span>
            <span class="hero-hour"><?= p('hero.start', ['time' => $marathonStart]) ?> <span class="hero-semi">· <?= p('hero.semi', ['time' => $semiStart]) ?></span></span>
          </h1>
          <div class="hero-actions">
            <a class="btn btn-signal" href="inscriptions.php"><?= p('hero.cta_register') ?></a>
            <a class="btn btn-ghost" href="parcours.php"><?= p('hero.cta_parcours') ?></a>
          </div>
        </div>

        <!-- finish-line clock -->
        <div class="clock">
          <div class="clock-head">
            <p class="clock-label"><?= p('cd.label', [], 'common') ?></p>
            <p class="clock-sub"><?= t('clock.sub', ['date' => mim_l10n_date($schedule['marathon_date'], '{wdshort} {day} {month} {year}'), 'time' => $marathonHour]) ?></p>
          </div>
          <div class="clock-digits" role="timer" aria-label="<?= p('cd.aria', [], 'common') ?>">
            <div class="digit">
              <span class="digit-value" data-cd="days">—</span>
              <span class="digit-label"><?= p('cd.days', [], 'common') ?></span>
            </div>
            <div class="digit">
              <span class="digit-value" data-cd="hours">—</span>
              <span class="digit-label"><?= p('cd.hours', [], 'common') ?></span>
            </div>
            <div class="digit">
              <span class="digit-value" data-cd="minutes">—</span>
              <span class="digit-label"><?= p('cd.minutes', [], 'common') ?></span>
            </div>
            <div class="digit">
              <span class="digit-value" data-cd="seconds">—</span>
              <span class="digit-label"><?= p('cd.seconds', [], 'common') ?></span>
            </div>
          </div>
        </div>
        <p class="sr-only" data-cd-live aria-live="polite"></p>
      </section>

      <!-- ================= COURSE STRIP ================= -->
      <div class="strip" aria-label="<?= p('strip.label') ?>">
        <div class="strip-inner">
          <p><span class="strip-key"><?= p('race.marathon', [], 'common') ?></span><?= p('strip.marathon_value') ?></p>
          <p><span class="strip-key"><?= p('strip.semi') ?></span><?= p('strip.semi_value') ?></p>
          <p><span class="strip-key"><?= p('strip.tous') ?></span><?= p('strip.tous_value') ?></p>
          <p><span class="strip-key"><?= p('ui.start', [], 'common') ?></span><?= e($marathonStart) ?></p>
          <p><span class="strip-key"><?= p('ui.limit', [], 'common') ?></span><?= p('strip.limit_value') ?></p>
          <p><span class="strip-key"><?= p('ui.edition_word', [], 'common') ?></span><?= e((string) $edition) ?><sup><?= e(mim_l10n_edition_sup($edition)) ?></sup></p>
        </div>
      </div>

      <!-- ================= SOMMAIRE ================= -->
      <section class="section" aria-labelledby="sommaire-title">
        <div class="wrap">
          <header class="sec-head">
            <p class="tag"><?= p('sommaire.tag') ?></p>
            <h2 id="sommaire-title"><?= p('sommaire.title') ?></h2>
            <p class="sec-lede"><?= p('sommaire.lede') ?></p>
          </header>

          <ol class="index-list">
            <li>
              <a class="index-row" href="epreuves.php">
                <span class="index-no">01</span>
                <span class="index-name"><?= p('nav.epreuves', [], 'common') ?></span>
                <span class="index-desc"><?= p('sommaire.row.epreuves.desc', ['marathon_time' => $marathonHour, 'semi_time' => $semiHour]) ?></span>
                <span class="index-go" aria-hidden="true">↗</span>
              </a>
            </li>
            <li>
              <a class="index-row" href="parcours.php">
                <span class="index-no">02</span>
                <span class="index-name"><?= p('nav.parcours', [], 'common') ?></span>
                <span class="index-desc"><?= p('sommaire.row.parcours.desc') ?></span>
                <span class="index-go" aria-hidden="true">↗</span>
              </a>
            </li>
            <li>
              <a class="index-row" href="programme.php">
                <span class="index-no">03</span>
                <span class="index-name"><?= p('nav.programme', [], 'common') ?></span>
                <span class="index-desc"><?= p('sommaire.row.programme.desc') ?></span>
                <span class="index-go" aria-hidden="true">↗</span>
              </a>
            </li>
            <li>
              <a class="index-row" href="primes.php">
                <span class="index-no">04</span>
                <span class="index-name"><?= p('nav.primes', [], 'common') ?></span>
                <span class="index-desc"><?= p('sommaire.row.primes.desc') ?></span>
                <span class="index-go" aria-hidden="true">↗</span>
              </a>
            </li>
            <li>
              <a class="index-row" href="village.php">
                <span class="index-no">05</span>
                <span class="index-name"><?= p('nav.village', [], 'common') ?></span>
                <span class="index-desc"><?= p('sommaire.row.village.desc') ?></span>
                <span class="index-go" aria-hidden="true">↗</span>
              </a>
            </li>
            <li>
              <a class="index-row" href="infos.php">
                <span class="index-no">06</span>
                <span class="index-name"><?= p('nav.infos', [], 'common') ?></span>
                <span class="index-desc"><?= p('sommaire.row.infos.desc') ?></span>
                <span class="index-go" aria-hidden="true">↗</span>
              </a>
            </li>
            <li>
              <a class="index-row" href="inscriptions.php">
                <span class="index-no">07</span>
                <span class="index-name"><?= p('nav.inscriptions', [], 'common') ?></span>
                <span class="index-desc"><?= p('sommaire.row.inscriptions.desc') ?></span>
                <span class="index-go" aria-hidden="true">↗</span>
              </a>
            </li>
          </ol>
        </div>
      </section>

      <!-- ================= CONTACT ================= -->
      <section class="home-close" aria-labelledby="close-title">
        <div class="wrap home-close-inner">
          <div>
            <p class="tag tag-invert"><?= p('contact.tag') ?></p>
            <h2 id="close-title"><?= t('contact.title') ?></h2>
          </div>
          <div class="home-close-actions">
            <?php /* Sans e-mail saisie, le bouton mène à la section contact plutôt qu'à un mailto: vide. */ ?>
            <a class="btn btn-signal" href="<?= e(mim_email('contact') !== '' ? 'mailto:' . mim_email('contact') : 'infos.php#contact') ?>"><?= p('contact.mail') ?></a>
            <a class="link-arrow" href="infos.php#contact"><?= p('contact.more') ?><span aria-hidden="true">↗</span></a>
          </div>
        </div>
      </section>
<?php require __DIR__ . '/inc/footer.php'; ?>
