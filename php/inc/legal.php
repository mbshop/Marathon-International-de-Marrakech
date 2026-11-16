<?php
declare(strict_types=1);

/**
 * Rédaction des mentions variables (entité, e-mails, tarifs, délais) pour les pages légales.
 * Une valeur non saisie n'est jamais inventée : elle devient « à préciser », visible
 * par l'organisateur et par la passerelle de paiement lors du contrôle du site.
 */

/** Libellé de repli quand une mention légale reste à saisir. */
function mim_pending(): string
{
    return t('legal.pending', [], 'common');
}

/** Remplace les jetons {…} d'une phrase du catalogue par les valeurs de configuration. */
function mim_fill(string $text, array $args): string
{
    $replacements = [];
    foreach ($args as $name => $value) {
        $replacements['{' . $name . '}'] = (string) $value;
    }

    $html = strtr($text, $replacements);

    /* Une e-mail non saisie ne laisse jamais un mailto: invalide dans le texte. */
    return preg_replace_callback(
        '#<a href="mailto:([^"]*)">.*?</a>#s',
        static fn (array $m): string => filter_var($m[1], FILTER_VALIDATE_EMAIL)
            ? $m[0]
            : '<span class="pending-inline">' . mim_pending() . '</span>',
        $html
    ) ?? $html;
}

/**
 * Lien mailto: d'une e-mail officielle, ou le libellé « à préciser » si rien n'est saisi.
 * $label permet un intitulé traduit à la place de l'adresse.
 */
function mim_email_link(string $kind = 'contact', string $label = ''): string
{
    $email = mim_email($kind);

    if ($email === '') {
        return '<span class="pending-inline">' . mim_pending() . '</span>';
    }

    return '<a href="mailto:' . e($email) . '">' . e($label !== '' ? $label : $email) . '</a>';
}

/** Adresse saisie pour un jeton de phrase, sinon le libellé « à préciser ». */
function mim_email_or_pending(string $kind): string
{
    $email = mim_email($kind);

    return $email === '' ? mim_pending() : $email;
}

/**
 * Isole une valeur latine (prix, EIN, adresse) dans un paragraphe arabe :
 * sans FSI/PDI, « 95 USD » se peint « USD 95 » et le tarif devient illisible.
 * Les e-mails ne passent pas ici, ils servent aussi d'URL mailto:.
 */
function mim_iso(string $value): string
{
    if (mim_lang() !== 'ar' || $value === '') {
        return $value;
    }

    return "\u{2068}" . $value . "\u{2069}";
}

/** Adresse postale américaine de la structure qui facture, sur une seule ligne. */
function mim_entity_address(): string
{
    $entity = mim_legal()['entity'];
    $lines = array_filter([
        $entity['street'],
        trim($entity['city'] . ' ' . $entity['state'] . ' ' . $entity['zip']),
        $entity['country'],
    ], static fn (string $line): bool => $line !== '');

    return $lines === [] ? mim_pending() : implode(', ', $lines);
}

/** « Nom, EIN, adresse » ou le libellé « à préciser » si l'entité est incomplète. */
function mim_entity_block(): string
{
    $entity = mim_legal()['entity'];

    if ($entity['name'] === '' || $entity['ein'] === '' || $entity['street'] === '') {
        return mim_pending();
    }

    return $entity['name'] . ' — EIN ' . $entity['ein'] . ' — ' . mim_entity_address();
}

/** Montant formaté dans sa devise, avec le séparateur décimal de la langue du visiteur. */
function mim_price_text(string $value, string $code): string
{
    if ($value === '' || !is_numeric($value)) {
        return mim_pending();
    }

    $num = (float) $value;
    $decimals = abs($num - round($num)) < 0.005 ? 0 : 2;

    return number_format($num, $decimals, mim_date_locale()['decimal'], ' ') . ' ' . $code;
}

/** Prix de référence dans la devise facturée, ou repli visible. */
function mim_price_label(string $race): string
{
    return mim_price_text(mim_price($race), mim_legal()['pricing']['billing']);
}

/**
 * Devises proposées à la lecture sur la page Inscriptions : la devise facturée
 * d'abord, puis EUR et MAD seulement si l'organisateur y a saisi un prix.
 * Aucune contre-valeur n'est calculée : ce que le visiteur voit a été saisi.
 *
 * @return array<string, array<string, string>>
 */
function mim_price_table(): array
{
    $pricing = mim_legal()['pricing'];
    $table = [$pricing['billing'] => [
        'marathon' => $pricing['marathon'],
        'semi' => $pricing['semi'],
        'tous' => $pricing['tous'],
    ]];

    foreach (['eur' => 'EUR', 'mad' => 'MAD'] as $prefix => $code) {
        if ($code === $pricing['billing']) {
            continue;
        }

        $row = [
            'marathon' => $pricing[$prefix . '_marathon'],
            'semi' => $pricing[$prefix . '_semi'],
            'tous' => $pricing[$prefix . '_tous'],
        ];
        if (array_filter($row, static fn (string $value): bool => $value !== '')) {
            $table[$code] = $row;
        }
    }

    return $table;
}

/** Date du catalogue de configuration, formatée dans la langue du visiteur. */
function mim_policy_date(string $key): string
{
    $value = (string) (mim_legal()['policies'][$key] ?? '');

    if ($value === '') {
        return mim_pending();
    }

    return mim_l10n_date($value, mim_date_locale()['long'], true);
}

/**
 * Jetons {…} injectés dans toutes les phrases légales traduites.
 *
 * @return array<string, string>
 */
function mim_legal_tokens(): array
{
    $legal = mim_legal();

    return [
        'entity' => mim_iso($legal['entity']['name'] !== '' ? $legal['entity']['name'] : mim_pending()),
        'ein' => mim_iso($legal['entity']['ein'] !== '' ? $legal['entity']['ein'] : mim_pending()),
        'address' => mim_iso(mim_entity_address()),
        'entity_block' => mim_iso(mim_entity_block()),
        'association' => mim_iso($legal['association']['name'] !== '' ? $legal['association']['name'] : mim_pending()),
        'association_role' => mim_iso($legal['association']['role'] !== '' ? $legal['association']['role'] : mim_pending()),
        'email_contact' => mim_email_or_pending('contact'),
        'email_support' => mim_email_or_pending('support'),
        'email_legal' => mim_email_or_pending('legal'),
        'billing' => mim_iso($legal['pricing']['billing']),
        'price_marathon' => mim_iso(mim_price_label('marathon')),
        'price_semi' => mim_iso(mim_price_label('semi')),
        'price_tous' => mim_iso(mim_price_label('tous')),
        'fx_source' => mim_iso($legal['pricing']['fx_source'] !== '' ? $legal['pricing']['fx_source'] : mim_pending()),
        'fx_moment' => mim_iso($legal['pricing']['fx_moment'] !== '' ? $legal['pricing']['fx_moment'] : mim_pending()),
        'refund_deadline' => mim_iso(mim_policy_date('refund_deadline')),
        'deferral_deadline' => mim_iso(mim_policy_date('deferral_deadline')),
        'deferral_fee' => mim_iso($legal['policies']['deferral_fee'] !== '' ? $legal['policies']['deferral_fee'] : mim_pending()),
        'terms_updated' => mim_iso(mim_policy_date('terms_updated')),
        'privacy_updated' => mim_iso(mim_policy_date('privacy_updated')),
        'refund_updated' => mim_iso(mim_policy_date('refund_updated')),
    ];
}

/** Les trois pages légales, dans l'ordre de lecture. @return array<string, string> */
function mim_legal_pages(): array
{
    return [
        'conditions' => 'conditions.php',
        'confidentialite' => 'confidentialite.php',
        'remboursement' => 'remboursement.php',
    ];
}

/** Nom de catalogue (legal.conditions / legal.privacy / legal.refund) d'une page légale. */
function mim_legal_label(string $page): string
{
    return [
        'conditions' => 'conditions',
        'confidentialite' => 'privacy',
        'remboursement' => 'refund',
    ][$page] ?? $page;
}
