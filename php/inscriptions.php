<?php
declare(strict_types=1);

require __DIR__ . '/inc/bootstrap.php';

$schedule = mim_schedule();
$registrationUrl = mim_site()['registration_url'];
$regJson = json_encode($registrationUrl, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

$page = 'inscriptions';
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
            <p class="tag"><?= p('head.tag', ['no' => $navPos['no'], 'label' => t('nav.inscriptions', [], 'common')]) ?></p>
            <p class="page-head-index"><?= p('head.index', ['cur' => $navPos['no'], 'total' => $navPos['total']]) ?></p>
          </div>
          <h1><?= p('head.title') ?></h1>
          <p class="sec-lede"><?= p('head.lede') ?></p>
        </div>
      </header>

      <section class="section" aria-labelledby="reg-title">
        <div class="wrap">
          <h2 class="block-title" id="reg-title"><?= p('reg.title') ?></h2>

          <?php
            $tok = mim_legal_tokens();
            $mimPriceTable = mim_price_table();
            $mimBilling = (string) array_key_first($mimPriceTable);
            $mimCurrencies = array_keys($mimPriceTable);
            $mimRaceLabels = [
                'marathon' => p('legal.price_marathon', [], 'common'),
                'semi' => p('legal.price_semi', [], 'common'),
                'tous' => p('legal.price_tous', [], 'common'),
            ];
            $mimPrices = [];
            foreach ($mimPriceTable as $mimCur => $mimRow) {
                foreach ($mimRow as $mimRace => $mimValue) {
                    $mimPrices[$mimRace][$mimCur] = mim_iso(mim_price_text($mimValue, $mimCur));
                }
            }
            $mimMultiCur = count($mimCurrencies) > 1;
          ?>
          <div class="price-card"<?= $mimMultiCur ? ' data-billing="' . e($mimBilling) . '"' : '' ?>>
            <h3 class="block-title"><?= p('legal.price_title', [], 'common') ?></h3>
            <p class="price-lede"><?= t('legal.price_lede', [], 'common') ?></p>

            <?php if ($mimMultiCur): ?>
              <div class="price-cur" role="group" aria-label="<?= p('legal.price_shown', [], 'common') ?>">
                <?php foreach ($mimCurrencies as $mimIndex => $mimCur): ?>
                  <button type="button" data-cur="<?= e($mimCur) ?>" aria-pressed="<?= $mimIndex === 0 ? 'true' : 'false' ?>"><?= e($mimCur) ?></button>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>

            <div class="price-rows"<?= $mimMultiCur ? ' aria-live="polite"' : '' ?>>
              <?php foreach ($mimRaceLabels as $mimRace => $mimRaceLabel): ?>
                <div class="price-row"<?= $mimMultiCur ? ' data-prices="' . e((string) json_encode($mimPrices[$mimRace], JSON_UNESCAPED_UNICODE)) . '"' : '' ?>>
                  <span><?= $mimRaceLabel ?></span>
                  <b class="price-val"><?= e($mimPrices[$mimRace][$mimBilling]) ?></b>
                </div>
              <?php endforeach; ?>
            </div>

            <h3 class="block-title"><?= p('legal.fx_title', [], 'common') ?></h3>
            <dl class="price-meta">
              <div><dt><?= p('legal.price_billing', [], 'common') ?></dt><dd><?= e($tok['billing']) ?></dd></div>
              <?php if ($mimMultiCur): ?>
                <div><dt><?= p('legal.price_shown', [], 'common') ?></dt><dd data-price-shown><?= e($mimBilling) ?></dd></div>
              <?php endif; ?>
              <div><dt><?= p('legal.fx_source', [], 'common') ?></dt><dd><?= e($tok['fx_source']) ?></dd></div>
              <div><dt><?= p('legal.fx_moment', [], 'common') ?></dt><dd><?= e($tok['fx_moment']) ?></dd></div>
            </dl>
            <?php if ($mimMultiCur): ?>
              <p class="price-note price-indicative" data-price-indicative hidden><?= mim_fill(t('legal.price_indicative', [], 'common'), $tok) ?></p>
            <?php endif; ?>
            <p class="price-note"><?= mim_fill(t('legal.fx_note', [], 'common'), $tok) ?></p>
            <p class="price-legal">
              <span><?= p('legal.read_policies', [], 'common') ?></span>
              <?php foreach (mim_legal_pages() as $mimKey => $mimScript): ?>
                <a href="<?= e($mimScript) ?>"><?= p('legal.' . mim_legal_label($mimKey), [], 'common') ?></a>
              <?php endforeach; ?>
            </p>
          </div>

            <?php if ($mimMultiCur): ?>
            <script>
              (function () {
                var card = document.querySelector('.price-card[data-billing]');
                if (!card) {
                  return;
                }
                var billing = card.getAttribute('data-billing');
                var buttons = card.querySelectorAll('.price-cur button');
                var rows = card.querySelectorAll('.price-row[data-prices]');
                var shown = card.querySelector('[data-price-shown]');
                var indicative = card.querySelector('[data-price-indicative]');

                function select(currency) {
                  rows.forEach(function (row) {
                    var prices = JSON.parse(row.getAttribute('data-prices'));
                    if (prices[currency]) {
                      row.querySelector('.price-val').textContent = prices[currency];
                    }
                  });
                  buttons.forEach(function (button) {
                    button.setAttribute('aria-pressed', button.getAttribute('data-cur') === currency ? 'true' : 'false');
                  });
                  if (shown) {
                    shown.textContent = currency;
                  }
                  if (indicative) {
                    indicative.hidden = currency === billing;
                  }
                }

                buttons.forEach(function (button) {
                  button.addEventListener('click', function () {
                    select(button.getAttribute('data-cur'));
                  });
                });
              })();
            </script>
            <?php endif; ?>

          <?php if ($registrationUrl !== ''): ?>
            <script>window.MIM_REGISTRATION_APP = <?= $regJson ?>;</script>
            <iframe
              id="reg-app-frame"
              class="reg-frame"
              src="reg-app.php"
              title="<?= p('reg.frame_title') ?>"
              scrolling="no"
            ></iframe>
            <p class="reg-alt">
              <?= t('reg.fallback') ?> <a class="link-arrow" href="<?= e($registrationUrl) ?>" target="_blank" rel="noopener noreferrer"><?= p('reg.open_new_tab') ?><span aria-hidden="true">↗</span></a>
            </p>
            <script>
              (function () {
                var frame = document.getElementById('reg-app-frame');
                if (!frame) {
                  return;
                }
                function resize() {
                  try {
                    var doc = frame.contentDocument;
                    if (!doc || !doc.documentElement) {
                      return;
                    }
                    var height = doc.documentElement.scrollHeight;
                    if (doc.body && doc.body.scrollHeight > height) {
                      height = doc.body.scrollHeight;
                    }
                    if (height > 0) {
                      frame.style.height = height + 'px';
                    }
                  } catch (error) {}
                }
                frame.addEventListener('load', function () {
                  resize();
                  var doc = frame.contentDocument;
                  if (!doc || !doc.documentElement) {
                    return;
                  }
                  if (typeof MutationObserver !== 'undefined') {
                    new MutationObserver(resize).observe(doc.documentElement, {
                      childList: true,
                      subtree: true,
                      attributes: true,
                    });
                  }
                  setInterval(resize, 600);
                });
              })();
            </script>
          <?php else: ?>
            <p><?= p('reg.empty_text') ?></p>
            <p>
              <a
                class="btn btn-signal"
                href="https://www.marathonmarrakech.ma/registration"
                target="_blank"
                rel="noopener noreferrer"
                ><?= p('reg.empty_button') ?></a
              >
            </p>
          <?php endif; ?>
        </div>
      </section>

      <a class="page-next" href="<?= e($navPos['next']) ?>.php" aria-label="<?= p('next.back_aria') ?>">
        <div class="wrap page-next-inner">
          <div>
            <p class="page-next-label"><?= p('next.back_label') ?></p>
            <p class="page-next-title"><?= p('nav.' . $navPos['next'], [], 'common') ?></p>
          </div>
          <span class="page-next-go" aria-hidden="true">→</span>
        </div>
      </a>
<?php require __DIR__ . '/inc/footer.php'; ?>
