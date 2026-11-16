<?php
declare(strict_types=1);

/**
 * Gabarit partagé des trois pages légales. L'appelant a déjà posé $GLOBALS['page'] ;
 * tout le contenu vient de lang/<locale>/<page>.php et les mentions variables
 * (entité, e-mails, tarifs, délais) de data/legal.json.
 */

$page = (string) $GLOBALS['page'];
$mimLegalOrder = ['conditions' => '01', 'confidentialite' => '02', 'remboursement' => '03'];
$mimLegalTotal = '03';
$mimLegalNo = $mimLegalOrder[$page] ?? '01';
$mimLegalNext = array_values(array_filter(
    array_keys(mim_legal_pages()),
    static fn (string $key): bool => ($mimLegalOrder[$key] ?? '') > $mimLegalNo
));
$tok = mim_legal_tokens();
$sections = (array) mim_text('sections');
$mimUpdatedKey = [
    'conditions' => 'terms_updated',
    'confidentialite' => 'privacy_updated',
    'remboursement' => 'refund_updated',
][$page] ?? 'terms_updated';

$title = mim_fill(t('title'), $tok);
$description = mim_fill(t('description'), $tok);
$ogDescription = mim_fill(t('og_description'), $tok);

require __DIR__ . '/header.php';
?>
      <header class="page-head" id="top">
        <div class="wrap">
          <div class="page-head-top">
            <p class="tag"><?= p('legal.nav_title', [], 'common') ?></p>
            <p class="page-head-index"><?= p('head.index', ['cur' => $mimLegalNo, 'total' => $mimLegalTotal]) ?></p>
          </div>
          <h1><?= p('head.title') ?></h1>
          <p class="sec-lede"><?= t('head.lede') ?></p>
          <p class="legal-updated"><?= t('legal.updated', ['date' => $tok[$mimUpdatedKey]], 'common') ?></p>
        </div>
      </header>

      <nav class="legal-nav" aria-label="<?= p('legal.nav_title', [], 'common') ?>">
        <div class="wrap legal-nav-inner">
          <?php foreach (mim_legal_pages() as $key => $script): ?>
            <a href="<?= e($script) ?>"<?= $key === $page ? ' aria-current="page"' : '' ?>>
              <span class="legal-nav-no"><?= e($mimLegalOrder[$key] ?? '') ?></span><?= p('legal.' . mim_legal_label($key), [], 'common') ?>
            </a>
          <?php endforeach; ?>
        </div>
      </nav>

      <section class="section section-bone-2" aria-labelledby="legal-summary">
        <div class="wrap legal">
          <div class="legal-body">
            <?php foreach ($sections as $i => $section): ?>
              <article class="legal-art" id="art-<?= (int) $i + 1 ?>">
                <h2 class="block-title" id="art-<?= (int) $i + 1 ?>-t"><?= mim_fill((string) $section['t'], $tok) ?></h2>
                <?php foreach ((array) ($section['body'] ?? []) as $line): ?>
                  <p><?= mim_fill((string) $line, $tok) ?></p>
                <?php endforeach; ?>
                <?php foreach ((array) ($section['list'] ?? []) as $line): ?>
                  <p class="legal-li"><?= mim_fill((string) $line, $tok) ?></p>
                <?php endforeach; ?>
              </article>
            <?php endforeach; ?>
          </div>

          <aside class="legal-side">
            <h2 class="block-title" id="legal-summary"><?= p('toc.title') ?></h2>
            <ol class="legal-toc">
              <?php foreach ($sections as $i => $section): ?>
                <li><a href="#art-<?= (int) $i + 1 ?>"><?= mim_fill((string) $section['t'], $tok) ?></a></li>
              <?php endforeach; ?>
            </ol>

            <h2 class="block-title"><?= p('legal.contact_title', [], 'common') ?></h2>
            <p class="legal-side-line"><span><?= p('legal.email_support', [], 'common') ?></span>
              <?= mim_email_link('support') ?></p>
            <p class="legal-side-line"><span><?= p('legal.email_legal', [], 'common') ?></span>
              <?= mim_email_link('legal') ?></p>

            <h2 class="block-title"><?= p('legal.status_title', [], 'common') ?></h2>
            <?php if (mim_entity_ready()): ?>
              <p><?= mim_fill(t('legal.status_text', [], 'common'), $tok) ?></p>
            <?php else: ?>
              <p class="legal-pending"><?= t('legal.status_text_pending', [], 'common') ?></p>
            <?php endif; ?>
          </aside>
        </div>
      </section>

      <?php if ($mimLegalNext !== []): ?>
        <?php $next = $mimLegalNext[0]; ?>
        <a class="page-next" href="<?= e($next) ?>.php" aria-label="<?= p('next.aria', ['label' => t('legal.' . mim_legal_label($next), [], 'common')]) ?>">
          <div class="wrap page-next-inner">
            <div>
              <p class="page-next-label"><?= p('next.label', ['no' => $mimLegalOrder[$next]]) ?></p>
              <p class="page-next-title"><?= p('legal.' . mim_legal_label($next), [], 'common') ?></p>
            </div>
            <span class="page-next-go" aria-hidden="true">→</span>
          </div>
        </a>
      <?php else: ?>
        <a class="page-next" href="inscriptions.php" aria-label="<?= p('next.aria', ['label' => t('nav.inscriptions', [], 'common')]) ?>">
          <div class="wrap page-next-inner">
            <div>
              <p class="page-next-label"><?= p('next.label', ['no' => '07']) ?></p>
              <p class="page-next-title"><?= p('nav.inscriptions', [], 'common') ?></p>
            </div>
            <span class="page-next-go" aria-hidden="true">→</span>
          </div>
        </a>
      <?php endif; ?>
<?php require __DIR__ . '/footer.php'; ?>
