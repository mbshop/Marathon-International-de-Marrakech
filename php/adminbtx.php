<?php
declare(strict_types=1);

require __DIR__ . '/inc/bootstrap.php';

// Le back-office reste en français : il n'est pas publié et n'est pas traduit.
$GLOBALS['mim_lang_pin'] = 'fr';

/* Compte créé au premier démarrage, à remplacer depuis l’onglet Sécurité. */
const MIM_ADMIN_DEFAULT_LOGIN = 'adminbtx';
const MIM_ADMIN_DEFAULT_PASSWORD = 'adminbtx';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ]);
    session_start();
}

function mim_admin_csrf(): string
{
    if (empty($_SESSION['mim_csrf'])) {
        $_SESSION['mim_csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['mim_csrf'];
}

function mim_admin_csrf_ok(): bool
{
    return hash_equals($_SESSION['mim_csrf'] ?? '', (string) ($_POST['csrf'] ?? ''));
}

function mim_admin_redirect(string $hash = ''): void
{
    header('Location: adminbtx.php' . ($hash !== '' ? '#' . $hash : ''));
    exit;
}

function mim_admin_media_redirect(string $filter): void
{
    $target = 'adminbtx.php';
    if ($filter !== 'all') {
        $target .= '?media_edition=' . urlencode($filter);
    }
    header('Location: ' . $target . '#tab-medias');
    exit;
}

/** Filtre d’édition du panneau Galerie : « all » ou un numéro de classe. */
function mim_admin_gallery_filter(string $filter): string
{
    return $filter === 'all' ? 'all' : (string) (int) $filter;
}

function mim_admin_gallery_redirect(string $filter): void
{
    $target = 'adminbtx.php';
    if ($filter !== 'all') {
        $target .= '?gallery_edition=' . urlencode($filter);
    }
    header('Location: ' . $target . '#tab-galerie');
    exit;
}

/**
 * Normalise un champ « <input type="file" name="photos[]" multiple> ».
 *
 * @return array<int, array{name: string, tmp_name: string, size: int, error: int}>
 */
function mim_admin_uploads(string $field): array
{
    $input = $_FILES[$field] ?? null;

    if (!is_array($input) || !is_array($input['name'] ?? null)) {
        return [];
    }

    $uploads = [];
    foreach (array_keys($input['name']) as $index) {
        $name = trim((string) ($input['name'][$index] ?? ''));
        if ($name === '') {
            continue;
        }
        $uploads[] = [
            'name' => $name,
            'tmp_name' => (string) ($input['tmp_name'][$index] ?? ''),
            'size' => (int) ($input['size'][$index] ?? 0),
            'error' => (int) ($input['error'][$index] ?? UPLOAD_ERR_NO_FILE),
        ];
    }

    return $uploads;
}

function mim_admin_upload_reason(int $code): string
{
    return match ($code) {
        UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'dépasse la limite d’upload de PHP',
        UPLOAD_ERR_PARTIAL => 'téléversement interrompu : réessayez',
        UPLOAD_ERR_NO_FILE => 'aucun fichier reçu',
        UPLOAD_ERR_NO_TMP_DIR => 'dossier temporaire manquant sur le serveur',
        UPLOAD_ERR_CANT_WRITE => 'écriture sur le disque impossible',
        UPLOAD_ERR_EXTENSION => 'blocage par une extension PHP',
        default => 'erreur de téléversement inconnue',
    };
}

/** Poids maximum réellement accepté, en tenant compte des limites de PHP. */
function mim_admin_upload_limit(): int
{
    $weight = static function (string $value): int {
        $value = trim($value);
        if ($value === '') {
            return 0;
        }
        $multiplier = match (strtoupper(substr($value, -1))) {
            'G' => 1073741824,
            'M' => 1048576,
            'K' => 1024,
            default => 1,
        };

        return (int) ((float) $value * $multiplier);
    };

    $upload = $weight((string) ini_get('upload_max_filesize'));
    $post = $weight((string) ini_get('post_max_size'));
    $limit = $upload > 0 ? $upload : MIM_GALLERY_MAX_INPUT;

    if ($post > 0) {
        $limit = min($limit, $post);
    }

    return max(1, min($limit, MIM_GALLERY_MAX_INPUT));
}

function mim_admin_credentials(): ?array
{
    /* Une base installée avant l’identifiant personnalisable n’a pas la colonne login. */
    mim_db_migrate();

    $stored = mim_store_read('admin');

    if (!isset($stored['password_hash']) || $stored['password_hash'] === '') {
        if (!mim_store_writable()) {
            return null;
        }
        mim_store_write('admin', [
            'login' => MIM_ADMIN_DEFAULT_LOGIN,
            'password_hash' => password_hash(MIM_ADMIN_DEFAULT_PASSWORD, PASSWORD_DEFAULT),
        ]);
        $stored = mim_store_read('admin');
    }

    if (!is_array($stored) || !isset($stored['password_hash']) || $stored['password_hash'] === '') {
        return null;
    }

    /* Configuration d’avant l’identifiant : on l’aligne sur le compte par défaut. */
    if (!isset($stored['login']) || $stored['login'] === '') {
        $stored['login'] = MIM_ADMIN_DEFAULT_LOGIN;
        mim_store_write('admin', $stored);
    }

    return $stored;
}

/** Réglages MySQL saisis dans l’onglet « Base de données », complétés par les valeurs actives. */
function mim_admin_db_candidate(): array
{
    $current = mim_db_config();
    $pass = (string) ($_POST['db_pass'] ?? '');

    /* Champ vide : le mot de passe enregistré reste en place, sauf effacement demandé. */
    if ($pass === '' && empty($_POST['db_pass_clear'])) {
        $pass = (string) $current['pass'];
    }

    return [
        'host' => trim((string) ($_POST['db_host'] ?? '')),
        'port' => (int) ($_POST['db_port'] ?? 0),
        'name' => trim((string) ($_POST['db_name'] ?? '')),
        'user' => trim((string) ($_POST['db_user'] ?? '')),
        'pass' => $pass,
        'charset' => 'utf8mb4',
    ];
}

function mim_admin_db_errors(array $config): array
{
    $errors = [];

    if (preg_match('/^[A-Za-z0-9._:\-\[\]]{1,255}$/', (string) $config['host']) !== 1) {
        $errors[] = 'L’hôte accepte lettres, chiffres, point, tiret, deux-points et crochets (adresse IPv6).';
    }
    if ((int) $config['port'] < 1 || (int) $config['port'] > 65535) {
        $errors[] = 'Le port doit être compris entre 1 et 65535.';
    }
    if (preg_match('/^[A-Za-z0-9_]{1,64}$/', (string) $config['name']) !== 1) {
        $errors[] = 'Le nom de la base accepte lettres, chiffres et souligné, 64 caractères au plus.';
    }
    if (preg_match('/^[A-Za-z0-9._@\-]{1,64}$/', (string) $config['user']) !== 1) {
        $errors[] = 'L’utilisateur MySQL accepte lettres, chiffres, point, tiret, arobase et souligné, 64 caractères au plus.';
    }
    if (mb_strlen((string) $config['pass']) > 255) {
        $errors[] = 'Le mot de passe MySQL ne peut pas dépasser 255 caractères.';
    }

    return $errors;
}

/** Traduit un échec PDO en conséquence lisible pour l’administrateur. */
function mim_admin_db_reason(PDOException $exception): string
{
    $info = $exception->errorInfo;
    $driver = is_array($info) && isset($info[1]) ? (int) $info[1] : 0;

    if ($driver === 0 && preg_match('/\[(\d{4})\]/', $exception->getMessage(), $match)) {
        $driver = (int) $match[1];
    }

    return match ($driver) {
        1049 => 'La base de données n’existe pas encore : utilisez « Créer la base et les tables ».',
        1045 => 'Identifiant ou mot de passe MySQL refusé par le serveur.',
        1044 => 'Cet utilisateur n’a pas les droits sur cette base.',
        2002 => 'Serveur MySQL injoignable : vérifiez l’hôte et le port.',
        2005 => 'Hôte MySQL inconnu : vérifiez l’adresse du serveur.',
        default => 'Connexion MySQL impossible. ' . $exception->getMessage(),
    };
}

$credentials = mim_admin_credentials();
$isAuthenticated = !empty($_SESSION['mim_admin']);
$errors = [];
$dbErrors = [];
$dbTest = null;
$form = mim_schedule();
$passwordErrors = [];
$mediaErrors = [];
$galleryErrors = [];
$registrationErrors = [];
$registrationUrl = mim_site()['registration_url'];
$legalErrors = [];
$legal = mim_legal();
$firstRun = $credentials !== null && password_verify(MIM_ADMIN_DEFAULT_PASSWORD, (string) $credentials['password_hash']);

/* ---------- actions ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && mim_admin_csrf_ok()) {
    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'login' && !$isAuthenticated) {
        $tries = $_SESSION['mim_admin_tries'] ?? ['count' => 0, 'time' => 0];
        $blocked = $tries['count'] >= 6 && time() - $tries['time'] < 600;
        $login = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if ($credentials === null) {
            $errors[] = 'Impossible de vérifier le compte. ' . mim_store_failure();
        } elseif ($blocked) {
            $errors[] = 'Trop de tentatives. Réessayez dans quelques minutes.';
        } elseif (
            hash_equals((string) $credentials['login'], $login)
            && password_verify($password, (string) $credentials['password_hash'])
        ) {
            session_regenerate_id(true);
            $_SESSION['mim_admin'] = true;
            unset($_SESSION['mim_admin_tries']);
            mim_admin_redirect();
        } else {
            sleep(1);
            $_SESSION['mim_admin_tries'] = ['count' => $tries['count'] + 1, 'time' => time()];
            $errors[] = 'Identifiant ou mot de passe incorrect.';
        }
    } elseif ($action === 'logout') {
        $_SESSION = [];
        session_destroy();
        header('Location: adminbtx.php');
        exit;
    } elseif ($action === 'schedule' && $isAuthenticated) {
        $form = [
            'edition' => (int) ($_POST['edition'] ?? 0),
            'marathon_date' => (string) ($_POST['marathon_date'] ?? ''),
            'marathon_start' => (string) ($_POST['marathon_start'] ?? ''),
            'semi_date' => (string) ($_POST['semi_date'] ?? ''),
            'semi_start' => (string) ($_POST['semi_start'] ?? ''),
            'tous_date' => (string) ($_POST['tous_date'] ?? ''),
            'tous_start' => (string) ($_POST['tous_start'] ?? ''),
        ];

        if ($form['edition'] < 1 || $form['edition'] > 999) {
            $errors[] = 'Le numéro d’édition doit être compris entre 1 et 999.';
        }
        foreach (['marathon', 'semi', 'tous'] as $race) {
            if (!mim_is_date($form[$race . '_date'])) {
                $errors[] = 'La date du ' . $race . ' est invalide.';
            }
            if (!mim_is_time($form[$race . '_start'])) {
                $errors[] = 'L’heure de départ du ' . $race . ' est invalide.';
            }
        }

        if (!$errors) {
            if (!mim_store_writable()) {
                $errors[] = 'Le dossier data/ n’est pas accessible en écriture : enregistrement impossible.';
            } elseif (!mim_store_write('schedule', $form)) {
                $errors[] = 'L’enregistrement a échoué. ' . mim_store_failure();
            } else {
                mim_flash('Dates et heures de départ enregistrées. Les pages publiques sont à jour.');
                mim_admin_redirect('tab-horaires');
            }
        }
    } elseif ($action === 'credentials' && $isAuthenticated) {
        $current = (string) ($_POST['current_password'] ?? '');
        $login = trim((string) ($_POST['login'] ?? ''));
        $new = (string) ($_POST['new_password'] ?? '');
        $confirm = (string) ($_POST['confirm_password'] ?? '');

        if ($credentials === null || !password_verify($current, (string) $credentials['password_hash'])) {
            $passwordErrors[] = 'Le mot de passe actuel est incorrect.';
        }
        if (preg_match('/^[A-Za-z0-9._-]{3,64}$/', $login) !== 1) {
            $passwordErrors[] = 'L’identifiant doit compter de 3 à 64 caractères : lettres, chiffres, point, tiret ou souligné.';
        }

        /* Les deux champs de mot de passe restent vides quand seul l’identifiant change. */
        $hash = (string) ($credentials['password_hash'] ?? '');
        if ($new !== '' || $confirm !== '') {
            if (mb_strlen($new) < 10) {
                $passwordErrors[] = 'Le nouveau mot de passe doit contenir au moins 10 caractères.';
            }
            if ($new !== $confirm) {
                $passwordErrors[] = 'La confirmation ne correspond pas au nouveau mot de passe.';
            }
            $hash = password_hash($new, PASSWORD_DEFAULT);
        }

        if (!$passwordErrors) {
            if (!mim_store_write('admin', ['login' => $login, 'password_hash' => $hash])) {
                $passwordErrors[] = 'L’enregistrement du compte a échoué. ' . mim_store_failure();
            } else {
                mim_flash('Identifiant et mot de passe enregistrés.');
                mim_admin_redirect('tab-securite');
            }
        }
    } elseif (in_array($action, ['db_test', 'db_save', 'db_create', 'db_import'], true) && $isAuthenticated) {
        $dbCandidate = mim_admin_db_candidate();
        $dbErrors = mim_admin_db_errors($dbCandidate);

        if ($action === 'db_test' && !$dbErrors) {
            try {
                $dbPdo = mim_db_connect($dbCandidate);
                $version = (string) $dbPdo->query('SELECT VERSION()')->fetchColumn();
                $tables = $dbPdo->prepare('SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = ?');
                $tables->execute([$dbCandidate['name']]);
                $found = array_map('strval', $tables->fetchAll(PDO::FETCH_COLUMN));
                $dbTest = [
                    'host' => $dbCandidate['host'],
                    'port' => (int) $dbCandidate['port'],
                    'database' => $dbCandidate['name'],
                    'version' => $version,
                    'tables' => count($found),
                    'missing' => array_values(array_diff(array_keys(mim_db_tables()), $found)),
                ];
            } catch (PDOException $exception) {
                $dbErrors[] = mim_admin_db_reason($exception);
            }
        } elseif (!$dbErrors) {
            /* Les trois autres actions travaillent sur les réglages affichés : on les écrit d’abord. */
            if (!mim_db_save_config($dbCandidate)) {
                $dbErrors[] = 'Écriture de config/database.local.php impossible : vérifiez les droits du dossier config/.';
            } else {
                mim_db_reset();

                try {
                    if ($action === 'db_save') {
                        mim_flash('Réglages MySQL enregistrés dans config/database.local.php.');
                    } elseif ($action === 'db_create') {
                        mim_db_install(false, true);
                        mim_flash('Base « ' . $dbCandidate['name'] . ' » et ' . count(mim_db_tables()) . ' tables vérifiées. Importez data/ pour recopier la configuration.');
                    } else {
                        $dbLog = mim_db_install(true, false);
                        mim_flash('Configuration recopiée dans la base (' . count($dbLog) . ' opération(s)).');
                    }
                    mim_admin_redirect('tab-base');
                } catch (Throwable $exception) {
                    $dbErrors[] = 'Opération MySQL impossible : ' . $exception->getMessage();
                }
            }
        }
    } elseif ($action === 'media_quota' && $isAuthenticated) {
        $quota = (int) ($_POST['quota_mb'] ?? 0);

        if ($quota < 1 || $quota > 102400) {
            $mediaErrors[] = 'Le quota doit être compris entre 1 et 102 400 Mo.';
        } else {
            $data = mim_media_data();
            $payload = ['quota_mb' => $quota, 'media' => $data['media']];
            if (!mim_store_writable()) {
                $mediaErrors[] = 'Le dossier data/ n’est pas accessible en écriture : enregistrement impossible.';
            } elseif (!mim_store_write('media', $payload)) {
                $mediaErrors[] = 'L’enregistrement du quota a échoué. ' . mim_store_failure();
            } else {
                mim_flash('Quota de stockage mis à jour.');
                mim_admin_redirect('tab-medias');
            }
        }
    } elseif ($action === 'media_save' && $isAuthenticated) {
        $file = (string) ($_POST['file'] ?? '');
        $files = mim_image_files();
        $entry = [
            'edition' => (int) ($_POST['edition'] ?? -1),
            'ref' => trim((string) ($_POST['ref'] ?? '')),
            'alt' => trim((string) ($_POST['alt'] ?? '')),
            'legende' => trim((string) ($_POST['legende'] ?? '')),
        ];
        $filter = (string) ($_POST['media_edition'] ?? 'all');
        $filter = $filter === 'all' ? 'all' : (string) (int) $filter;

        $seoBrand = 'Marathon International de Marrakech';
        foreach (['alt' => 255, 'legende' => 500] as $field => $limit) {
            $value = $entry[$field];
            if ($value === '' && $field === 'alt') {
                $value = $seoBrand . ' — ' . ($entry['ref'] !== '' ? $entry['ref'] : basename($file));
            }
            if ($value !== '' && mb_stripos($value, $seoBrand) === false) {
                $value .= ' — ' . $seoBrand;
            }
            $entry[$field] = substr($value, 0, $limit);
        }

        if (!isset($files[$file])) {
            $mediaErrors[] = 'Fichier introuvable dans le dossier assets/.';
        }
        if ($entry['edition'] < 0 || $entry['edition'] > 999) {
            $mediaErrors[] = 'L’édition doit être comprise entre 0 (non classé) et 999.';
        }
        if (mb_strlen($entry['ref']) > 255 || mb_strlen($entry['alt']) > 255 || mb_strlen($entry['legende']) > 500) {
            $mediaErrors[] = 'Textes trop longs : 255 caractères max pour la référence et l’alt, 500 pour la légende.';
        }

        if (!$mediaErrors) {
            $data = mim_media_data();
            $payload = ['quota_mb' => $data['quota_mb'], 'media' => $data['media']];
            $payload['media'][$file] = $entry;
            if (!mim_store_writable()) {
                $mediaErrors[] = 'Le dossier data/ n’est pas accessible en écriture : enregistrement impossible.';
            } elseif (!mim_store_write('media', $payload)) {
                $mediaErrors[] = 'L’enregistrement du média a échoué. ' . mim_store_failure();
            } else {
                mim_flash('Média « ' . basename($file) . ' » enregistré.');
                mim_admin_media_redirect($filter);
            }
        }
    } elseif ($action === 'gallery_upload' && $isAuthenticated) {
        $edition = (int) ($_POST['edition'] ?? -1);
        $ref = mb_substr(trim((string) ($_POST['ref'] ?? '')), 0, 255);
        $filter = mim_admin_gallery_filter((string) ($_POST['gallery_edition'] ?? 'all'));
        $uploads = mim_admin_uploads('photos');
        $data = mim_gallery_data();
        $quotaBytes = max(1, $data['quota_mb']) * 1048576;
        $used = mim_gallery_used_bytes();
        $limit = mim_admin_upload_limit();
        $seoBrand = 'Marathon International de Marrakech';
        $imported = 0;
        $rejections = [];
        $saved = false;

        if ($edition < 0 || $edition > 999) {
            $galleryErrors[] = 'L’édition doit être comprise entre 0 (à classer) et 999.';
        }
        if (!$uploads) {
            $galleryErrors[] = 'Aucune photo sélectionnée : choisissez un ou plusieurs fichiers.';
        }
        if (!mim_store_writable()) {
            $galleryErrors[] = mim_backend() === 'mysql'
                ? 'La base de données est injoignable : import impossible.'
                : 'Le dossier data/ n’est pas accessible en écriture : import impossible.';
        }

        if (!$galleryErrors) {
            foreach ($uploads as $upload) {
                $label = mb_substr(basename($upload['name']), 0, 60);

                if ($upload['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($upload['tmp_name'])) {
                    $rejections[] = '« ' . $label . ' » : ' . mim_admin_upload_reason($upload['error']) . '.';
                    continue;
                }
                if ($upload['size'] > $limit) {
                    $rejections[] = '« ' . $label . ' » : ' . mim_format_bytes($upload['size'])
                        . ' dépasse la limite acceptée par le serveur (' . mim_format_bytes($limit) . ').';
                    continue;
                }
                if ($used + $upload['size'] > $quotaBytes) {
                    $rejections[] = '« ' . $label . ' » : quota de la galerie saturé, '
                        . mim_format_bytes($used) . ' déjà utilisés sur ' . mim_format_bytes($quotaBytes) . '.';
                    continue;
                }

                [$uploadError, $photo] = mim_gallery_store($upload['tmp_name'], $edition, $ref);

                if ($uploadError !== '') {
                    $rejections[] = '« ' . $label . ' » : ' . $uploadError;
                    continue;
                }

                $file = (string) $photo['file'];
                unset($photo['file']);
                $subject = $photo['ref'] !== '' ? $photo['ref'] : pathinfo($file, PATHINFO_FILENAME);
                $photo['alt']['fr'] = $seoBrand . ' — ' . $subject;
                $data['photos'][$file] = $photo;
                $used += $photo['size'] + $photo['thumb_size'];
                $imported++;
            }

            if ($imported > 0) {
                $saved = mim_gallery_save($data);
                if (!$saved) {
                    $galleryErrors[] = 'L’enregistrement de la galerie a échoué. ' . mim_store_failure();
                }
            }
        }

        if ($saved) {
            if ($rejections) {
                mim_flash($imported . ' photo(s) importée(s) · ' . count($rejections) . ' fichier(s) rejeté(s) : '
                    . implode(' ', array_slice($rejections, 0, 3)), 'error');
            } else {
                mim_flash($imported . ' photo(s) importée(s) dans la galerie, ré-encodée(s) en WebP et sans métadonnées EXIF.');
            }
            mim_admin_gallery_redirect($filter);
        }

        foreach ($rejections as $rejection) {
            $galleryErrors[] = $rejection;
        }
    } elseif ($action === 'gallery_save' && $isAuthenticated) {
        $file = (string) ($_POST['file'] ?? '');
        $data = mim_gallery_data();
        $filter = mim_admin_gallery_filter((string) ($_POST['gallery_edition'] ?? 'all'));
        $seoBrand = 'Marathon International de Marrakech';
        $localeText = static function (string $field, string $locale): string {
            $values = $_POST[$field] ?? [];
            $raw = is_array($values) ? ($values[$locale] ?? '') : '';

            return is_scalar($raw) ? trim((string) $raw) : '';
        };

        if (!isset($data['photos'][$file])) {
            $galleryErrors[] = 'Photo introuvable dans la galerie : fiche non enregistrée.';
        } else {
            $entry = $data['photos'][$file];
            $edition = (int) ($_POST['edition'] ?? -1);
            $entry['edition'] = max(0, min(999, $edition));
            $entry['published'] = (string) ($_POST['published'] ?? '') === '1';
            $entry['ref'] = mb_substr(trim((string) ($_POST['ref'] ?? '')), 0, 255);
            $entry['credit'] = mb_substr(trim((string) ($_POST['credit'] ?? '')), 0, 120);
            $taken = trim((string) ($_POST['taken'] ?? ''));

            if ($edition < 0 || $edition > 999) {
                $galleryErrors[] = 'L’édition doit être comprise entre 0 (à classer) et 999.';
            }
            if ($taken === '' || mim_is_date($taken)) {
                $entry['taken'] = $taken;
            } else {
                $galleryErrors[] = 'Date de prise de vue invalide : utilisez le format AAAA-MM-JJ.';
            }

            foreach (array_keys(MIM_LOCALES) as $locale) {
                foreach (['alt' => 255, 'caption' => 500] as $field => $max) {
                    $value = mb_substr($localeText($field, $locale), 0, $max);
                    // Le repli des autres langues passe par le français : la marque y reste.
                    if ($locale === 'fr' && $field === 'alt' && $value !== ''
                        && mb_stripos($value, $seoBrand) === false) {
                        $value = mb_substr($value . ' — ' . $seoBrand, 0, $max);
                    }
                    $entry[$field][$locale] = $value;
                }
            }

            if (!$galleryErrors) {
                $data['photos'][$file] = $entry;
                if (!mim_store_writable()) {
                    $galleryErrors[] = 'Le dossier data/ n’est pas accessible en écriture : enregistrement impossible.';
                } elseif (!mim_gallery_save($data)) {
                    $galleryErrors[] = 'L’enregistrement de la fiche a échoué. ' . mim_store_failure();
                } else {
                    mim_flash('Fiche de « ' . basename($file) . ' » enregistrée. La page Galerie est à jour.');
                    mim_admin_gallery_redirect($filter);
                }
            }
        }
    } elseif ($action === 'gallery_delete' && $isAuthenticated) {
        $file = (string) ($_POST['file'] ?? '');
        $data = mim_gallery_data();
        $filter = mim_admin_gallery_filter((string) ($_POST['gallery_edition'] ?? 'all'));

        if (!isset($data['photos'][$file])) {
            $galleryErrors[] = 'Photo introuvable dans la galerie : suppression impossible.';
        } else {
            mim_gallery_remove($file);
            unset($data['photos'][$file]);
            if (!mim_gallery_save($data)) {
                $galleryErrors[] = 'La fiche n’a pas pu être retirée de la galerie. ' . mim_store_failure();
            } else {
                mim_flash('Photo « ' . basename($file) . ' » et sa miniature supprimées.');
                mim_admin_gallery_redirect($filter);
            }
        }
    } elseif ($action === 'gallery_quota' && $isAuthenticated) {
        $quota = (int) ($_POST['quota_mb'] ?? 0);
        $data = mim_gallery_data();

        if ($quota < 1 || $quota > 102400) {
            $galleryErrors[] = 'Le quota doit être compris entre 1 et 102 400 Mo.';
        } else {
            $data['quota_mb'] = $quota;
            if (!mim_store_writable()) {
                $galleryErrors[] = 'Le dossier data/ n’est pas accessible en écriture : enregistrement impossible.';
            } elseif (!mim_gallery_save($data)) {
                $galleryErrors[] = 'L’enregistrement du quota a échoué. ' . mim_store_failure();
            } else {
                mim_flash('Quota de la galerie mis à jour.');
                mim_admin_gallery_redirect('all');
            }
        }
    } elseif ($action === 'registration' && $isAuthenticated) {
        $registrationUrl = trim((string) ($_POST['registration_url'] ?? ''));

        if (mb_strlen($registrationUrl) > 500) {
            $registrationErrors[] = 'Le lien ne peut pas dépasser 500 caractères.';
        } elseif ($registrationUrl !== '' && !mim_is_http_url($registrationUrl)) {
            $registrationErrors[] = 'Le lien doit être une URL http(s) valide, par ex. https://runsign.vercel.app/.';
        }

        if (!$registrationErrors) {
            if (!mim_store_writable()) {
                $registrationErrors[] = 'Le dossier data/ n’est pas accessible en écriture : enregistrement impossible.';
            } elseif (!mim_store_write('site', ['registration_url' => $registrationUrl])) {
                $registrationErrors[] = 'L’enregistrement du lien a échoué. ' . mim_store_failure();
            } else {
                mim_flash($registrationUrl === ''
                    ? 'Lien d’inscription retiré : la page Inscriptions reprend le site officiel.'
                    : 'Lien de l’application d’inscription enregistré. La page Inscriptions est à jour.');
                mim_admin_redirect('tab-inscriptions');
            }
        }
    } elseif ($action === 'legal' && $isAuthenticated) {
        $text = static fn (string $key): string => trim((string) ($_POST[$key] ?? ''));
        $date = static function (string $key) use ($text) {
            $value = $text($key);
            if ($value === '') {
                return '';
            }
            $day = DateTime::createFromFormat('Y-m-d', $value);

            return $day && $day->format('Y-m-d') === $value ? $value : null;
        };

        $next = [
            'entity' => [
                'name' => $text('entity_name'),
                'ein' => strtoupper($text('entity_ein')),
                'street' => $text('entity_street'),
                'city' => $text('entity_city'),
                'state' => strtoupper($text('entity_state')),
                'zip' => $text('entity_zip'),
                'country' => $text('entity_country'),
            ],
            'association' => [
                'name' => $text('association_name'),
                'role' => $text('association_role'),
            ],
            'emails' => [
                'contact' => strtolower($text('email_contact')),
                'support' => strtolower($text('email_support')),
                'legal' => strtolower($text('email_legal')),
            ],
            'pricing' => [
                'billing' => strtoupper($text('pricing_billing')),
                'marathon' => $text('price_marathon'),
                'semi' => $text('price_semi'),
                'tous' => $text('price_tous'),
                'eur_marathon' => $text('price_eur_marathon'),
                'eur_semi' => $text('price_eur_semi'),
                'eur_tous' => $text('price_eur_tous'),
                'mad_marathon' => $text('price_mad_marathon'),
                'mad_semi' => $text('price_mad_semi'),
                'mad_tous' => $text('price_mad_tous'),
                'fx_source' => $text('fx_source'),
                'fx_moment' => $text('fx_moment'),
            ],
            'policies' => [
                'terms_updated' => $date('terms_updated'),
                'privacy_updated' => $date('privacy_updated'),
                'refund_updated' => $date('refund_updated'),
                'refund_deadline' => $date('refund_deadline'),
                'deferral_deadline' => $date('deferral_deadline'),
                'deferral_fee' => $text('deferral_fee'),
            ],
        ];

        foreach ($next['entity'] as $key => $value) {
            if (mb_strlen($value) > 120) {
                $legalErrors[] = 'Un champ de l’entité dépasse 120 caractères.';
                break;
            }
        }

        $legal = $next;

        if ($next['entity']['ein'] !== '' && !preg_match('/^\d{2}-\d{7}$/', $next['entity']['ein'])) {
            $legalErrors[] = 'Le numéro EIN doit être au format 12-3456789.';
        }

        if ($next['entity']['zip'] !== '' && !preg_match('/^\d{5}(-\d{4})?$/', $next['entity']['zip'])) {
            $legalErrors[] = 'Le ZIP doit être à 5 chiffres ou 5+4 (12345 ou 12345-6789).';
        }

        foreach ($next['emails'] as $kind => $value) {
            if ($value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $legalErrors[] = 'Adresse e-mail invalide : « ' . $value . ' ».';
            }
        }

        if (!preg_match('/^[A-Z]{3}$/', $next['pricing']['billing'])) {
            $legalErrors[] = 'La devise facturée doit être un code à 3 lettres, ex. USD.';
        }

        $priceFields = [
            'marathon' => $next['pricing']['billing'], 'semi' => $next['pricing']['billing'], 'tous' => $next['pricing']['billing'],
            'eur_marathon' => 'EUR', 'eur_semi' => 'EUR', 'eur_tous' => 'EUR',
            'mad_marathon' => 'MAD', 'mad_semi' => 'MAD', 'mad_tous' => 'MAD',
        ];
        foreach ($priceFields as $field => $currency) {
            $value = $next['pricing'][$field];
            if ($value !== '' && (!is_numeric($value) || (float) $value <= 0 || (float) $value > 100000)) {
                $legalErrors[] = 'Le prix « ' . $field . ' » doit être un nombre positif (en ' . $currency . ').';
            }
        }

        foreach ($next['policies'] as $key => $value) {
            if ($value === null) {
                $legalErrors[] = 'Date invalide (« ' . $key . ' ») : utilisez le format AAAA-MM-JJ.';
                $next['policies'][$key] = '';
            }
        }

        foreach ($next as $section => $fields) {
            foreach ($fields as $key => $value) {
                if (is_string($value) && mb_strlen($value) > 300) {
                    $legalErrors[] = 'Le champ « ' . $key . ' » est trop long (300 caractères max).';
                }
            }
        }

        if (!$legalErrors) {
            if (!mim_store_writable()) {
                $legalErrors[] = 'Le dossier data/ n’est pas accessible en écriture : enregistrement impossible.';
            } elseif (!mim_store_write('legal', $next)) {
                $legalErrors[] = 'L’enregistrement des mentions légales a échoué. ' . mim_store_failure();
            } else {
                mim_flash('Mentions légales, e-mails et tarifs enregistrés.');
                mim_admin_redirect('tab-legal');
            }
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    /* Une requête multiparts trop lourde est vidée par PHP avant l'arrivée des champs. */
    if (empty($_POST) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
        $galleryErrors[] = 'Envoi refusé : le poids cumulé dépasse la limite post_max_size de PHP ('
            . (string) ini_get('post_max_size') . '). Importez moins de photos à la fois.';
    } else {
        $errors[] = 'Session expirée : rechargez la page et réessayez.';
    }
}

$isAuthenticated = !empty($_SESSION['mim_admin']);
$flash = mim_flash();
$csrf = mim_admin_csrf();
$schedule = mim_schedule();
$updatedAt = mim_store_updated('schedule');

$mediaData = ['quota_mb' => 500, 'media' => []];
$mediaList = [];
$mediaFilter = 'all';
$mediaFilterEdition = null;
$mediaEditions = [];
$mediaUsed = 0;
$mediaTotal = 0;
$mediaCount = 0;

if ($isAuthenticated) {
    $files = mim_image_files();
    $mediaData = mim_media_data();
    $mediaFilter = (string) ($_GET['media_edition'] ?? 'all');
    $mediaFilterEdition = $mediaFilter === 'all' ? null : (int) $mediaFilter;

    foreach ($mediaData['media'] as $entry) {
        if (!in_array($entry['edition'], $mediaEditions, true)) {
            $mediaEditions[] = $entry['edition'];
        }
    }
    rsort($mediaEditions);

    foreach ($files as $relative => $size) {
        $mediaTotal += $size;
        $entry = mim_media_entry($mediaData['media'], $relative);
        if ($mediaFilterEdition !== null && $entry['edition'] !== $mediaFilterEdition) {
            continue;
        }
        $mediaUsed += $size;
        $mediaList[] = ['file' => $relative, 'size' => $size] + $entry;
    }
    $mediaCount = count($mediaList);
}

$mediaQuotaBytes = max(1, $mediaData['quota_mb']) * 1048576;
$mediaPercent = (int) round($mediaTotal / $mediaQuotaBytes * 100);
$mediaPercentCapped = min(100, $mediaPercent);

$galleryData = ['quota_mb' => 200, 'photos' => []];
$galleryList = [];
$galleryFilter = 'all';
$galleryFilterEdition = null;
$galleryEditions = [];
$galleryUsed = 0;
$galleryPublished = 0;
$galleryMissing = 0;
$galleryUnclassified = 0;

if ($isAuthenticated) {
    $galleryData = mim_gallery_data();
    $galleryFilter = mim_admin_gallery_filter((string) ($_GET['gallery_edition'] ?? 'all'));
    $galleryFilterEdition = $galleryFilter === 'all' ? null : (int) $galleryFilter;
    $galleryEditions = mim_gallery_editions();
    $galleryUsed = mim_gallery_used_bytes();

    foreach ($galleryData['photos'] as $file => $photo) {
        if ($photo['published']) {
            $galleryPublished++;
        }
        if (!is_file(MIM_ROOT . '/' . $file)) {
            $galleryMissing++;
        }
        if ($photo['edition'] === 0) {
            $galleryUnclassified++;
        }
        if ($galleryFilterEdition !== null && $photo['edition'] !== $galleryFilterEdition) {
            continue;
        }
        $photo['file'] = $file;
        $galleryList[] = $photo;
    }

    if ($galleryUnclassified > 0) {
        $galleryEditions[] = 0;
    }

    usort($galleryList, static fn (array $a, array $b): int => [$b['edition'], $b['added']] <=> [$a['edition'], $a['added']]);
}

$galleryQuotaBytes = max(1, $galleryData['quota_mb']) * 1048576;
$galleryPercent = (int) round($galleryUsed / $galleryQuotaBytes * 100);
$galleryPercentCapped = min(100, $galleryPercent);
$galleryUploadLimit = mim_admin_upload_limit();
$galleryCount = count($galleryList);

$dbConfig = mim_db_config();
$dbSource = mim_db_config_source();
$dbBackend = mim_backend();
$dbError = mim_db_error();
$dbRows = [];
$dbVersion = '';

if ($dbBackend === 'mysql') {
    try {
        $dbVersion = (string) mim_db()->query('SELECT VERSION()')->fetchColumn();
        foreach (array_keys(mim_db_tables()) as $dbTable) {
            $dbRows[$dbTable] = (int) mim_db()->query('SELECT COUNT(*) FROM `' . $dbTable . '`')->fetchColumn();
        }
    } catch (PDOException $exception) {
        $dbError = $exception->getMessage();
    }
}

$dbSourceLabel = match ($dbSource) {
    'env' => 'variables d’environnement MIM_DB_*',
    'file' => 'config/database.local.php',
    default => 'valeurs par défaut du code',
};

/* Un message d'erreur doit rester lisible là où l'action a été tentée. */
$admTabs = [
    'horaires' => 'Horaires',
    'medias' => 'Médias',
    'galerie' => 'Galerie',
    'inscriptions' => 'Inscriptions',
    'legal' => 'Légal',
    'base' => 'Base de données',
    'securite' => 'Sécurité',
];
$admTabKeys = array_keys($admTabs);
$activeTab = 'horaires';
$tabErrors = [
    'medias' => $mediaErrors,
    'galerie' => $galleryErrors,
    'inscriptions' => $registrationErrors,
    'legal' => $legalErrors,
    'base' => $dbErrors,
    'securite' => $passwordErrors,
];
foreach ($tabErrors as $tab => $tabErrorList) {
    if ($tabErrorList) {
        $activeTab = $tab;
        break;
    }
}
?>
<!doctype html>
<html lang="fr">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="robots" content="noindex, nofollow" />
    <title>Adminbtx — Marathon International de Marrakech</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Big+Shoulders+Display:wght@600;700;800;900&family=IBM+Plex+Mono:wght@400;500;600&family=IBM+Plex+Sans:wght@400;500;600&display=swap"
      rel="stylesheet"
    />
    <link rel="stylesheet" href="styles.css" />
    <link rel="stylesheet" href="admin.css" />
    <link rel="icon" type="image/png" sizes="96x96" href="assets/favicon.png" />
  </head>
  <body class="adm">
    <header class="adm-bar">
      <div class="adm-bar-inner">
        <a class="adm-brand" href="index.php">MIM <span>adminbtx</span></a>
        <div class="adm-bar-actions">
          <a class="adm-link" href="index.php">Voir le site</a>
          <?php if ($isAuthenticated): ?>
            <form method="post" class="adm-logout">
              <input type="hidden" name="csrf" value="<?= e($csrf) ?>" />
              <button type="submit" name="action" value="logout" class="adm-link">Déconnexion</button>
            </form>
          <?php endif; ?>
        </div>
      </div>
    </header>

    <main class="adm-main">
      <?php if ($flash): ?>
        <p class="adm-flash adm-flash-<?= e($flash['type']) ?>" role="status"><?= e($flash['text']) ?></p>
      <?php endif; ?>

      <?php if (!$isAuthenticated): ?>
        <section class="adm-card adm-login">
          <p class="tag">Adminbtx</p>
          <h1>Accès réservé</h1>
          <p class="adm-lede">Connectez-vous pour modifier les dates et les heures de départ des épreuves.</p>

          <?php if ($errors): ?>
            <ul class="adm-errors" role="alert">
              <?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?>
            </ul>
          <?php endif; ?>

          <?php if ($firstRun): ?>
            <p class="adm-note">
              Compte initial&nbsp;: identifiant <code>adminbtx</code>, mot de passe <code>adminbtx</code>.
              Changez les deux juste après la première connexion.
            </p>
          <?php endif; ?>

          <form method="post" class="adm-form">
            <input type="hidden" name="csrf" value="<?= e($csrf) ?>" />
            <label class="adm-field">
              <span>Identifiant</span>
              <input type="text" name="username" autocomplete="username" required autofocus />
            </label>
            <label class="adm-field">
              <span>Mot de passe</span>
              <input type="password" name="password" autocomplete="current-password" required />
            </label>
            <button class="btn btn-signal" type="submit" name="action" value="login">Se connecter</button>
          </form>
        </section>
      <?php else: ?>
        <div class="adm-head">
          <p class="tag">Adminbtx</p>
          <h1>Back-office</h1>
          <p class="adm-lede">
            Sept sections : horaires de départ, visuels du site, galerie photo, application d’inscription,
            mentions légales, base de données et sécurité. Chaque modification est appliquée immédiatement
            aux pages publiques, sans republication.
          </p>
        </div>

        <div class="adm-tabs">
          <?php foreach ($admTabKeys as $tabKey): ?>
            <input type="radio" name="adm-tab" id="tab-<?= e($tabKey) ?>"<?= $tabKey === $activeTab ? ' checked' : '' ?> />
          <?php endforeach; ?>
          <div class="adm-tab-bar" role="tablist" aria-label="Sections du back-office">
            <?php foreach ($admTabKeys as $index => $tabKey): ?>
              <label for="tab-<?= e($tabKey) ?>" role="tab"><span><?= e(sprintf('%02d', $index + 1)) ?></span><?= e($admTabs[$tabKey]) ?></label>
            <?php endforeach; ?>
          </div>

          <div class="adm-tab-panels">
          <section class="adm-panel adm-panel-horaires" role="tabpanel">
            <?php if ($errors): ?>
              <ul class="adm-errors" role="alert">
                <?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?>
              </ul>
            <?php endif; ?>
            <?php if ($updatedAt): ?>
              <p class="adm-meta adm-panel-meta">Horaires — dernière modification&nbsp;: <?= e(date('d/m/Y à H:i', $updatedAt)) ?></p>
            <?php endif; ?>

            <form method="post" class="adm-grid">
              <input type="hidden" name="csrf" value="<?= e($csrf) ?>" />

          <section class="adm-card">
            <h2>Général</h2>
            <label class="adm-field">
              <span>Numéro d’édition</span>
              <input type="number" name="edition" min="1" max="999" value="<?= e((string) $form['edition']) ?>" required />
            </label>
            <p class="adm-hint">Affiché dans l’accroche, la frise et le pied de page.</p>
          </section>

          <section class="adm-card">
            <h2>Marathon</h2>
            <div class="adm-row">
              <label class="adm-field">
                <span>Date</span>
                <input type="date" name="marathon_date" value="<?= e($form['marathon_date']) ?>" required />
              </label>
              <label class="adm-field">
                <span>Heure de départ</span>
                <input type="time" name="marathon_start" value="<?= e($form['marathon_start']) ?>" required />
              </label>
            </div>
            <p class="adm-hint">Le compte à rebours de l’accueil suit cette date et cette heure.</p>
          </section>

          <section class="adm-card">
            <h2>Semi-marathon</h2>
            <div class="adm-row">
              <label class="adm-field">
                <span>Date</span>
                <input type="date" name="semi_date" value="<?= e($form['semi_date']) ?>" required />
              </label>
              <label class="adm-field">
                <span>Heure de départ</span>
                <input type="time" name="semi_start" value="<?= e($form['semi_start']) ?>" required />
              </label>
            </div>
          </section>

          <section class="adm-card">
            <h2>Course pour Tous — 5 km</h2>
            <div class="adm-row">
              <label class="adm-field">
                <span>Date</span>
                <input type="date" name="tous_date" value="<?= e($form['tous_date']) ?>" required />
              </label>
              <label class="adm-field">
                <span>Heure de départ</span>
                <input type="time" name="tous_start" value="<?= e($form['tous_start']) ?>" required />
              </label>
            </div>
            <p class="adm-hint">Les journées du programme et du village sont calculées à partir de ces dates.</p>
          </section>

          <div class="adm-actions">
            <button class="btn btn-signal" type="submit" name="action" value="schedule">Enregistrer les horaires</button>
            <a class="btn btn-ghost" href="index.php">Prévisualiser le site</a>
          </div>
        </form>
          </section>

          <section class="adm-panel adm-panel-medias" role="tabpanel">
            <div class="adm-card adm-card-wide" id="medias">
          <h2>Visuels du site</h2>
          <p class="adm-hint">
            Catalogue des images du dossier <code>assets/</code> déjà déposées sur le serveur&nbsp;: affiches,
            photos d’ambiance, logos. Renseignez la référence interne, le texte
            alternatif et la légende (référencement), puis classez chaque média par édition pour le retrouver.
            Le nom « Marathon International de Marrakech » est ajouté automatiquement aux champs SEO s’il est
            absent, et le texte alternatif se génère seul à partir de la référence si vous le laissez vide.
            Les photos publiées dans la page Galerie se gèrent dans l’onglet suivant.
          </p>

          <?php if ($mediaErrors): ?>
            <ul class="adm-errors" role="alert">
              <?php foreach ($mediaErrors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?>
            </ul>
          <?php endif; ?>

          <div class="adm-media-bar">
            <form method="get" action="adminbtx.php#tab-medias" class="adm-media-filter">
              <label class="adm-field">
                <span>Édition</span>
                <select name="media_edition" onchange="this.form.submit()">
                  <option value="all" <?= $mediaFilter === 'all' ? 'selected' : '' ?>>Toutes les éditions</option>
                  <option value="0" <?= $mediaFilter !== 'all' && (int) $mediaFilter === 0 ? 'selected' : '' ?>>Non classés</option>
                  <?php foreach ($mediaEditions as $editionNumber): ?>
                    <?php if ($editionNumber === 0) { continue; } ?>
                    <option value="<?= (int) $editionNumber ?>" <?= $mediaFilter !== 'all' && (int) $mediaFilter === $editionNumber ? 'selected' : '' ?>>Édition <?= (int) $editionNumber ?></option>
                  <?php endforeach; ?>
                </select>
              </label>
            </form>

            <form method="post" class="adm-media-filter">
              <input type="hidden" name="csrf" value="<?= e($csrf) ?>" />
              <label class="adm-field">
                <span>Quota de stockage (Mo)</span>
                <input type="number" name="quota_mb" min="1" max="102400" value="<?= e((string) $mediaData['quota_mb']) ?>" required />
              </label>
              <button class="btn btn-ink" type="submit" name="action" value="media_quota">Mettre à jour</button>
            </form>
          </div>

          <div
            class="adm-meter"
            role="img"
            aria-label="Espace disque utilisé par la galerie : <?= $mediaPercent ?> %"
          >
            <div class="adm-meter-label">
              <span>Espace galerie utilisé</span>
              <span><?= e(mim_format_bytes($mediaTotal)) ?> / <?= e((string) $mediaData['quota_mb']) ?> Mo (<?= $mediaPercent ?> %)</span>
            </div>
            <div class="adm-meter-track">
              <div
                class="adm-meter-bar<?= $mediaPercent >= 100 ? ' is-full' : ($mediaPercent >= 80 ? ' is-warn' : '') ?>"
                style="width: <?= $mediaPercentCapped ?>%"
              ></div>
            </div>
          </div>

          <p class="adm-media-meta">
            <?= $mediaCount ?> média<?= $mediaCount > 1 ? 's' : '' ?> affiché<?= $mediaCount > 1 ? 's' : '' ?>
            · <?= e(mim_format_bytes($mediaUsed)) ?> pour cette sélection
          </p>

          <?php if (!$mediaList): ?>
            <p class="adm-hint">Aucun média dans cette sélection.</p>
          <?php else: ?>
            <div class="adm-media-grid">
              <?php foreach ($mediaList as $media): ?>
                <form method="post" class="adm-media-card">
                  <input type="hidden" name="csrf" value="<?= e($csrf) ?>" />
                  <input type="hidden" name="file" value="<?= e($media['file']) ?>" />
                  <input type="hidden" name="media_edition" value="<?= e((string) $mediaFilter) ?>" />
                  <img class="adm-media-thumb" src="<?= e($media['file']) ?>" alt="" loading="lazy" />
                  <div class="adm-media-top">
                    <p class="adm-media-name"><?= e(basename($media['file'])) ?></p>
                    <span class="adm-media-size"><?= e(mim_format_bytes($media['size'])) ?></span>
                  </div>
                  <label class="adm-field">
                    <span>Référence interne</span>
                    <input type="text" name="ref" maxlength="255" value="<?= e($media['ref']) ?>" placeholder="MIM-2027-001" />
                  </label>
                  <label class="adm-field">
                    <span>Texte alternatif (SEO)</span>
                    <input type="text" name="alt" maxlength="255" value="<?= e($media['alt']) ?>" placeholder="Départ du marathon, avenue de la Menara…" />
                  </label>
                  <label class="adm-field">
                    <span>Légende (SEO)</span>
                    <textarea name="legende" maxlength="500" placeholder="Description affichée avec l’image."><?= e($media['legende']) ?></textarea>
                  </label>
                  <label class="adm-field">
                    <span>Édition (0 = non classé)</span>
                    <input type="number" name="edition" min="0" max="999" value="<?= e((string) $media['edition']) ?>" required />
                  </label>
                  <button class="btn btn-signal adm-media-save" type="submit" name="action" value="media_save">Enregistrer</button>
                </form>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
            </div>
          </section>

          <section class="adm-panel adm-panel-galerie" role="tabpanel">
            <div class="adm-card adm-card-wide" id="galerie">
              <h2>Galerie photo</h2>
              <p class="adm-hint">
                Les photos importées ici alimentent la page publique
                <a href="galerie.php">Galerie</a>. Chaque fichier est contrôlé — JPEG, PNG, WebP ou AVIF,
                640&nbsp;×&nbsp;420 pixels minimum, 12&nbsp;000 pixels maximum sur le plus grand côté — puis
                ré-encodé en deux tailles (1600&nbsp;px à l’affichage, 480&nbsp;px pour la miniature). Le
                ré-encodage supprime les métadonnées EXIF, donc les coordonnées GPS de l’appareil, et le nom du
                fichier d’origine n’est jamais conservé.
              </p>

              <?php if ($galleryErrors): ?>
                <ul class="adm-errors" role="alert">
                  <?php foreach ($galleryErrors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?>
                </ul>
              <?php endif; ?>

              <form method="post" enctype="multipart/form-data" class="adm-gallery-upload">
                <input type="hidden" name="csrf" value="<?= e($csrf) ?>" />
                <input type="hidden" name="gallery_edition" value="<?= e($galleryFilter) ?>" />
                <div class="adm-row">
                  <label class="adm-field">
                    <span>Édition de la prise de vue (0 = à classer)</span>
                    <input type="number" name="edition" min="0" max="999"
                      value="<?= e((string) ($galleryFilterEdition ?? $schedule['edition'])) ?>" required />
                  </label>
                  <label class="adm-field">
                    <span>Référence interne (facultatif)</span>
                    <input type="text" name="ref" maxlength="255" placeholder="MIM-2027-D1-012" />
                  </label>
                </div>
                <label class="adm-field">
                  <span>Photos à importer</span>
                  <input type="file" name="photos[]" accept="image/jpeg,image/png,image/webp,image/avif" multiple required />
                </label>
                <p class="adm-hint">
                  Limite du serveur PHP&nbsp;: <?= e(mim_format_bytes($galleryUploadLimit)) ?> par fichier
                  (<code>upload_max_filesize</code> = <?= e((string) ini_get('upload_max_filesize')) ?>,
                  <code>post_max_size</code> = <?= e((string) ini_get('post_max_size')) ?>). Réduisez ces valeurs
                  dans votre panneau d’hébergement pour importer des clichés plus lourds&nbsp;; ils seront de
                  toute façon allégés à l’import.
                </p>
                <div class="adm-actions">
                  <button class="btn btn-signal" type="submit" name="action" value="gallery_upload">Importer les photos</button>
                  <a class="btn btn-ghost" href="galerie.php">Voir la page Galerie</a>
                </div>
              </form>

              <div class="adm-media-bar">
                <form method="get" action="adminbtx.php#tab-galerie" class="adm-media-filter">
                  <label class="adm-field">
                    <span>Album</span>
                    <select name="gallery_edition" onchange="this.form.submit()">
                      <option value="all" <?= $galleryFilter === 'all' ? 'selected' : '' ?>>Toutes les éditions</option>
                      <option value="0" <?= $galleryFilter === '0' ? 'selected' : '' ?>>À classer</option>
                      <?php foreach ($galleryEditions as $editionNumber): ?>
                        <?php if ($editionNumber === 0) { continue; } ?>
                        <option value="<?= (int) $editionNumber ?>" <?= $galleryFilter === (string) $editionNumber ? 'selected' : '' ?>>Édition <?= (int) $editionNumber ?></option>
                      <?php endforeach; ?>
                    </select>
                  </label>
                </form>

                <form method="post" class="adm-media-filter">
                  <input type="hidden" name="csrf" value="<?= e($csrf) ?>" />
                  <label class="adm-field">
                    <span>Quota galerie (Mo)</span>
                    <input type="number" name="quota_mb" min="1" max="102400" value="<?= e((string) $galleryData['quota_mb']) ?>" required />
                  </label>
                  <button class="btn btn-ink" type="submit" name="action" value="gallery_quota">Mettre à jour</button>
                </form>
              </div>

              <div
                class="adm-meter"
                role="img"
                aria-label="Espace occupé par la galerie photo : <?= $galleryPercent ?> %"
              >
                <div class="adm-meter-label">
                  <span>Place prise par les photos</span>
                  <span><?= e(mim_format_bytes($galleryUsed)) ?> / <?= e((string) $galleryData['quota_mb']) ?> Mo (<?= $galleryPercent ?> %)</span>
                </div>
                <div class="adm-meter-track">
                  <div
                    class="adm-meter-bar<?= $galleryPercent >= 100 ? ' is-full' : ($galleryPercent >= 80 ? ' is-warn' : '') ?>"
                    style="width: <?= $galleryPercentCapped ?>%"
                  ></div>
                </div>
              </div>

              <p class="adm-media-meta">
                <?= $galleryCount ?> photo<?= $galleryCount > 1 ? 's' : '' ?> dans cet album
                · <?= $galleryPublished ?> publiée<?= $galleryPublished > 1 ? 's' : '' ?> sur
                <?= count($galleryData['photos']) ?> au total
              </p>

              <?php if ($galleryMissing): ?>
                <p class="adm-warn" role="status">
                  <?= $galleryMissing ?> fiche(s) correspondent à un fichier absent de
                  <code>assets/galerie/</code>&nbsp;: supprimez-les ou réenvoyez l’image.
                </p>
              <?php endif; ?>

              <?php if (!$galleryList): ?>
                <p class="adm-hint">Aucune photo dans cet album&nbsp;: importez-en ci-dessus pour ouvrir la galerie.</p>
              <?php else: ?>
                <div class="adm-gallery-grid">
                  <?php foreach ($galleryList as $photo): ?>
                    <?php $absent = !is_file(MIM_ROOT . '/' . $photo['file']); ?>
                    <div class="adm-gallery-card<?= $absent ? ' is-missing' : '' ?><?= $photo['published'] ? '' : ' is-draft' ?>">
                      <form method="post" class="adm-gallery-form">
                        <input type="hidden" name="csrf" value="<?= e($csrf) ?>" />
                        <input type="hidden" name="file" value="<?= e($photo['file']) ?>" />
                        <input type="hidden" name="gallery_edition" value="<?= e($galleryFilter) ?>" />

                        <?php if ($photo['thumb'] !== '' && !$absent): ?>
                          <img class="adm-media-thumb" src="<?= e($photo['thumb']) ?>" alt="" loading="lazy" />
                        <?php else: ?>
                          <p class="adm-gallery-absent">Fichier manquant</p>
                        <?php endif; ?>

                        <div class="adm-media-top">
                          <p class="adm-media-name"><?= e(basename($photo['file'])) ?></p>
                          <span class="adm-media-size"><?= e(mim_format_bytes($photo['size'] + $photo['thumb_size'])) ?></span>
                        </div>
                        <p class="adm-gallery-dims"><?= (int) $photo['width'] ?> × <?= (int) $photo['height'] ?> px</p>

                        <label class="adm-check">
                          <input type="checkbox" name="published" value="1" <?= $photo['published'] ? 'checked' : '' ?> />
                          <span>Publiée sur la page Galerie</span>
                        </label>

                        <label class="adm-field">
                          <span>Référence interne</span>
                          <input type="text" name="ref" maxlength="255" value="<?= e($photo['ref']) ?>" placeholder="MIM-2027-D1-012" />
                        </label>
                        <div class="adm-row">
                          <label class="adm-field">
                            <span>Édition</span>
                            <input type="number" name="edition" min="0" max="999" value="<?= e((string) $photo['edition']) ?>" required />
                          </label>
                          <label class="adm-field">
                            <span>Prise de vue</span>
                            <input type="date" name="taken" value="<?= e($photo['taken']) ?>" />
                          </label>
                        </div>
                        <label class="adm-field">
                          <span>Crédit photographique</span>
                          <input type="text" name="credit" maxlength="120" value="<?= e($photo['credit']) ?>" placeholder="© Nom du photographe" />
                        </label>

                        <label class="adm-field">
                          <span>Texte alternatif — français</span>
                          <input type="text" name="alt[fr]" maxlength="255" value="<?= e($photo['alt']['fr']) ?>" placeholder="Départ du marathon, avenue de la Menara…" />
                        </label>
                        <label class="adm-field">
                          <span>Légende — français</span>
                          <textarea name="caption[fr]" maxlength="500" placeholder="Description affichée sous la photo."><?= e($photo['caption']['fr']) ?></textarea>
                        </label>

                        <?php foreach (MIM_LOCALES as $locale => $meta): ?>
                          <?php if ($locale === 'fr') { continue; } ?>
                          <details class="adm-gallery-lang">
                            <summary><?= e($meta['native']) ?> (<?= e($locale) ?>)</summary>
                            <label class="adm-field">
                              <span>Texte alternatif</span>
                              <input type="text" name="alt[<?= e($locale) ?>]" maxlength="255" value="<?= e($photo['alt'][$locale]) ?>" />
                            </label>
                            <label class="adm-field">
                              <span>Légende</span>
                              <textarea name="caption[<?= e($locale) ?>]" maxlength="500"><?= e($photo['caption'][$locale]) ?></textarea>
                            </label>
                          </details>
                        <?php endforeach; ?>

                        <button class="btn btn-signal adm-media-save" type="submit" name="action" value="gallery_save">Enregistrer</button>
                      </form>

                      <form method="post" class="adm-gallery-delete" onsubmit="return confirm('Supprimer définitivement cette photo et sa miniature ?');">
                        <input type="hidden" name="csrf" value="<?= e($csrf) ?>" />
                        <input type="hidden" name="file" value="<?= e($photo['file']) ?>" />
                        <input type="hidden" name="gallery_edition" value="<?= e($galleryFilter) ?>" />
                        <button class="adm-link-danger" type="submit" name="action" value="gallery_delete">Supprimer la photo</button>
                      </form>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>
          </section>

          <section class="adm-panel adm-panel-inscriptions" role="tabpanel">
            <div class="adm-card adm-card-wide">
              <h2>Application d’inscription</h2>
              <p class="adm-hint">
                Lien de l’application d’inscription en ligne — par ex.
                <code>https://runsign.vercel.app/</code>. Elle est intégrée directement dans la page publique
                <a href="inscriptions.php">Inscriptions</a>, sans quitter le site. Laissez vide pour revenir
                au site officiel.
              </p>

              <?php if ($registrationErrors): ?>
                <ul class="adm-errors" role="alert">
                  <?php foreach ($registrationErrors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?>
                </ul>
              <?php endif; ?>

              <form method="post" class="adm-form adm-form-registration">
                <input type="hidden" name="csrf" value="<?= e($csrf) ?>" />
                <label class="adm-field">
                  <span>Lien de l’application</span>
                  <input
                    type="url"
                    name="registration_url"
                    maxlength="500"
                    value="<?= e($registrationUrl) ?>"
                    placeholder="https://runsign.vercel.app/"
                  />
                </label>
                <div class="adm-actions">
                  <button class="btn btn-signal" type="submit" name="action" value="registration">Enregistrer le lien</button>
                  <?php if ($registrationUrl !== ''): ?>
                    <a class="btn btn-ghost" href="<?= e($registrationUrl) ?>" target="_blank" rel="noopener noreferrer">Ouvrir l’application</a>
                    <a class="btn btn-ghost" href="inscriptions.php">Voir la page Inscriptions</a>
                  <?php endif; ?>
                </div>
              </form>
            </div>
          </section>

          <section class="adm-panel adm-panel-legal" role="tabpanel">
            <div class="adm-card adm-card-wide" id="legal">
          <h2>Mentions légales, e-mails et tarifs</h2>
          <p class="adm-hint">
            Ces valeurs alimentent le pied de page, l’encart tarifaire de la page Inscriptions et les pages
            Conditions, Confidentialité et Remboursement. Rien n’est inventé&nbsp;: un champ laissé vide
            s’affiche « à préciser » sur le site (les contre-valeur EUR et MAD vides retirent simplement
            la devise du sélecteur). Les passerelles de paiement exigent une
            entité facturatrice identifiable et des e-mails sur le domaine du site.
          </p>

          <?php if ($legalErrors): ?>
            <ul class="adm-errors" role="alert">
              <?php foreach ($legalErrors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?>
            </ul>
          <?php endif; ?>

          <?php if (!mim_entity_ready()): ?>
            <p class="adm-warn" role="status">
              Identité juridique incomplète&nbsp;: nom de la structure, numéro EIN et adresse postale sont
              requis pour afficher les mentions légales du pied de page.
            </p>
          <?php endif; ?>

          <form method="post" class="adm-grid">
            <input type="hidden" name="csrf" value="<?= e($csrf) ?>" />

            <section class="adm-card">
              <h2>Structure qui facture</h2>
              <p class="adm-hint">La personne morale qui encaisse les droits d’inscription (billetterie).</p>
              <label class="adm-field">
                <span>Nom légal (LLC)</span>
                <input type="text" name="entity_name" maxlength="120" value="<?= e($legal['entity']['name']) ?>" placeholder="Ex. Atlas Race Events LLC" />
              </label>
              <label class="adm-field">
                <span>Numéro EIN</span>
                <input type="text" name="entity_ein" maxlength="12" value="<?= e($legal['entity']['ein']) ?>" placeholder="12-3456789" pattern="\d{2}-\d{7}" />
              </label>
              <label class="adm-field">
                <span>Adresse postale</span>
                <input type="text" name="entity_street" maxlength="120" value="<?= e($legal['entity']['street']) ?>" placeholder="1234 Main St, Suite 500" />
              </label>
              <div class="adm-row">
                <label class="adm-field">
                  <span>Ville</span>
                  <input type="text" name="entity_city" maxlength="120" value="<?= e($legal['entity']['city']) ?>" />
                </label>
                <label class="adm-field">
                  <span>État</span>
                  <input type="text" name="entity_state" maxlength="24" value="<?= e($legal['entity']['state']) ?>" placeholder="NY" />
                </label>
                <label class="adm-field">
                  <span>ZIP</span>
                  <input type="text" name="entity_zip" maxlength="10" value="<?= e($legal['entity']['zip']) ?>" placeholder="12345" pattern="\d{5}(-\d{4})?" />
                </label>
              </div>
              <label class="adm-field">
                <span>Pays</span>
                <input type="text" name="entity_country" maxlength="60" value="<?= e($legal['entity']['country']) ?>" placeholder="United States" />
              </label>
            </section>

            <section class="adm-card">
              <h2>Association partenaire</h2>
              <p class="adm-hint">
                Nom et rôle affichés à côté de la structure qui facture, pour distinguer l’organisation
                sportive de la relation commerciale.
              </p>
              <label class="adm-field">
                <span>Nom de l’association</span>
                <input type="text" name="association_name" maxlength="120" value="<?= e($legal['association']['name']) ?>" />
              </label>
              <label class="adm-field">
                <span>Rôle</span>
                <input type="text" name="association_role" maxlength="300" value="<?= e($legal['association']['role']) ?>" placeholder="Organisation technique et sportive de l’épreuve" />
              </label>
            </section>

            <section class="adm-card">
              <h2>E-mails officiels</h2>
              <p class="adm-hint">
                Utilisez le domaine du site. Aucune adresse générique (gmail, hotmail…) ne doit rester
                visible&nbsp;: les passerelles de paiement les traitent comme un signal de fraude.
                Tant qu'un champ est vide, le site affiche «&nbsp;à préciser&nbsp;» à la place de l'adresse&nbsp;;
                aucune adresse n'est inventée.
              </p>
              <label class="adm-field">
                <span>Contact général</span>
                <input type="email" name="email_contact" maxlength="120" value="<?= e($legal['emails']['contact']) ?>" placeholder="contact@marathonmarrakech.ma" />
              </label>
              <label class="adm-field">
                <span>Assistance inscription et paiement</span>
                <input type="email" name="email_support" maxlength="120" value="<?= e($legal['emails']['support']) ?>" placeholder="support@marathonmarrakech.ma" />
              </label>
              <label class="adm-field">
                <span>Vie privée et demandes légales</span>
                <input type="email" name="email_legal" maxlength="120" value="<?= e($legal['emails']['legal']) ?>" placeholder="legal@marathonmarrakech.ma" />
              </label>
            </section>

            <section class="adm-card">
              <h2>Tarifs de référence</h2>
              <p class="adm-hint">
                Prix affichés sur la page Inscriptions avant le formulaire, dans la devise réellement
                facturée. Les contre-valeurs MAD et EUR dépendent du taux appliqué par la passerelle au
                moment du paiement&nbsp;: précisez la source et le moment de la conversion.
              </p>
              <div class="adm-row">
                <label class="adm-field">
                  <span>Devise facturée</span>
                  <input type="text" name="pricing_billing" maxlength="3" value="<?= e($legal['pricing']['billing']) ?>" placeholder="USD" required />
                </label>
                <label class="adm-field">
                  <span>Marathon 42,195 km</span>
                  <input type="number" name="price_marathon" min="0" step="0.01" value="<?= e($legal['pricing']['marathon']) ?>" placeholder="45" />
                </label>
              </div>
              <div class="adm-row">
                <label class="adm-field">
                  <span>Semi-marathon 21,097 km</span>
                  <input type="number" name="price_semi" min="0" step="0.01" value="<?= e($legal['pricing']['semi']) ?>" placeholder="30" />
                </label>
                <label class="adm-field">
                  <span>Course pour Tous 5 km</span>
                  <input type="number" name="price_tous" min="0" step="0.01" value="<?= e($legal['pricing']['tous']) ?>" placeholder="15" />
                </label>
              </div>
              <label class="adm-field">
                <span>Source du taux de change</span>
                <input type="text" name="fx_source" maxlength="300" value="<?= e($legal['pricing']['fx_source']) ?>" placeholder="Ex. taux Mastercard publié la veille du paiement" />
              </label>
              <label class="adm-field">
                <span>Moment de la conversion</span>
                <input type="text" name="fx_moment" maxlength="300" value="<?= e($legal['pricing']['fx_moment']) ?>" placeholder="Ex. conversion appliquée par votre banque à la date d’autorisation" />
              </label>

              <h3 class="adm-sub">Devise choisie par le participant</h3>
              <p class="adm-hint">
                Sur la page Inscriptions, le visiteur peut basculer l’affichage entre <?= e($legal['pricing']['billing']) ?>, EUR et MAD.
                Ces montants ne sont jamais calculés par le site&nbsp;: saisis-les au tarif que tu pratiques,
                ou laisse la ligne vide pour ne pas proposer la devise. Le débit réel reste celui de
                l’application d’inscription en <?= e($legal['pricing']['billing']) ?>.
              </p>
              <div class="adm-row">
                <label class="adm-field">
                  <span>Marathon — EUR</span>
                  <input type="number" name="price_eur_marathon" min="0" step="0.01" value="<?= e($legal['pricing']['eur_marathon']) ?>" />
                </label>
                <label class="adm-field">
                  <span>Semi — EUR</span>
                  <input type="number" name="price_eur_semi" min="0" step="0.01" value="<?= e($legal['pricing']['eur_semi']) ?>" />
                </label>
                <label class="adm-field">
                  <span>5 km — EUR</span>
                  <input type="number" name="price_eur_tous" min="0" step="0.01" value="<?= e($legal['pricing']['eur_tous']) ?>" />
                </label>
              </div>
              <div class="adm-row">
                <label class="adm-field">
                  <span>Marathon — MAD</span>
                  <input type="number" name="price_mad_marathon" min="0" step="0.01" value="<?= e($legal['pricing']['mad_marathon']) ?>" />
                </label>
                <label class="adm-field">
                  <span>Semi — MAD</span>
                  <input type="number" name="price_mad_semi" min="0" step="0.01" value="<?= e($legal['pricing']['mad_semi']) ?>" />
                </label>
                <label class="adm-field">
                  <span>5 km — MAD</span>
                  <input type="number" name="price_mad_tous" min="0" step="0.01" value="<?= e($legal['pricing']['mad_tous']) ?>" />
                </label>
              </div>
            </section>

            <section class="adm-card">
              <h2>Politiques et délais</h2>
              <p class="adm-hint">
                Dates de dernière mise à jour des trois pages légales, et barèmes de remboursement / report
                cités dans la politique de remboursement.
              </p>
              <div class="adm-row">
                <label class="adm-field">
                  <span>Conditions — mise à jour</span>
                  <input type="date" name="terms_updated" value="<?= e($legal['policies']['terms_updated']) ?>" />
                </label>
                <label class="adm-field">
                  <span>Confidentialité — mise à jour</span>
                  <input type="date" name="privacy_updated" value="<?= e($legal['policies']['privacy_updated']) ?>" />
                </label>
              </div>
              <div class="adm-row">
                <label class="adm-field">
                  <span>Remboursement — mise à jour</span>
                  <input type="date" name="refund_updated" value="<?= e($legal['policies']['refund_updated']) ?>" />
                </label>
                <label class="adm-field">
                  <span>Limite de remboursement</span>
                  <input type="date" name="refund_deadline" value="<?= e($legal['policies']['refund_deadline']) ?>" />
                </label>
              </div>
              <div class="adm-row">
                <label class="adm-field">
                  <span>Limite de report d’édition</span>
                  <input type="date" name="deferral_deadline" value="<?= e($legal['policies']['deferral_deadline']) ?>" />
                </label>
                <label class="adm-field">
                  <span>Frais de report</span>
                  <input type="text" name="deferral_fee" maxlength="60" value="<?= e($legal['policies']['deferral_fee']) ?>" placeholder="Ex. 15 USD ou gratuit" />
                </label>
              </div>
            </section>

            <div class="adm-actions">
              <button class="btn btn-signal" type="submit" name="action" value="legal">Enregistrer les mentions légales</button>
              <a class="btn btn-ghost" href="conditions.php">Conditions</a>
              <a class="btn btn-ghost" href="confidentialite.php">Confidentialité</a>
              <a class="btn btn-ghost" href="remboursement.php">Remboursement</a>
            </div>
          </form>
            </div>
          </section>

          <section class="adm-panel adm-panel-base" role="tabpanel">
            <div class="adm-card adm-card-wide">
              <h2>Base de données MySQL</h2>
              <p class="adm-hint">
                Réglages lus dans les variables d’environnement <code>MIM_DB_*</code> quand elles existent,
                sinon dans <code>config/database.local.php</code>, sinon dans les valeurs par défaut du code.
                Si la base ne répond pas, le site continue de fonctionner avec les fichiers <code>data/*.json</code>.
              </p>

              <?php if ($dbErrors): ?>
                <ul class="adm-errors" role="alert">
                  <?php foreach ($dbErrors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?>
                </ul>
              <?php endif; ?>

              <?php if ($dbTest): ?>
                <p class="adm-flash" role="status">
                  Connexion réussie à <code><?= e($dbTest['database']) ?></code> sur
                  <code><?= e($dbTest['host']) ?>:<?= (int) $dbTest['port'] ?></code> —
                  MySQL <?= e($dbTest['version']) ?>, <?= (int) $dbTest['tables'] ?> table(s) dans la base<?= $dbTest['missing'] ? ', manquantes : ' . e(implode(', ', $dbTest['missing'])) : ', schéma complet' ?>.
                </p>
              <?php endif; ?>

              <?php if ($dbSource === 'env'): ?>
                <p class="adm-warn">
                  Les variables d’environnement <code>MIM_DB_*</code> sont définies sur ce serveur et restent
                  prioritaires : le fichier enregistré ici ne sera pas utilisé tant qu’elles existent.
                </p>
              <?php endif; ?>

              <dl class="adm-db-status">
                <div>
                  <dt>État</dt>
                  <dd>
                    <?php if ($dbBackend === 'mysql'): ?>
                      Connectée<?= $dbVersion !== '' ? ' — MySQL ' . e($dbVersion) : '' ?>
                    <?php else: ?>
                      Hors ligne — fichiers JSON utilisés
                    <?php endif; ?>
                  </dd>
                </div>
                <div>
                  <dt>Hôte</dt>
                  <dd><code><?= e($dbConfig['host']) ?>:<?= (int) $dbConfig['port'] ?></code></dd>
                </div>
                <div>
                  <dt>Base</dt>
                  <dd><code><?= e($dbConfig['name']) ?></code></dd>
                </div>
                <div>
                  <dt>Utilisateur</dt>
                  <dd><code><?= e($dbConfig['user']) ?></code> · mot de passe <?= $dbConfig['pass'] !== '' ? 'renseigné' : 'vide' ?></dd>
                </div>
                <div>
                  <dt>Origine</dt>
                  <dd><?= e($dbSourceLabel) ?></dd>
                </div>
              </dl>

              <?php if ($dbRows): ?>
                <table class="adm-db-tables">
                  <caption>Contenu des tables MIM</caption>
                  <thead>
                    <tr><th scope="col">Table</th><th scope="col">Lignes</th></tr>
                  </thead>
                  <tbody>
                    <?php foreach ($dbRows as $dbTable => $dbRowCount): ?>
                      <tr><th scope="row"><code><?= e($dbTable) ?></code></th><td><?= (int) $dbRowCount ?></td></tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              <?php elseif ($dbError !== ''): ?>
                <p class="adm-hint">Dernière erreur MySQL : <code><?= e($dbError) ?></code></p>
              <?php endif; ?>

              <form method="post" class="adm-form adm-db-form">
                <input type="hidden" name="csrf" value="<?= e($csrf) ?>" />
                <div class="adm-row">
                  <label class="adm-field">
                    <span>Hôte</span>
                    <input type="text" name="db_host" value="<?= e($dbConfig['host']) ?>" maxlength="255" required />
                  </label>
                  <label class="adm-field">
                    <span>Port</span>
                    <input type="number" name="db_port" value="<?= (int) $dbConfig['port'] ?>" min="1" max="65535" required />
                  </label>
                </div>
                <div class="adm-row">
                  <label class="adm-field">
                    <span>Base de données</span>
                    <input type="text" name="db_name" value="<?= e($dbConfig['name']) ?>" maxlength="64" required />
                  </label>
                  <label class="adm-field">
                    <span>Utilisateur</span>
                    <input type="text" name="db_user" value="<?= e($dbConfig['user']) ?>" maxlength="64" autocomplete="off" required />
                  </label>
                </div>
                <label class="adm-field">
                  <span>Mot de passe</span>
                  <input type="password" name="db_pass" autocomplete="new-password" maxlength="255" placeholder="<?= $dbConfig['pass'] !== '' ? 'Laisser vide pour conserver' : 'Aucun mot de passe enregistré' ?>" />
                </label>
                <?php if ($dbConfig['pass'] !== ''): ?>
                  <label class="adm-check">
                    <input type="checkbox" name="db_pass_clear" value="1" />
                    <span>Supprimer le mot de passe enregistré</span>
                  </label>
                <?php endif; ?>

                <div class="adm-actions">
                  <button class="btn btn-ghost" type="submit" name="action" value="db_test">Tester la connexion</button>
                  <button class="btn btn-ink" type="submit" name="action" value="db_save">Enregistrer</button>
                </div>

                <p class="adm-sub">Installation</p>
                <p class="adm-hint">Les réglages saisis sont enregistrés avant l’opération. Créer la base suppose que le compte MySQL ait le droit <code>CREATE DATABASE</code> ; sinon, créez-la chez l’hébergeur puis importez <code>sql/schema.sql</code>.</p>
                <div class="adm-actions">
                  <button class="btn btn-ghost" type="submit" name="action" value="db_create">Créer la base et les tables</button>
                  <button class="btn btn-ghost" type="submit" name="action" value="db_import">Recopier data/ dans la base</button>
                </div>
              </form>

              <p class="adm-foot">
                <code>config/database.local.php</code> reçoit les valeurs saisies et reste protégé par
                <code>config/.htaccess</code>. Installation en ligne de commande&nbsp;:
                <a class="adm-link" href="db-install.php">db-install.php</a>.
              </p>
            </div>
          </section>

          <section class="adm-panel adm-panel-securite" role="tabpanel">
            <div class="adm-card adm-card-wide">
          <h2>Identifiant et mot de passe</h2>
          <p class="adm-hint">Le compte adminbtx est unique. L’identifiant accepte lettres, chiffres, point, tiret et souligné ; le mot de passe demande au moins 10 caractères. Laissez les deux champs de mot de passe vides pour ne changer que l’identifiant.</p>

          <?php if ($passwordErrors): ?>
            <ul class="adm-errors" role="alert">
              <?php foreach ($passwordErrors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?>
            </ul>
          <?php endif; ?>

          <form method="post" class="adm-form adm-form-password">
            <input type="hidden" name="csrf" value="<?= e($csrf) ?>" />
            <label class="adm-field">
              <span>Mot de passe actuel</span>
              <input type="password" name="current_password" autocomplete="current-password" required />
            </label>
            <label class="adm-field">
              <span>Identifiant</span>
              <input
                type="text"
                name="login"
                value="<?= e((string) ($credentials['login'] ?? MIM_ADMIN_DEFAULT_LOGIN)) ?>"
                autocomplete="username"
                minlength="3"
                maxlength="64"
                pattern="[A-Za-z0-9._\-]{3,64}"
                required
              />
            </label>
            <div class="adm-row">
              <label class="adm-field">
                <span>Nouveau mot de passe</span>
                <input type="password" name="new_password" autocomplete="new-password" minlength="10" placeholder="Laisser vide pour conserver" />
              </label>
              <label class="adm-field">
                <span>Confirmation</span>
                <input type="password" name="confirm_password" autocomplete="new-password" minlength="10" />
              </label>
            </div>
            <button class="btn btn-ink" type="submit" name="action" value="credentials">Enregistrer le compte</button>
          </form>
            </div>
          </section>
          </div>
        </div>

        <p class="adm-foot">
          <?php if (mim_backend() === 'mysql'): ?>
            Configuration enregistrée dans la base <code><?= e(mim_db_config()['name']) ?></code>,
            avec une copie dans <code>data/</code> pour que le site continue de fonctionner
            si la base devient injoignable.
          <?php else: ?>
            Aucune base MySQL joignable : la configuration est lue et écrite dans
            <code>data/*.json</code>.
          <?php endif; ?>
          Les fichiers <code>data/schedule.json</code>, <code>data/media.json</code>,
          <code>data/site.json</code> et <code>data/admin.json</code> doivent rester hors d’accès public
          (protégés par <code>data/.htaccess</code> sous Apache).
          <a class="adm-link" href="db-install.php">Installer la base</a>
        </p>
      <?php endif; ?>
    </main>

    <script>
      (function () {
        var tabs = document.querySelectorAll('.adm-tabs > input[name="adm-tab"]');
        if (!tabs.length) {
          return;
        }
        function applyHash() {
          var id = window.location.hash.slice(1);
          tabs.forEach(function (tab) {
            tab.checked = tab.id === id;
          });
        }
        window.addEventListener('hashchange', applyHash);
        tabs.forEach(function (tab) {
          tab.addEventListener('change', function () {
            if (tab.checked) {
              history.replaceState(null, '', '#' + tab.id);
            }
          });
        });
        if (window.location.hash) {
          applyHash();
        }
      })();
    </script>
  </body>
</html>
