<?php
declare(strict_types=1);

$schedule = mim_schedule();
$edition = (int) $schedule['edition'];
$mimClient = [
    'marathon' => mim_target_iso(),
    'decimal' => mim_date_locale()['decimal'],
    'races' => [
        'marathon' => mim_race_start('marathon'),
        'semi' => mim_race_start('semi'),
        'tous' => mim_race_start('tous'),
    ],
    'ui' => [
        'live' => t('cd.live_after', [], 'common'),
        'races' => [
            'marathon' => [
                'kicker' => t('js.races.marathon.kicker', [], 'common'),
                'name' => t('js.races.marathon.name', [], 'common'),
                'blurb' => t('js.races.marathon.blurb', [], 'common'),
                'distance' => t('js.races.marathon.distance', [], 'common'),
                'limit' => t('js.races.marathon.limit', [], 'common'),
                'splits' => t('js.races.marathon.splits', [], 'common'),
                'rule' => t('js.races.marathon.rule', [], 'common'),
                'notes' => (array) mim_text('js.races.marathon.notes', 'common'),
            ],
            'semi' => [
                'kicker' => t('js.races.semi.kicker', [], 'common'),
                'name' => t('js.races.semi.name', [], 'common'),
                'blurb' => t('js.races.semi.blurb', [], 'common'),
                'distance' => t('js.races.semi.distance', [], 'common'),
                'limit' => t('js.races.semi.limit', [], 'common'),
                'splits' => t('js.races.semi.splits', [], 'common'),
                'rule' => t('js.races.semi.rule', [], 'common'),
                'notes' => (array) mim_text('js.races.semi.notes', 'common'),
            ],
            'tous' => [
                'kicker' => t('js.races.tous.kicker', [], 'common'),
                'name' => t('js.races.tous.name', [], 'common'),
                'blurb' => t('js.races.tous.blurb', [], 'common'),
                'distance' => t('js.races.tous.distance', [], 'common'),
                'limit' => t('js.races.tous.limit', [], 'common'),
                'splits' => t('js.races.tous.splits', [], 'common'),
                'rule' => '',
                'notes' => (array) mim_text('js.races.tous.notes', 'common'),
            ],
        ],
    ],
];
$mimJson = json_encode(
    $mimClient,
    JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
);
?>
    </main>

    <footer class="foot">
      <div class="wrap foot-inner">
        <p class="foot-word"><?= t('foot.word', [
            'edition' => mim_edition_label($edition),
            'date' => mim_l10n_date_dot($schedule['marathon_date']),
        ], 'common') ?></p>
        <p class="foot-src"><?= t('foot.src', [], 'common') ?></p>
        <div class="foot-links">
          <a class="foot-top" href="adminbtx.php"><?= p('foot.admin', [], 'common') ?></a>
          <a class="foot-top" href="#top"><?= p('foot.top', [], 'common') ?></a>
        </div>

        <?php
        $mimFootTok = mim_legal_tokens();
        $mimFootLegal = [
            'conditions' => p('legal.conditions_short', [], 'common'),
            'confidentialite' => p('legal.privacy_short', [], 'common'),
            'remboursement' => p('legal.refund_short', [], 'common'),
        ];
        ?>
        <div class="foot-legal">
          <section>
            <h2><?= p('legal.entity_title', [], 'common') ?></h2>
            <p><code><?= e(mim_iso(mim_entity_block())) ?></code></p>
            <h2><?= p('legal.association_title', [], 'common') ?></h2>
            <p><code><?= e($mimFootTok['association']) ?></code><br><?= e($mimFootTok['association_role']) ?></p>
          </section>

          <section>
            <h2><?= p('legal.status_title', [], 'common') ?></h2>
            <?php if (mim_entity_ready()): ?>
              <p><?= mim_fill(t('legal.status_text', [], 'common'), $mimFootTok) ?></p>
            <?php else: ?>
              <p class="legal-pending"><?= t('legal.status_text_pending', [], 'common') ?></p>
            <?php endif; ?>
          </section>

          <section>
            <h2><?= p('legal.nav_title', [], 'common') ?></h2>
            <ul>
              <?php foreach (mim_legal_pages() as $mimKey => $mimScript): ?>
                <li><a href="<?= e($mimScript) ?>"><?= $mimFootLegal[$mimKey] ?></a></li>
              <?php endforeach; ?>
            </ul>
          </section>

          <section>
            <h2><?= p('legal.contact_title', [], 'common') ?></h2>
            <ul>
              <li><?= mim_email_link('contact') ?></li>
              <li><?= mim_email_link('support', t('legal.email_support', [], 'common')) ?></li>
              <li><?= mim_email_link('legal', t('legal.email_legal', [], 'common')) ?></li>
            </ul>
          </section>
        </div>
      </div>
    </footer>

    <script>
      window.MIM = <?= $mimJson ?>;
    </script>
    <script src="<?= e(mim_asset('app.js')) ?>" defer></script>
  </body>
</html>
