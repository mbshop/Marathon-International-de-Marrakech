<?php
declare(strict_types=1);

/**
 * Locales served by the public site. "dir" drives the RTL layout used by Arabic.
 */
const MIM_LOCALES = [
    'fr' => ['dir' => 'ltr', 'native' => 'Français', 'short' => 'FR', 'og' => 'fr_FR'],
    'en' => ['dir' => 'ltr', 'native' => 'English', 'short' => 'EN', 'og' => 'en_US'],
    'ar' => ['dir' => 'rtl', 'native' => 'العربية', 'short' => 'AR', 'og' => 'ar_MA'],
    'es' => ['dir' => 'ltr', 'native' => 'Español', 'short' => 'ES', 'og' => 'es_ES'],
];

const MIM_LANG_COOKIE = 'mim_lang';
const MIM_LANG_DEFAULT = 'fr';

function mim_locale_exists(string $code): bool
{
    return isset(MIM_LOCALES[$code]);
}

/**
 * ?lang= wins over the visitor cookie, then French. A ?lang= stores the choice.
 */
function mim_lang(): string
{
    static $lang = null;

    if ($lang !== null) {
        return $lang;
    }

    if (isset($GLOBALS['mim_lang_pin']) && mim_locale_exists((string) $GLOBALS['mim_lang_pin'])) {
        return $lang = (string) $GLOBALS['mim_lang_pin'];
    }

    $requested = strtolower(trim((string) ($_GET['lang'] ?? '')));
    $cookie = strtolower(trim((string) ($_COOKIE[MIM_LANG_COOKIE] ?? '')));

    if (mim_locale_exists($requested)) {
        $lang = $requested;
        if (!headers_sent()) {
            setcookie(MIM_LANG_COOKIE, $lang, [
                'expires' => time() + 60 * 60 * 24 * 365,
                'path' => '/',
                'samesite' => 'Lax',
            ]);
            $_COOKIE[MIM_LANG_COOKIE] = $lang;
        }
    } elseif (mim_locale_exists($cookie)) {
        $lang = $cookie;
    } else {
        $lang = MIM_LANG_DEFAULT;
    }

    return $lang;
}

/** True until the visitor has picked a language once. */
function mim_needs_lang_choice(): bool
{
    return !mim_locale_exists(strtolower(trim((string) ($_COOKIE[MIM_LANG_COOKIE] ?? ''))))
        && !mim_locale_exists(strtolower(trim((string) ($_GET['lang'] ?? ''))));
}

function mim_lang_dir(): string
{
    return MIM_LOCALES[mim_lang()]['dir'];
}

/**
 * Current request URI with lang swapped, so switching keeps the reader on the same page.
 */
function mim_lang_url(string $locale): string
{
    $script = (string) ($_SERVER['SCRIPT_NAME'] ?? 'index.php');
    $query = $_GET ?? [];
    unset($query['lang'], $query['media_edition']);
    $query = array_filter($query, static fn ($value) => $value !== '');
    $query['lang'] = $locale;

    return $script . '?' . http_build_query($query);
}

/**
 * Catalog for one page: lang/<locale>/<page>.php merged over lang/<locale>/common.php.
 * Missing keys fall back to French, so an untranslated page never renders empty.
 *
 * @return array<string, string>
 */
function mim_catalog(string $page): array
{
    static $catalogs = [];
    $key = mim_lang() . '/' . $page;

    if (isset($catalogs[$key])) {
        return $catalogs[$key];
    }

    $load = static function (string $locale, string $file): array {
        $path = MIM_ROOT . '/lang/' . $locale . '/' . $file . '.php';

        return is_file($path) ? (array) require $path : [];
    };

    $locale = mim_lang();
    $catalogs[$key] = array_replace(
        $load('fr', 'common'),
        $load($locale, 'common'),
        $load('fr', $page),
        $load($locale, $page)
    );

    return $catalogs[$key];
}

/**
 * Translated string with %placeholders replaced by $args.
 */
function t(string $id, array $args = [], ?string $page = null): string
{
    $page ??= (string) ($GLOBALS['page'] ?? 'index');
    $text = mim_catalog($page)[$id] ?? $id;

    if ($args !== []) {
        $replacements = [];
        foreach ($args as $name => $value) {
            $replacements['{' . $name . '}'] = (string) $value;
        }
        $text = strtr($text, $replacements);
    }

    return mim_l10n_bidi($text);
}

/**
 * "15 000 $", "+212 524 31 35 72" or "01 / 03" inside an Arabic paragraph split into
 * separate bidi runs and paint reversed. Isolating each keeps the source order.
 */
function mim_l10n_bidi(string $text): string
{
    if (mim_lang() !== 'ar') {
        return $text;
    }

    return preg_replace_callback(
        '/[+]?[0-9][0-9.,:+$%\/\x{00A0}\x{2007}\x{2009}\x{202F}\x{2010}-\x{2015}\x{2212}\s]*[0-9$%]/u',
        static function (array $match): string {
            return preg_match('/\s[0-9$%]|\p{Pd}/u', $match[0])
                ? "\u{2068}" . $match[0] . "\u{2069}"
                : $match[0];
        },
        $text
    ) ?? $text;
}

/** Escaped shorthand for templates. */
function p(string $id, array $args = [], ?string $page = null): string
{
    return e(t($id, $args, $page));
}

/** Raw catalog value (arrays included) for building structured data. */
function mim_text(string $id, ?string $page = null)
{
    $page ??= (string) ($GLOBALS['page'] ?? 'index');
    $bidi = static function ($value) use (&$bidi) {
        if (is_array($value)) {
            return array_map($bidi, $value);
        }

        return is_string($value) ? mim_l10n_bidi($value) : $value;
    };

    return $bidi(mim_catalog($page)[$id] ?? null);
}

/**
 * "37ᵉ édition" in the locale word order, superscript included. Trusted catalog markup.
 */
function mim_edition_label(int $edition): string
{
    return t('ui.edition_label', [
        'n' => (string) $edition,
        'sup' => mim_l10n_edition_sup($edition),
    ], 'common');
}

/**
 * Localised day/month names and date order, from lang/dates.php.
 *
 * @return array{weekdays: array<int, string>, weekdaysShort: array<int, string>, months: array<int, string>, long: string, decimal: string, sup: array{first: string, other: string}}
 */
function mim_date_locale(?string $lang = null): array
{
    static $all = null;
    $all ??= (array) require MIM_ROOT . '/lang/dates.php';

    return $all[$lang ?? mim_lang()] ?? $all['fr'];
}

function mim_l10n_weekday(int $iso, bool $short = false): string
{
    $locale = mim_date_locale();
    $set = $short ? $locale['weekdaysShort'] : $locale['weekdays'];

    return $set[$iso] ?? '';
}

function mim_l10n_month(int $month): string
{
    return mim_date_locale()['months'][$month] ?? '';
}

/**
 * Date spelled out in the visitor language. Patterns use {weekday} {day} {month} {year}
 * so each locale can order them its own way.
 */
function mim_l10n_date(string $date, string $pattern, bool $capitalize = false): string
{
    $day = mim_day($date);
    $label = strtr($pattern, [
        '{weekday}' => mim_l10n_weekday((int) $day->format('N')),
        '{wdshort}' => mim_l10n_weekday((int) $day->format('N'), true),
        '{day}' => (string) (int) $day->format('j'),
        '{month}' => mim_l10n_month((int) $day->format('n')),
        '{year}' => $day->format('Y'),
    ]);

    return $capitalize ? ucfirst($label) : $label;
}

function mim_l10n_date_long(string $date, bool $capitalize = false): string
{
    return mim_l10n_date($date, mim_date_locale()['long'], $capitalize);
}

function mim_l10n_date_day_month(string $date): string
{
    return mim_l10n_date($date, mim_date_locale()['medium']);
}

function mim_l10n_date_dot(string $date): string
{
    return mim_day($date)->format('d.m.y');
}

function mim_l10n_weekday_dot(string $date, bool $withPeriod = true): string
{
    return rtrim(mim_l10n_weekday((int) mim_day($date)->format('N'), true), '.') . ($withPeriod ? '.' : '');
}

/** "Jeu. 28.01" in the visitor language. */
function mim_l10n_date_day_dot(string $date, bool $withPeriod = true): string
{
    return mim_l10n_weekday_dot($date, $withPeriod) . ' ' . mim_day($date)->format('d.m');
}

/** Decimal separator used for distances in the visitor language. */
function mim_l10n_decimal(string $value): string
{
    return str_replace('.', mim_date_locale()['decimal'], $value);
}

/** "07:00" written the way the visitor language writes it: 07h00 in French, 07:00 elsewhere. */
function mim_l10n_hour(string $time): string
{
    return mim_time($time, mim_date_locale()['timeSep']);
}

function mim_l10n_edition_sup(int $edition): string
{
    $set = mim_date_locale()['sup'];

    return $edition === 1 ? $set['first'] : $set['other'];
}
