<?php
declare(strict_types=1);

require __DIR__ . '/inc/bootstrap.php';

/**
 * Installateur de la base de données : crée le schéma, recopie la configuration déjà
 * saisie dans data/*.json, et écrit le fichier sql/schema.sql importable dans phpMyAdmin.
 *
 * Deux entrées : la ligne de commande, ou le navigateur quand un administrateur est
 * connecté à adminbtx.php. Sans session d'admin, la page répond une erreur.
 */

$isCli = PHP_SAPI === 'cli';
$cliOptions = $isCli ? getopt('', ['status', 'no-import', 'create-database', 'export']) : [];

if (!$isCli) {
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'httponly' => true,
            'samesite' => 'Lax',
            'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        ]);
        session_start();
    }

    if (empty($_SESSION['mim_admin'])) {
        http_response_code(403);
        header('Content-Type: text/plain; charset=UTF-8');
        echo "Accès réservé : ouvrez adminbtx.php, connectez-vous, puis revenez ici.\n";
        exit;
    }

    $csrfOk = hash_equals((string) ($_SESSION['mim_csrf'] ?? ''), (string) ($_POST['csrf'] ?? ''));
}

$sqlDirectory = MIM_ROOT . '/sql';
$schemaFile = $sqlDirectory . '/schema.sql';

/** État de la base : connexion, tables présentes, lignes par table. @return array<string, mixed> */
function mim_install_status(): array
{
    $status = [
        'backend' => mim_backend(),
        'error' => mim_db_error(),
        'config' => mim_db_config(),
        'tables' => [],
    ];
    $pdo = mim_db();

    if ($pdo === null) {
        return $status;
    }

    foreach (array_keys(mim_db_tables()) as $table) {
        try {
            $count = (int) $pdo->query('SELECT COUNT(*) FROM `' . $table . '`')->fetchColumn();
            $status['tables'][$table] = ['ok' => true, 'rows' => $count];
        } catch (PDOException $exception) {
            $status['tables'][$table] = ['ok' => false, 'rows' => null];
        }
    }

    return $status;
}

$log = [];
$failure = '';

function mim_install_export(string $schemaFile, string $sqlDirectory): void
{
    if (!is_dir($sqlDirectory) && !mkdir($sqlDirectory, 0o755, true)) {
        throw new RuntimeException('Impossible de créer le dossier sql/.');
    }
    if (file_put_contents($schemaFile, mim_db_dump(), LOCK_EX) === false) {
        throw new RuntimeException('Impossible d’écrire ' . $schemaFile . '.');
    }
}

if ($isCli) {
    try {
        if (isset($cliOptions['export'])) {
            mim_install_export($schemaFile, $sqlDirectory);
            $log[] = 'fichier écrit : sql/schema.sql';
        } elseif (isset($cliOptions['status'])) {
            $status = mim_install_status();
        } else {
            $log = mim_db_install(!isset($cliOptions['no-import']), isset($cliOptions['create-database']));
        }
    } catch (Throwable $throwable) {
        $failure = $throwable->getMessage();
    }

    $status = $status ?? mim_install_status();

    /* Sortie texte : l'installateur se lit aussi dans un script de déploiement. */
    $config = $status['config'];
    echo "Base visée : {$config['user']}@{$config['host']}:{$config['port']}/{$config['name']}\n";
    echo 'Stockage utilisé : ' . $status['backend'] . "\n";

    if ($status['error'] !== '') {
        echo 'MySQL : ' . $status['error'] . "\n";
    }

    foreach ($status['tables'] as $table => $state) {
        echo str_pad('  ' . $table, 22) . ($state['ok'] ? $state['rows'] . ' ligne(s)' : 'absente') . "\n";
    }

    foreach ($log as $line) {
        echo '  · ' . $line . "\n";
    }

    if ($failure !== '') {
        echo 'ÉCHEC : ' . $failure . "\n";
        exit(1);
    }

    exit(0);
}

/* ---------- navigation ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$csrfOk) {
    $failure = 'Session expirée : rechargez la page, reconnectez-vous si besoin et réessayez.';
}

$action = $csrfOk ? (string) ($_POST['action'] ?? '') : '';

if ($action !== '') {
    try {
        if ($action === 'export') {
            mim_install_export($schemaFile, $sqlDirectory);
            $log[] = 'fichier écrit : sql/schema.sql';
        } else {
            $log = mim_db_install($action === 'install-import', $action === 'install-create');
        }
    } catch (Throwable $throwable) {
        $failure = $throwable->getMessage();
    }
}

$status = mim_install_status();
$csrf = (string) ($_SESSION['mim_csrf'] ?? '');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">
  <title>Base de données — MIM</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Big+Shoulders+Display:wght@600;700;800;900&family=IBM+Plex+Mono:wght@400;500;600&family=IBM+Plex+Sans:wght@400;500;600&display=swap"
    rel="stylesheet"
  >
  <link rel="stylesheet" href="styles.css">
  <link rel="stylesheet" href="admin.css">
  <link rel="icon" type="image/png" sizes="96x96" href="assets/favicon.png">
  <style>
    .db-table { border-collapse: collapse; width: 100%; margin-top: 12px; }
    .db-table th, .db-table td { text-align: left; padding: 7px 10px; border-bottom: 1px solid var(--adm-line, #ddd8cc); font-size: 14px; }
    .db-table th { font-family: 'IBM Plex Mono', monospace; font-size: 11px; letter-spacing: .08em; text-transform: uppercase; }
    .db-missing { color: #a33; }
  </style>
</head>
<body class="adm">
  <header class="adm-bar">
    <div class="adm-bar-inner">
      <a class="adm-brand" href="index.php">MIM <span>base de données</span></a>
      <div class="adm-bar-actions">
        <a class="adm-link" href="adminbtx.php">Back-office</a>
        <a class="adm-link" href="index.php">Voir le site</a>
      </div>
    </div>
  </header>

  <main class="adm-main">
    <h1>Installation de la base</h1>

    <?php if ($failure !== ''): ?>
      <ul class="adm-errors" role="alert">
        <li><?= e($failure) ?></li>
      </ul>
    <?php endif; ?>

    <section class="adm-card adm-card-wide">
      <h2>Stockage utilisé</h2>
      <p class="adm-meta">
        <?php if ($status['backend'] === 'mysql'): ?>
          MySQL — <?= e($status['config']['name']) ?> sur <?= e($status['config']['host']) ?>:<?= e((string) $status['config']['port']) ?>,
          connecté en <?= e($status['config']['user']) ?>.
        <?php else: ?>
          Fichiers <code>data/*.json</code> : la base n’est ni joignable, ni installée.
        <?php endif; ?>
      </p>
      <?php if ($status['error'] !== ''): ?>
        <p class="adm-note"><?= e($status['error']) ?></p>
      <?php endif; ?>

      <table class="db-table">
        <thead>
          <tr><th>Table</th><th>Rôle</th><th>Lignes</th></tr>
        </thead>
        <tbody>
          <?php
          $roles = [
              'mim_schedule' => 'Édition, dates et heures de départ',
              'mim_site' => 'Lien de l’application d’inscription',
              'mim_legal' => 'Identité juridique, e-mails, tarifs, règles',
              'mim_admin' => 'Identifiant et mot de passe du back-office',
              'mim_setting' => 'Quotas de stockage',
              'mim_media' => 'Fiches des images du site',
              'mim_photo' => 'Galerie : fiches, textes et miniatures',
          ];
          foreach ($status['tables'] as $table => $state): ?>
            <tr>
              <td><code><?= e($table) ?></code></td>
              <td><?= e($roles[$table] ?? '') ?></td>
              <td class="<?= $state['ok'] ? '' : 'db-missing' ?>"><?= $state['ok'] ? e((string) $state['rows']) : 'absente' ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </section>

    <?php if ($log !== []): ?>
      <section class="adm-card adm-card-wide">
        <h2>Résultat de la dernière opération</h2>
        <ul class="adm-meta">
          <?php foreach ($log as $line): ?>
            <li><?= e($line) ?></li>
          <?php endforeach; ?>
        </ul>
      </section>
    <?php endif; ?>

    <section class="adm-card adm-card-wide">
      <h2>Actions</h2>
      <p class="adm-meta">
        L’installation crée les tables qui manquent puis recopie le contenu de
        <code>data/</code> dans la base. Les fichiers <code>data/*.json</code> continuent
        d’être écrits en miroir : si la base devient injoignable, le site reprend la
        dernière configuration enregistrée au lieu de repartir de zéro.
      </p>
      <form method="post" class="adm-form">
        <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
        <div class="adm-actions">
          <button class="btn btn-signal" type="submit" name="action" value="install-import">Créer les tables et importer data/</button>
          <button class="btn btn-ink" type="submit" name="action" value="install-create">Créer les tables seulement</button>
          <button class="btn btn-ink" type="submit" name="action" value="export">Écrire sql/schema.sql</button>
        </div>
      </form>
      <?php if ($status['config']['pass'] === ''): ?>
        <p class="adm-note">
          Le mot de passe MySQL utilisé est le défaut local de <code>config/database.php</code>
          (vide). En production, réglez les variables d’environnement <code>MIM_DB_HOST</code>,
          <code>MIM_DB_PORT</code>, <code>MIM_DB_NAME</code>, <code>MIM_DB_USER</code> et
          <code>MIM_DB_PASS</code> dans l’hébergement : rien de sensible ne doit rester dans le dépôt.
        </p>
      <?php endif; ?>
    </section>
  </main>
</body>
</html>
