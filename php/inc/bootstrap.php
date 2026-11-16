<?php
declare(strict_types=1);

date_default_timezone_set('Africa/Casablanca');

define('MIM_ROOT', dirname(__DIR__));
define('MIM_DATA', MIM_ROOT . '/data');

require __DIR__ . '/localize.php';
require __DIR__ . '/legal.php';
require __DIR__ . '/gallery.php';
require __DIR__ . '/db.php';

const MIM_DEFAULTS = [
    'edition' => 37,
    'marathon_date' => '2027-01-31',
    'marathon_start' => '07:00',
    'semi_date' => '2027-01-31',
    'semi_start' => '08:30',
    'tous_date' => '2027-01-30',
    'tous_start' => '09:00',
];

const MIM_WEEKDAYS = [
    1 => 'lundi', 2 => 'mardi', 3 => 'mercredi', 4 => 'jeudi',
    5 => 'vendredi', 6 => 'samedi', 7 => 'dimanche',
];

const MIM_WEEKDAYS_SHORT = [
    1 => 'Lun.', 2 => 'Mar.', 3 => 'Mer.', 4 => 'Jeu.',
    5 => 'Ven.', 6 => 'Sam.', 7 => 'Dim.',
];

const MIM_MONTHS = [
    1 => 'janvier', 2 => 'février', 3 => 'mars', 4 => 'avril', 5 => 'mai', 6 => 'juin',
    7 => 'juillet', 8 => 'août', 9 => 'septembre', 10 => 'octobre', 11 => 'novembre', 12 => 'décembre',
];

function mim_is_date(string $value): bool
{
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
        return false;
    }
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);

    return $date instanceof DateTimeImmutable && $date->format('Y-m-d') === $value;
}

function mim_is_time(string $value): bool
{
    return (bool) preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $value);
}

function mim_schedule(): array
{
    static $schedule = null;

    if ($schedule !== null) {
        return $schedule;
    }

    $schedule = MIM_DEFAULTS;
    $decoded = mim_store_read('schedule');

    if (is_array($decoded)) {
        foreach (MIM_DEFAULTS as $key => $default) {
            if (!array_key_exists($key, $decoded)) {
                continue;
            }
            $value = $decoded[$key];
            if ($key === 'edition') {
                $value = (int) $value;
                if ($value >= 1 && $value <= 999) {
                    $schedule[$key] = $value;
                }
            } elseif (str_ends_with($key, '_date')) {
                if (is_string($value) && mim_is_date($value)) {
                    $schedule[$key] = $value;
                }
            } elseif (is_string($value) && mim_is_time($value)) {
                $schedule[$key] = $value;
            }
        }
    }

    return $schedule;
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function mim_dt(string $date, string $time): DateTimeImmutable
{
    return new DateTimeImmutable($date . ' ' . $time, new DateTimeZone(date_default_timezone_get()));
}

function mim_day(string $date): DateTimeImmutable
{
    return new DateTimeImmutable($date . ' 12:00:00', new DateTimeZone(date_default_timezone_get()));
}

function mim_date_dot(string $date): string
{
    return mim_day($date)->format('d.m.y');
}

function mim_date_day_dot(string $date, bool $withPeriod = true): string
{
    $day = mim_day($date);
    $label = MIM_WEEKDAYS_SHORT[(int) $day->format('N')];

    if (!$withPeriod) {
        $label = rtrim($label, '.');
    }

    return $label . ' ' . $day->format('d.m');
}

function mim_date_day_num(string $date): string
{
    $day = mim_day($date);

    return rtrim(MIM_WEEKDAYS_SHORT[(int) $day->format('N')], '.') . ' ' . (int) $day->format('j');
}

function mim_date_long(string $date, bool $capitalize = false): string
{
    $day = mim_day($date);
    $label = MIM_WEEKDAYS[(int) $day->format('N')] . ' '
        . (int) $day->format('j') . ' '
        . MIM_MONTHS[(int) $day->format('n')] . ' '
        . $day->format('Y');

    return $capitalize ? ucfirst($label) : $label;
}

function mim_date_long_short(string $date): string
{
    $day = mim_day($date);

    return strtolower(MIM_WEEKDAYS_SHORT[(int) $day->format('N')]) . ' '
        . (int) $day->format('j') . ' '
        . MIM_MONTHS[(int) $day->format('n')] . ' '
        . $day->format('Y');
}

function mim_date_day_month(string $date): string
{
    $day = mim_day($date);

    return MIM_WEEKDAYS[(int) $day->format('N')] . ' '
        . (int) $day->format('j') . ' '
        . MIM_MONTHS[(int) $day->format('n')];
}

function mim_time(string $time, string $separator = ':'): string
{
    return str_replace(':', $separator, $time);
}

function mim_race_start(string $race, bool $withPeriod = true): string
{
    $schedule = mim_schedule();

    return mim_l10n_weekday_dot($schedule[$race . '_date'], $withPeriod)
        . ' — ' . mim_time($schedule[$race . '_start']);
}

function mim_edition_sup(int $edition): string
{
    return $edition === 1 ? 'er' : 'e';
}

function mim_target_iso(): string
{
    $schedule = mim_schedule();

    return mim_dt($schedule['marathon_date'], $schedule['marathon_start'])->format('c');
}

/**
 * Village days are placed relative to the marathon: J-3, J-2 and J-1.
 *
 * @return array{jeu: string, ven: string, sam: string, dim: string}
 */
function mim_programme_days(): array
{
    $schedule = mim_schedule();
    $race = mim_day($schedule['marathon_date']);
    $offset = static fn (int $days): string => $race->modify(($days >= 0 ? '+' : '') . $days . ' days')->format('Y-m-d');

    return [
        'jeu' => $offset(-3),
        'ven' => $offset(-2),
        'sam' => $offset(-1),
        'dim' => $schedule['marathon_date'],
    ];
}

/**
 * URL d’un fichier de la racine, horodatée à sa dernière modification :
 * le navigateur peut garder la feuille de style en cache sept jours, il faut donc
 * changer l’URL quand le contenu change.
 */
function mim_asset(string $file): string
{
    $modified = @filemtime(MIM_ROOT . '/' . ltrim($file, '/'));

    return $file . ($modified === false ? '' : '?v=' . $modified);
}

function mim_save_json(string $file, array $data): bool
{
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($json === false) {
        return false;
    }
    $temporary = $file . '.tmp';
    if (file_put_contents($temporary, $json . "\n", LOCK_EX) === false) {
        return false;
    }

    return rename($temporary, $file);
}

function mim_flash(?string $message = null, string $type = 'ok'): ?array
{
    if ($message !== null) {
        $_SESSION['mim_flash'] = ['text' => $message, 'type' => $type];

        return null;
    }

    $flash = $_SESSION['mim_flash'] ?? null;
    unset($_SESSION['mim_flash']);

    return $flash;
}

function mim_is_http_url(string $value): bool
{
    if (filter_var($value, FILTER_VALIDATE_URL) === false) {
        return false;
    }
    $scheme = strtolower((string) parse_url($value, PHP_URL_SCHEME));

    return $scheme === 'http' || $scheme === 'https';
}

/**
 * Pages publiques du menu principal, dans l'ordre de lecture. Le numéro affiché d'une
 * page est dérivé de sa position : insérer une page ne demande plus de renuméroter
 * les huit autres à la main.
 */
const MIM_NAV = [
    'index', 'epreuves', 'parcours', 'programme', 'primes', 'village', 'galerie', 'infos', 'inscriptions',
];

/** @return array<string, string> clé de page => numéro « 00 »… */
function mim_nav_pages(): array
{
    $pages = [];
    foreach (MIM_NAV as $position => $key) {
        $pages[$key] = sprintf('%02d', $position);
    }

    return $pages;
}

/**
 * Position de la page courante dans le menu.
 *
 * @return array{no: string, total: string, next: string, nextNo: string, prev: string}
 */
function mim_nav_position(string $page): array
{
    $position = array_search($page, MIM_NAV, true);
    if ($position === false) {
        $position = 0;
    }

    $last = count(MIM_NAV) - 1;
    $next = $position === $last ? 'index' : MIM_NAV[$position + 1];
    $prev = $position === 0 ? MIM_NAV[$last] : MIM_NAV[$position - 1];

    return [
        'no' => sprintf('%02d', $position),
        'total' => sprintf('%02d', $last),
        'next' => $next,
        'nextNo' => sprintf('%02d', $next === 'index' ? 0 : $position + 1),
        'prev' => $prev,
    ];
}

/**
 * Adresse absolue du site (schéma + hôte), vide en local sans en-tête Host.
 * Les données structurées et le sitemap ont besoin d’URLs complètes.
 */
function mim_origin(): string
{
    $host = (string) ($_SERVER['HTTP_HOST'] ?? '');

    if ($host === '') {
        return '';
    }

    $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

    return ($secure ? 'https' : 'http') . '://' . $host;
}

/** URL absolue d’un fichier du site, ou chemin relatif si l’hôte est inconnu. */
function mim_absolute(string $relative): string
{
    $origin = mim_origin();

    return $origin === '' ? $relative : $origin . '/' . ltrim($relative, '/');
}

/**
 * URL absolue de la page en cours de service, réécriture ou sous-dossier compris.
 * Vide tant que l’hôte n’est pas connu.
 */
function mim_page_url(bool $withLang = false): string
{
    $origin = mim_origin();

    if ($origin === '') {
        return '';
    }

    $path = strtok((string) ($_SERVER['REQUEST_URI'] ?? '/'), '?');

    return $origin . $path . ($withLang ? '?lang=' . mim_lang() : '');
}

/**
 * @return array{registration_url: string}
 */
function mim_site(): array
{
    static $site = null;

    if ($site !== null) {
        return $site;
    }

    $site = ['registration_url' => ''];
    $decoded = mim_store_read('site');

    if (is_array($decoded)) {
        $url = trim((string) ($decoded['registration_url'] ?? ''));
        if ($url !== '' && mim_is_http_url($url)) {
            $site['registration_url'] = $url;
        }
    }

    return $site;
}

/**
 * Identité juridique, e-mails officiels, tarifs de référence et règles de remboursement.
 * Tout est saisi dans adminbtx → onglet Légal ; rien n'est inventé, les champs vides
 * laissent simplement le bloc correspondant masqué sur le site.
 *
 * @return array{
 *   entity: array{name: string, ein: string, street: string, city: string, state: string, zip: string, country: string},
 *   association: array{name: string, role: string},
 *   emails: array{contact: string, support: string, legal: string},
 *   pricing: array{billing: string, marathon: string, semi: string, tous: string, fx_source: string, fx_moment: string},
 *   policies: array{terms_updated: string, privacy_updated: string, refund_updated: string, refund_deadline: string, deferral_deadline: string, deferral_fee: string}
 * }
 */
function mim_legal(): array
{
    static $legal = null;

    if ($legal !== null) {
        return $legal;
    }

    $legal = [
        'entity' => [
            'name' => '', 'ein' => '', 'street' => '', 'city' => '', 'state' => '', 'zip' => '', 'country' => '',
        ],
        'association' => [
            'name' => 'Association Le Grand Atlas',
            'role' => '',
        ],
        'emails' => [
            'contact' => '', 'support' => '', 'legal' => '',
        ],
        'pricing' => [
            'billing' => 'USD',
            'marathon' => '', 'semi' => '', 'tous' => '',
            'eur_marathon' => '', 'eur_semi' => '', 'eur_tous' => '',
            'mad_marathon' => '', 'mad_semi' => '', 'mad_tous' => '',
            'fx_source' => '', 'fx_moment' => '',
        ],
        'policies' => [
            'terms_updated' => '', 'privacy_updated' => '', 'refund_updated' => '',
            'refund_deadline' => '', 'deferral_deadline' => '', 'deferral_fee' => '',
        ],
    ];

    $decoded = mim_store_read('legal');

    if (is_array($decoded)) {
        foreach ($legal as $section => $fields) {
            if (!isset($decoded[$section]) || !is_array($decoded[$section])) {
                continue;
            }
            foreach ($fields as $key => $default) {
                if (isset($decoded[$section][$key]) && is_scalar($decoded[$section][$key])) {
                    $value = trim((string) $decoded[$section][$key]);
                    if ($value !== '') {
                        $legal[$section][$key] = $value;
                    }
                }
            }
        }
    }

    return $legal;
}

/** Adresse e-mail officielle d'un type (« contact », « support », « legal »). */
function mim_email(string $kind = 'contact'): string
{
    return mim_legal()['emails'][$kind] ?? mim_legal()['emails']['contact'];
}

/** L'entité américaine est-elle renseignée assez pour être affichée comme mention légale ? */
function mim_entity_ready(): bool
{
    $entity = mim_legal()['entity'];

    return $entity['name'] !== '' && $entity['ein'] !== '' && $entity['street'] !== '';
}

/** Prix de référence en devise facturée pour une épreuve, ou chaîne vide si non saisi. */
function mim_price(string $race): string
{
    $pricing = mim_legal()['pricing'];

    return (string) ($pricing[$race] ?? '');
}

/**
 * @return array<string, int> image paths relative to the site root ("assets/…") => size in bytes
 */
function mim_image_files(): array
{
    static $files = null;

    if ($files !== null) {
        return $files;
    }

    $files = [];
    $extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif', 'svg'];
    $directory = MIM_ROOT . '/assets';

    if (is_dir($directory)) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if (!$file->isFile() || !in_array(strtolower($file->getExtension()), $extensions, true)) {
                continue;
            }
            $relative = 'assets/' . str_replace('\\', '/', $iterator->getSubPathname());
            if (str_starts_with($relative, MIM_GALLERY_DIR . '/')) {
                continue;
            }
            $files[$relative] = (int) $file->getSize();
        }
    }

    ksort($files);

    return $files;
}

/**
 * @return array{quota_mb: int, media: array<string, array{edition: int, ref: string, alt: string, legende: string}>}
 */
function mim_media_data(): array
{
    static $data = null;

    if ($data !== null) {
        return $data;
    }

    $data = ['quota_mb' => 500, 'media' => []];
    $decoded = mim_store_read('media');

    if (is_array($decoded)) {
        $quota = (int) ($decoded['quota_mb'] ?? 0);
        if ($quota >= 1 && $quota <= 102400) {
            $data['quota_mb'] = $quota;
        }
        if (isset($decoded['media']) && is_array($decoded['media'])) {
            foreach ($decoded['media'] as $name => $entry) {
                if (!is_string($name) || !is_array($entry)) {
                    continue;
                }
                $data['media'][$name] = [
                    'edition' => max(0, min(999, (int) ($entry['edition'] ?? 0))),
                    'ref' => substr((string) ($entry['ref'] ?? ''), 0, 255),
                    'alt' => substr((string) ($entry['alt'] ?? ''), 0, 255),
                    'legende' => substr((string) ($entry['legende'] ?? ''), 0, 500),
                ];
            }
        }
    }

    return $data;
}

/**
 * @param array<string, array{edition: int, ref: string, alt: string, legende: string}> $media
 * @return array{edition: int, ref: string, alt: string, legende: string}
 */
function mim_media_entry(array $media, string $file): array
{
    return $media[$file] ?? ['edition' => 0, 'ref' => '', 'alt' => '', 'legende' => ''];
}

function mim_format_bytes(int $bytes): string
{
    if ($bytes >= 1048576) {
        $value = rtrim(rtrim(number_format($bytes / 1048576, 1, ',', ' '), '0'), ',');

        return $value . ' Mo';
    }

    return max(1, (int) round($bytes / 1024)) . ' Ko';
}
