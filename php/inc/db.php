<?php
declare(strict_types=1);

/**
 * Stockage de la configuration : MySQL quand la base répond et qu'elle est installée,
 * fichiers data/*.json sinon.
 *
 * Le site public et le back-office ne voient qu'une seule interface, mim_store_read() et
 * mim_store_write(), qui rend exactement la forme des anciens fichiers JSON. Un hébergement
 * sans base, ou une base tombée, continue donc de fonctionner avec les fichiers ; une base
 * installée devient la source réelle dès que le schéma est en place.
 */

/**
 * @param bool $reload Relit config/database.php, pour appliquer un réglage écrit à l'instant.
 * @return array{host: string, port: int, name: string, user: string, pass: string, charset: string}
 */
function mim_db_config(bool $reload = false): array
{
    static $config = null;

    if ($config !== null && !$reload) {
        return $config;
    }

    $file = MIM_ROOT . '/config/database.php';
    $config = is_file($file) ? (array) require $file : [];
    $config += ['host' => '127.0.0.1', 'port' => 3306, 'name' => 'mim_marrakech', 'user' => 'root', 'pass' => '', 'charset' => 'utf8mb4'];
    $config['port'] = (int) $config['port'];

    return $config;
}

/**
 * Connexion partagée, ou null si MySQL est injoignable. L'échec est mémorisé pour que
 * la page ne retente pas la connexion à chaque requête.
 */
function mim_db(): ?PDO
{
    if (array_key_exists('mim_db_pdo', $GLOBALS)) {
        return $GLOBALS['mim_db_pdo'];
    }

    try {
        $GLOBALS['mim_db_pdo'] = mim_db_connect(mim_db_config());
    } catch (PDOException $exception) {
        $GLOBALS['mim_db_pdo'] = null;
        $GLOBALS['mim_db_error'] = $exception->getMessage();
    }

    return $GLOBALS['mim_db_pdo'];
}

/** Oublie la connexion et les réglages en cache : à appeler après une modification de config. */
function mim_db_reset(): void
{
    mim_db_config(true);
    unset($GLOBALS['mim_db_pdo'], $GLOBALS['mim_backend'], $GLOBALS['mim_db_error']);
}

/** Ouvre une connexion avec les paramètres fournis, sans passer par le cache. */
function mim_db_connect(array $config): PDO
{
    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=%s',
        (string) $config['host'],
        (int) $config['port'],
        (string) $config['name'],
        (string) ($config['charset'] ?? 'utf8mb4')
    );

    return new PDO($dsn, (string) $config['user'], (string) $config['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 3,
    ]);
}

/** Fichier écrit par l'onglet « Base de données » d'adminbtx, hors du dépôt. */
function mim_db_config_file(): string
{
    return MIM_ROOT . '/config/database.local.php';
}

/** Origine des réglages actifs : environnement, fichier local, ou défauts du code. */
function mim_db_config_source(): string
{
    foreach (['MIM_DB_HOST', 'MIM_DB_PORT', 'MIM_DB_NAME', 'MIM_DB_USER', 'MIM_DB_PASS'] as $key) {
        $value = getenv($key);

        if ($value !== false && $value !== '') {
            return 'env';
        }
    }

    return is_file(mim_db_config_file()) ? 'file' : 'default';
}

/**
 * Enregistre les réglages MySQL dans config/database.local.php.
 *
 * Le fichier ne contient que des valeurs passées à var_export() : pas de code saisi.
 * Les variables d'environnement restent prioritaires à la lecture.
 */
function mim_db_save_config(array $config): bool
{
    $payload = [
        'host' => (string) $config['host'],
        'port' => (int) $config['port'],
        'name' => (string) $config['name'],
        'user' => (string) $config['user'],
        'pass' => (string) $config['pass'],
    ];

    $body = "<?php\n"
        . "declare(strict_types=1);\n\n"
        . "/* Base de données — fichier écrit depuis adminbtx. Ne pas publier. */\n\n"
        . 'return ' . var_export($payload, true) . ";\n";

    $file = mim_db_config_file();
    $written = file_put_contents($file, $body, LOCK_EX);

    if ($written === false) {
        return false;
    }

    @chmod($file, 0600);

    return true;
}

/** Dernier message d'échec de MySQL, vide quand tout va bien. */
function mim_db_error(): string
{
    return (string) ($GLOBALS['mim_db_error'] ?? '');
}

/**
 * Le back-office peut-il écrire ? Toujours oui quand MySQL sert : le dossier data/
 * n'est alors qu'un miroir, son écriture ne doit pas bloquer un formulaire.
 */
function mim_store_writable(): bool
{
    return mim_backend() === 'mysql' || (is_dir(MIM_DATA) && is_writable(MIM_DATA));
}

/** Raison lisible d'un enregistrement impossible, à afficher telle quelle à l'administrateur. */
function mim_store_failure(): string
{
    return mim_backend() === 'mysql'
        ? 'La base de données a refusé l’enregistrement : ' . mim_db_error()
        : 'Le dossier data/ n’est pas accessible en écriture : enregistrement impossible.';
}

/**
 * Schéma de la base, une entrée par table. Une seule source : l'installateur, le fichier
 * sql/schema.sql et les écritures la lisent tous.
 *
 * @return array<string, string> nom de table => CREATE TABLE
 */
function mim_db_tables(): array
{
    $tail = ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';

    return [
        'mim_schedule' => <<<SQL
CREATE TABLE IF NOT EXISTS mim_schedule (
  id TINYINT UNSIGNED NOT NULL,
  edition SMALLINT UNSIGNED NOT NULL DEFAULT 37,
  marathon_date DATE NULL,
  marathon_start TIME NULL,
  semi_date DATE NULL,
  semi_start TIME NULL,
  tous_date DATE NULL,
  tous_start TIME NULL,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
)$tail
SQL,

        'mim_site' => <<<SQL
CREATE TABLE IF NOT EXISTS mim_site (
  id TINYINT UNSIGNED NOT NULL,
  registration_url VARCHAR(255) NOT NULL DEFAULT '',
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
)$tail
SQL,

        'mim_legal' => <<<SQL
CREATE TABLE IF NOT EXISTS mim_legal (
  id TINYINT UNSIGNED NOT NULL,
  entity_name VARCHAR(255) NOT NULL DEFAULT '',
  entity_ein VARCHAR(64) NOT NULL DEFAULT '',
  entity_street VARCHAR(255) NOT NULL DEFAULT '',
  entity_city VARCHAR(120) NOT NULL DEFAULT '',
  entity_state VARCHAR(120) NOT NULL DEFAULT '',
  entity_zip VARCHAR(32) NOT NULL DEFAULT '',
  entity_country VARCHAR(120) NOT NULL DEFAULT '',
  association_name VARCHAR(255) NOT NULL DEFAULT '',
  association_role VARCHAR(255) NOT NULL DEFAULT '',
  email_contact VARCHAR(255) NOT NULL DEFAULT '',
  email_support VARCHAR(255) NOT NULL DEFAULT '',
  email_legal VARCHAR(255) NOT NULL DEFAULT '',
  billing_currency VARCHAR(8) NOT NULL DEFAULT 'USD',
  price_marathon VARCHAR(32) NOT NULL DEFAULT '',
  price_semi VARCHAR(32) NOT NULL DEFAULT '',
  price_tous VARCHAR(32) NOT NULL DEFAULT '',
  eur_marathon VARCHAR(32) NOT NULL DEFAULT '',
  eur_semi VARCHAR(32) NOT NULL DEFAULT '',
  eur_tous VARCHAR(32) NOT NULL DEFAULT '',
  mad_marathon VARCHAR(32) NOT NULL DEFAULT '',
  mad_semi VARCHAR(32) NOT NULL DEFAULT '',
  mad_tous VARCHAR(32) NOT NULL DEFAULT '',
  fx_source VARCHAR(255) NOT NULL DEFAULT '',
  fx_moment VARCHAR(255) NOT NULL DEFAULT '',
  terms_updated VARCHAR(32) NOT NULL DEFAULT '',
  privacy_updated VARCHAR(32) NOT NULL DEFAULT '',
  refund_updated VARCHAR(32) NOT NULL DEFAULT '',
  refund_deadline VARCHAR(32) NOT NULL DEFAULT '',
  deferral_deadline VARCHAR(32) NOT NULL DEFAULT '',
  deferral_fee VARCHAR(64) NOT NULL DEFAULT '',
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
)$tail
SQL,

        'mim_admin' => <<<SQL
CREATE TABLE IF NOT EXISTS mim_admin (
  id TINYINT UNSIGNED NOT NULL,
  login VARCHAR(64) NOT NULL DEFAULT '',
  password_hash VARCHAR(255) NOT NULL DEFAULT '',
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
)$tail
SQL,

        'mim_setting' => <<<SQL
CREATE TABLE IF NOT EXISTS mim_setting (
  name VARCHAR(64) NOT NULL,
  value VARCHAR(255) NOT NULL DEFAULT '',
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (name)
)$tail
SQL,

        'mim_media' => <<<SQL
CREATE TABLE IF NOT EXISTS mim_media (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  file VARCHAR(255) NOT NULL,
  edition SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  ref VARCHAR(255) NOT NULL DEFAULT '',
  alt VARCHAR(255) NOT NULL DEFAULT '',
  legende VARCHAR(500) NOT NULL DEFAULT '',
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_file (file)
)$tail
SQL,

        'mim_photo' => <<<SQL
CREATE TABLE IF NOT EXISTS mim_photo (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  file VARCHAR(255) NOT NULL,
  thumb VARCHAR(255) NOT NULL DEFAULT '',
  edition SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  published TINYINT(1) NOT NULL DEFAULT 1,
  ref VARCHAR(255) NOT NULL DEFAULT '',
  credit VARCHAR(120) NOT NULL DEFAULT '',
  taken DATE NULL,
  width INT UNSIGNED NOT NULL DEFAULT 0,
  height INT UNSIGNED NOT NULL DEFAULT 0,
  size INT UNSIGNED NOT NULL DEFAULT 0,
  thumb_size INT UNSIGNED NOT NULL DEFAULT 0,
  added_at INT UNSIGNED NOT NULL DEFAULT 0,
  alt_fr VARCHAR(255) NOT NULL DEFAULT '',
  alt_en VARCHAR(255) NOT NULL DEFAULT '',
  alt_ar VARCHAR(255) NOT NULL DEFAULT '',
  alt_es VARCHAR(255) NOT NULL DEFAULT '',
  caption_fr VARCHAR(500) NOT NULL DEFAULT '',
  caption_en VARCHAR(500) NOT NULL DEFAULT '',
  caption_ar VARCHAR(500) NOT NULL DEFAULT '',
  caption_es VARCHAR(500) NOT NULL DEFAULT '',
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_file (file),
  KEY idx_publication (published, edition, added_at)
)$tail
SQL,
    ];
}

/** Fichier SQL complet, importable dans phpMyAdmin après création de la base. */
function mim_db_dump(): string
{
    $config = mim_db_config();
    $lines = [
        '-- Structure de la base du Marathon International de Marrakech',
        '-- Généré par php db-install.php --export',
        '-- Créer d\'abord la base « ' . $config['name'] . ' » sur le serveur, puis importer ce fichier.',
        '',
        'SET NAMES utf8mb4;',
        '',
    ];

    foreach (mim_db_tables() as $sql) {
        $lines[] = $sql . ';';
        $lines[] = '';
    }

    return implode("\n", $lines);
}

/**
 * Colonnes d'une table de configuration à ligne unique, et chemin dans le tableau PHP
 * correspondant. C'est ce lien qui garde les pages publiques inchangées.
 *
 * @return array<string, array{table: string, file: string, map: array<string, array<int, string>>, casts: array<string, string>}>
 */
function mim_db_stores(): array
{
    return [
        'schedule' => [
            'table' => 'mim_schedule',
            'file' => 'schedule.json',
            'map' => [
                'edition' => ['edition'],
                'marathon_date' => ['marathon_date'],
                'marathon_start' => ['marathon_start'],
                'semi_date' => ['semi_date'],
                'semi_start' => ['semi_start'],
                'tous_date' => ['tous_date'],
                'tous_start' => ['tous_start'],
            ],
            'casts' => [
                'edition' => 'int',
                'marathon_start' => 'time',
                'semi_start' => 'time',
                'tous_start' => 'time',
            ],
        ],
        'site' => [
            'table' => 'mim_site',
            'file' => 'site.json',
            'map' => ['registration_url' => ['registration_url']],
            'casts' => [],
        ],
        'admin' => [
            'table' => 'mim_admin',
            'file' => 'admin.json',
            'map' => ['login' => ['login'], 'password_hash' => ['password_hash']],
            'casts' => [],
        ],
        'legal' => [
            'table' => 'mim_legal',
            'file' => 'legal.json',
            'map' => [
                'entity_name' => ['entity', 'name'],
                'entity_ein' => ['entity', 'ein'],
                'entity_street' => ['entity', 'street'],
                'entity_city' => ['entity', 'city'],
                'entity_state' => ['entity', 'state'],
                'entity_zip' => ['entity', 'zip'],
                'entity_country' => ['entity', 'country'],
                'association_name' => ['association', 'name'],
                'association_role' => ['association', 'role'],
                'email_contact' => ['emails', 'contact'],
                'email_support' => ['emails', 'support'],
                'email_legal' => ['emails', 'legal'],
                'billing_currency' => ['pricing', 'billing'],
                'price_marathon' => ['pricing', 'marathon'],
                'price_semi' => ['pricing', 'semi'],
                'price_tous' => ['pricing', 'tous'],
                'eur_marathon' => ['pricing', 'eur_marathon'],
                'eur_semi' => ['pricing', 'eur_semi'],
                'eur_tous' => ['pricing', 'eur_tous'],
                'mad_marathon' => ['pricing', 'mad_marathon'],
                'mad_semi' => ['pricing', 'mad_semi'],
                'mad_tous' => ['pricing', 'mad_tous'],
                'fx_source' => ['pricing', 'fx_source'],
                'fx_moment' => ['pricing', 'fx_moment'],
                'terms_updated' => ['policies', 'terms_updated'],
                'privacy_updated' => ['policies', 'privacy_updated'],
                'refund_updated' => ['policies', 'refund_updated'],
                'refund_deadline' => ['policies', 'refund_deadline'],
                'deferral_deadline' => ['policies', 'deferral_deadline'],
                'deferral_fee' => ['policies', 'deferral_fee'],
            ],
            'casts' => [],
        ],
        'media' => [
            'table' => 'mim_media',
            'file' => 'media.json',
            'map' => [],
            'casts' => [],
        ],
        'gallery' => [
            'table' => 'mim_photo',
            'file' => 'gallery.json',
            'map' => [],
            'casts' => [],
        ],
    ];
}

/** Le stockage réellement utilisé : « mysql » seulement si la base répond et est à jour. */
function mim_backend(): string
{
    if (isset($GLOBALS['mim_backend'])) {
        return (string) $GLOBALS['mim_backend'];
    }

    if (mim_db() === null) {
        return $GLOBALS['mim_backend'] = 'json';
    }

    $pdo = mim_db();

    try {
        foreach (array_keys(mim_db_tables()) as $table) {
            $pdo->query('SELECT 1 FROM `' . $table . '` LIMIT 1');
        }
    } catch (PDOException $exception) {
        $GLOBALS['mim_db_error'] = $exception->getMessage();

        return $GLOBALS['mim_backend'] = 'json';
    }

    return $GLOBALS['mim_backend'] = 'mysql';
}

/**
 * Lecture d'un jeu de configuration.
 *
 * @return array<string, mixed>
 */
function mim_store_read(string $store): array
{
    $stores = mim_db_stores();

    if (!isset($stores[$store])) {
        throw new InvalidArgumentException("Stockage inconnu : $store");
    }

    if (mim_backend() === 'mysql') {
        try {
            return $store === 'media' || $store === 'gallery'
                ? mim_store_read_rows($store)
                : mim_store_read_single($store);
        } catch (PDOException $exception) {
            $GLOBALS['mim_db_error'] = $exception->getMessage();
        }
    }

    $decoded = json_decode((string) @file_get_contents(MIM_DATA . '/' . $stores[$store]['file']), true);

    return is_array($decoded) ? $decoded : [];
}

/** @return array<string, mixed> */
function mim_store_read_single(string $store): array
{
    $definition = mim_db_stores()[$store];
    $columns = array_keys($definition['map']);
    $sql = 'SELECT `' . implode('`, `', $columns) . '` FROM `' . $definition['table'] . '` WHERE id = 1 LIMIT 1';
    $statement = mim_db()->query($sql);
    $row = $statement->fetch();
    $data = [];

    if (!is_array($row)) {
        return $data;
    }

    foreach ($definition['map'] as $column => $path) {
        $value = $row[$column] ?? null;

        if ($value === null) {
            $value = '';
        }

        if (isset($definition['casts'][$column])) {
            $value = mim_db_cast($value, $definition['casts'][$column]);
        }

        mim_store_path_set($data, $path, $value);
    }

    return $data;
}

/**
 * Ramène une valeur de MySQL à la forme que le site manipule déjà.
 * Une colonne TIME répond « 07:00:00 » : les heures de départ sont écrites « 07:00 »
 * partout ailleurs, et la validation mim_is_time() refuserait la forme longue.
 * PHP rend aussi bien des chaînes que des entiers selon les colonnes : d'où mixed.
 */
function mim_db_cast(mixed $value, string $kind): mixed
{
    if ($kind === 'int') {
        return (int) $value;
    }

    if ($kind === 'time' && preg_match('/^(\d{2}:\d{2}):\d{2}$/', (string) $value, $match) === 1) {
        return $match[1];
    }

    return $value;
}

/**
 * Médias et galerie : plusieurs lignes, plus un quota tenu dans mim_setting.
 *
 * @return array{quota_mb: int, media: array<string, array<string, mixed>>}|array{quota_mb: int, photos: array<string, array<string, mixed>>}
 */
function mim_store_read_rows(string $store): array
{
    $defaultQuota = $store === 'media' ? 500 : 200;
    $setting = $store === 'media' ? 'media_quota_mb' : 'gallery_quota_mb';
    $statement = mim_db()->prepare('SELECT value FROM mim_setting WHERE name = ?');
    $statement->execute([$setting]);
    $quota = (int) $statement->fetchColumn();
    $payload = ['quota_mb' => $quota >= 1 ? $quota : $defaultQuota, $store === 'media' ? 'media' : 'photos' => []];

    if ($store === 'media') {
        foreach (mim_db()->query('SELECT file, edition, ref, alt, legende FROM mim_media ORDER BY id') as $row) {
            $payload['media'][(string) $row['file']] = [
                'edition' => (int) $row['edition'],
                'ref' => (string) $row['ref'],
                'alt' => (string) $row['alt'],
                'legende' => (string) $row['legende'],
            ];
        }

        return $payload;
    }

    foreach (mim_db()->query('SELECT * FROM mim_photo ORDER BY id') as $row) {
        $photo = [
            'edition' => (int) $row['edition'],
            'published' => (int) $row['published'] === 1,
            'ref' => (string) $row['ref'],
            'credit' => (string) $row['credit'],
            'taken' => (string) ($row['taken'] ?? ''),
            'alt' => [],
            'caption' => [],
            'thumb' => (string) $row['thumb'],
            'width' => (int) $row['width'],
            'height' => (int) $row['height'],
            'size' => (int) $row['size'],
            'thumb_size' => (int) $row['thumb_size'],
            'added' => (int) $row['added_at'],
        ];

        foreach (array_keys(MIM_LOCALES) as $locale) {
            $photo['alt'][$locale] = (string) $row['alt_' . $locale];
            $photo['caption'][$locale] = (string) $row['caption_' . $locale];
        }

        $payload['photos'][(string) $row['file']] = $photo;
    }

    return $payload;
}

/**
 * Écriture d'un jeu de configuration.
 *
 * @param array<string, mixed> $data
 */
function mim_store_write(string $store, array $data): bool
{
    $stores = mim_db_stores();

    if (!isset($stores[$store])) {
        throw new InvalidArgumentException("Stockage inconnu : $store");
    }

    if (mim_backend() !== 'mysql') {
        return mim_save_json(MIM_DATA . '/' . $stores[$store]['file'], $data);
    }

    try {
        $saved = $store === 'media' || $store === 'gallery'
            ? mim_store_write_rows($store, $data)
            : mim_store_write_single($store, $data);
    } catch (PDOException $exception) {
        /* Une écriture de galerie ou de médias est une transaction : sans retour en
           arrière elle resterait ouverte et bloquerait toutes les écritures suivantes. */
        $pdo = mim_db();
        if ($pdo !== null && $pdo->inTransaction()) {
            $pdo->rollBack();
        }

        $GLOBALS['mim_db_error'] = $exception->getMessage();

        return false;
    }

    /* Le fichier JSON reste le miroir à jour : si la base tombe un jour, le repli
       proposé au visiteur et à l'administrateur sera la dernière configuration vraie. */
    if ($saved) {
        mim_save_json(MIM_DATA . '/' . $stores[$store]['file'], $data);
    }

    return $saved;
}

/** @param array<string, mixed> $data */
function mim_store_write_single(string $store, array $data): bool
{
    $definition = mim_db_stores()[$store];
    /* La ligne unique de chaque jeu de configuration porte l'id 1 : c'est sa clé. */
    $values = ['id' => 1];

    foreach ($definition['map'] as $column => $path) {
        $value = mim_store_path_get($data, $path, '');

        /* Une date ou une heure vide se range en NULL, jamais en chaîne vide. */
        if ($value === '' && preg_match('/_(date|start)$/', $column)) {
            $value = null;
        }

        $values[$column] = $value;
    }

    /* Upsert et non « UPDATE puis INSERT si rowCount() == 0 » : MySQL compte les lignes
       modifiées, donc un enregistrement sans changement renvoie 0 et l'INSERT échoue
       sur la clé primaire. */
    mim_db_upsert(mim_db(), $definition['table'], $values, ['id']);

    return true;
}

/**
 * Écrit une ligne en la remplaçant si elle existe déjà.
 *
 * La clause « ON DUPLICATE KEY UPDATE » est dépréciée depuis MySQL 8.0.20 et le compte
 * rendu par rowCount() ne distingue pas « rien à faire » de « ligne absente » : on teste
 * donc la présence avant de choisir UPDATE ou INSERT.
 *
 * Les préparations natives de PDO rejettent toute clé surnuméraire : chaque exécution ne
 * reçoit exactement que les valeurs que sa requête nomme.
 *
 * @param array<string, mixed> $columns colonne => valeur
 * @param array<int, string> $keys colonnes qui désignent la ligne
 */
function mim_db_upsert(PDO $pdo, string $table, array $columns, array $keys): void
{
    $conditions = [];
    $lookups = [];

    foreach ($keys as $key) {
        $conditions[] = "`$key` = :key_$key";
        $lookups['key_' . $key] = $columns[$key];
    }

    $where = implode(' AND ', $conditions);
    $exists = $pdo->prepare('SELECT COUNT(*) FROM `' . $table . '` WHERE ' . $where);
    $exists->execute($lookups);

    if ((int) $exists->fetchColumn() > 0) {
        $assignments = [];
        $values = $lookups;

        foreach ($columns as $name => $value) {
            if (in_array($name, $keys, true)) {
                continue;
            }
            $assignments[] = "`$name` = :$name";
            $values[$name] = $value;
        }

        $pdo->prepare('UPDATE `' . $table . '` SET ' . implode(', ', $assignments) . ' WHERE ' . $where)
            ->execute($values);

        return;
    }

    $names = array_keys($columns);
    $fields = '`' . implode('`, `', $names) . '`';
    $binds = ':' . implode(', :', $names);

    $pdo->prepare('INSERT INTO `' . $table . '` (' . $fields . ') VALUES (' . $binds . ')')->execute($columns);
}

/** @param array<string, mixed> $data */
function mim_store_write_rows(string $store, array $data): bool
{
    $pdo = mim_db();
    $entries = $store === 'media' ? (array) ($data['media'] ?? []) : (array) ($data['photos'] ?? []);
    $quota = (int) ($data['quota_mb'] ?? ($store === 'media' ? 500 : 200));

    $pdo->beginTransaction();

    mim_db_upsert($pdo, 'mim_setting', [
        'name' => $store === 'media' ? 'media_quota_mb' : 'gallery_quota_mb',
        'value' => (string) $quota,
    ], ['name']);

    $locales = array_keys(MIM_LOCALES);

    foreach ($entries as $file => $entry) {
        if ($store === 'media') {
            mim_db_upsert($pdo, 'mim_media', [
                'file' => (string) $file,
                'edition' => (int) ($entry['edition'] ?? 0),
                'ref' => (string) ($entry['ref'] ?? ''),
                'alt' => (string) ($entry['alt'] ?? ''),
                'legende' => (string) ($entry['legende'] ?? ''),
            ], ['file']);

            continue;
        }

        $columns = [
            'file' => (string) $file,
            'thumb' => (string) ($entry['thumb'] ?? ''),
            'edition' => (int) ($entry['edition'] ?? 0),
            'published' => !empty($entry['published']) ? 1 : 0,
            'ref' => (string) ($entry['ref'] ?? ''),
            'credit' => (string) ($entry['credit'] ?? ''),
            'taken' => mim_is_date((string) ($entry['taken'] ?? '')) ? (string) $entry['taken'] : null,
            'width' => (int) ($entry['width'] ?? 0),
            'height' => (int) ($entry['height'] ?? 0),
            'size' => (int) ($entry['size'] ?? 0),
            'thumb_size' => (int) ($entry['thumb_size'] ?? 0),
            'added_at' => (int) ($entry['added'] ?? 0),
        ];

        foreach ($locales as $locale) {
            $columns['alt_' . $locale] = (string) (($entry['alt'][$locale] ?? ''));
        }

        foreach ($locales as $locale) {
            $columns['caption_' . $locale] = (string) (($entry['caption'][$locale] ?? ''));
        }

        mim_db_upsert($pdo, 'mim_photo', $columns, ['file']);
    }

    /* Les lignes retirées du fichier ne doivent pas survivre en base. */
    $files = array_map(static fn ($file): string => (string) $file, array_keys($entries));
    $table = mim_db_stores()[$store]['table'];

    if ($files === []) {
        $pdo->exec('DELETE FROM `' . $table . '`');
    } else {
        $placeholders = rtrim(str_repeat('?, ', count($files)), ', ');
        $pdo->prepare('DELETE FROM `' . $table . '` WHERE file NOT IN (' . $placeholders . ')')->execute($files);
    }

    $pdo->commit();

    return true;
}

/** @param array<int, string> $path @param-out array<string, mixed> $data */
function mim_store_path_set(array &$data, array $path, $value): void
{
    $key = array_shift($path);

    if ($key === null) {
        return;
    }

    if ($path === []) {
        $data[$key] = $value;

        return;
    }

    if (!isset($data[$key]) || !is_array($data[$key])) {
        $data[$key] = [];
    }

    mim_store_path_set($data[$key], $path, $value);
}

/**
 * @param array<string, mixed> $data
 * @param array<int, string> $path
 */
function mim_store_path_get(array $data, array $path, $default = '')
{
    $cursor = $data;

    foreach ($path as $key) {
        if (!is_array($cursor) || !array_key_exists($key, $cursor)) {
            return $default;
        }

        $cursor = $cursor[$key];
    }

    /* Seule la feuille doit être une valeur simple : les niveaux intermédiaires sont des tableaux. */
    return is_scalar($cursor) ? trim((string) $cursor) : $default;
}

/**
 * Date de dernière modification du jeu : l'horodatage de la base quand elle sert,
 * celui du fichier sinon. Le plan de site s'en sert pour <lastmod>.
 */
function mim_store_updated(string $store): int
{
    $stores = mim_db_stores();

    if (!isset($stores[$store])) {
        return 0;
    }

    if (mim_backend() === 'mysql') {
        try {
            $table = $stores[$store]['table'];
            /* UNIX_TIMESTAMP rend l'instant vrai, quel que soit le fuseau de la session
               MySQL : sur OVH le serveur est en UTC alors que le site est à Casablanca. */
            $statement = mim_db()->query("SELECT MAX(UNIX_TIMESTAMP(updated_at)) FROM `$table`");
            $stamp = $statement->fetchColumn();

            if ($stamp !== null && $stamp !== false) {
                return (int) $stamp;
            }
        } catch (PDOException $exception) {
            $GLOBALS['mim_db_error'] = $exception->getMessage();
        }
    }

    $file = MIM_DATA . '/' . $stores[$store]['file'];

    return is_file($file) ? (int) filemtime($file) : 0;
}

/**
 * Complète les tables installées par une version antérieure du schéma.
 *
 * « CREATE TABLE IF NOT EXISTS » ne touche jamais une table existante : sans cette
 * reprise, l'enregistrement du compte admin échouerait sur une base déjà en service.
 *
 * @return array<int, string> colonnes ajoutées, pour l'affichage de l'installateur
 */
function mim_db_migrate(): array
{
    $pdo = mim_db();
    $log = [];

    if ($pdo === null) {
        return $log;
    }

    /* Colonne ajoutée => définition SQL, tables et colonnes fixes dans le code. */
    $additions = [
        'mim_admin' => ['login' => "VARCHAR(64) NOT NULL DEFAULT '' AFTER id"],
    ];
    $columns = $pdo->prepare(
        'SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?'
    );

    foreach ($additions as $table => $definitions) {
        $columns->execute([mim_db_config()['name'], $table]);
        $existing = $columns->fetchAll(PDO::FETCH_COLUMN);

        if ($existing === []) {
            continue;
        }

        foreach ($definitions as $column => $definition) {
            if (in_array($column, $existing, true)) {
                continue;
            }
            $pdo->exec("ALTER TABLE `$table` ADD COLUMN `$column` $definition");
            $log[] = "colonne $table.$column ajoutée";
        }
    }

    return $log;
}

/**
 * Installe le schéma, puis, si demandé, recopie la configuration déjà présente dans
 * data/*.json. Rappelable : les tables existantes ne sont pas touchées, les écritures
 * sont des UPSERT.
 *
 * @return array<int, string> actions réalisées, pour l'affichage
 */
function mim_db_install(bool $import = true, bool $createDatabase = false): array
{
    $config = mim_db_config();
    $log = [];

    if ($createDatabase) {
        $dsn = sprintf('mysql:host=%s;port=%d;charset=%s', $config['host'], $config['port'], $config['charset']);
        $root = new PDO($dsn, $config['user'], $config['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 3,
        ]);
        $root->exec(sprintf(
            'CREATE DATABASE IF NOT EXISTS `%s` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci',
            str_replace('`', '', $config['name'])
        ));
        $log[] = 'base « ' . $config['name'] . ' » créée si elle manquait';
    }

    $pdo = mim_db();

    if ($pdo === null) {
        throw new RuntimeException('MySQL injoignable : ' . mim_db_error());
    }

    foreach (mim_db_tables() as $name => $sql) {
        $pdo->exec($sql);
        $log[] = "table $name vérifiée";
    }

    $log = array_merge($log, mim_db_migrate());

    /* Les tables viennent d'apparaître : la réponse précédente, « json », est périmée. */
    unset($GLOBALS['mim_backend']);

    if (!$import) {
        return $log;
    }

    foreach (array_keys(mim_db_stores()) as $store) {
        $file = MIM_DATA . '/' . mim_db_stores()[$store]['file'];

        if (!is_file($file)) {
            continue;
        }

        $decoded = json_decode((string) file_get_contents($file), true);

        if (!is_array($decoded) || $decoded === []) {
            continue;
        }

        if (mim_store_write($store, $decoded)) {
            $log[] = "configuration importée : $store";
        } else {
            $log[] = "import impossible : $store (" . mim_db_error() . ')';
        }
    }

    return $log;
}
