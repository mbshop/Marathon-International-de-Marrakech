<?php
declare(strict_types=1);

require __DIR__ . '/inc/bootstrap.php';

$schedule = mim_schedule();

$page = 'infos';
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
            <p class="tag"><?= p('head.tag', ['no' => $navPos['no'], 'label' => t('nav.infos', [], 'common')]) ?></p>
            <p class="page-head-index"><?= p('head.index', ['cur' => $navPos['no'], 'total' => $navPos['total']]) ?></p>
          </div>
          <h1><?= p('head.title') ?></h1>
          <p class="sec-lede"><?= t('head.lede') ?></p>
        </div>
      </header>

      <section class="section section-bone-2" aria-labelledby="faq-title">
        <div class="wrap infos">
          <div class="infos-intro">
            <h2 id="faq-title" class="block-title"><?= p('faq.intro_title') ?></h2>
            <p class="sec-lede"><?= p('faq.intro_lede') ?></p>
            <a class="btn btn-ink" href="inscriptions.php"><?= p('faq.intro_button') ?></a>
          </div>

          <div class="faq">
            <details open>
              <summary><?= t('faq.q1') ?></summary>
              <p><?= p('faq.a1') ?></p>
            </details>
            <details>
              <summary><?= t('faq.q2') ?></summary>
              <p><?= p('faq.a2') ?></p>
              <a class="link-arrow" href="https://www.marathonmarrakech.ma/regulation" target="_blank" rel="noopener noreferrer"><?= p('faq.link_regulation') ?><span aria-hidden="true">↗</span></a>
            </details>
            <details>
              <summary><?= t('faq.q3') ?></summary>
              <p><?= t('faq.a3') ?></p>
            </details>
            <details>
              <summary><?= t('faq.q4') ?></summary>
              <p><?= p('faq.a4', ['date' => mim_l10n_date_day_month($schedule['tous_date']), 'time' => mim_l10n_hour($schedule['tous_start'])]) ?></p>
            </details>
            <details>
              <summary><?= t('faq.q5') ?></summary>
              <p><?= p('faq.a5') ?></p>
              <a class="link-arrow" href="https://www.marathonmarrakech.ma/gallery" target="_blank" rel="noopener noreferrer"><?= p('faq.link_gallery') ?><span aria-hidden="true">↗</span></a>
            </details>
          </div>
        </div>
      </section>

      <section class="section" id="contact" aria-labelledby="contact-title">
        <div class="wrap">
          <h2 class="block-title" id="contact-title"><?= p('contact.title') ?></h2>
          <dl class="contact">
            <div><dt><?= p('contact.address_label') ?></dt><dd><?= t('contact.address') ?></dd></div>
            <div><dt><?= p('contact.phone_label') ?></dt><dd><a href="tel:+212524313572"><?= p('contact.phone') ?></a></dd></div>
            <div><dt><?= p('contact.whatsapp_label') ?></dt><dd><a href="https://wa.me/212628252624" target="_blank" rel="noopener noreferrer"><?= p('contact.whatsapp') ?></a></dd></div>
            <div><dt><?= p('contact.email_label') ?></dt><dd><?= mim_email_link('contact') ?></dd></div>
            <div><dt><?= p('contact.register_label') ?></dt><dd><?= mim_email_link('support') ?></dd></div>
            <div><dt><?= p('contact.hours_label') ?></dt><dd><?= t('contact.hours') ?></dd></div>
          </dl>
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
