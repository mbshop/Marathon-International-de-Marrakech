<?php
declare(strict_types=1);

/*
 * Paramètres MySQL de la configuration du site.
 *
 * Deux sources, dans cet ordre :
 *   1. les variables d'environnement MIM_DB_* (hébergement, systemd, docker) ;
 *   2. config/database.local.php, écrit par l'onglet « Base de données » d'adminbtx
 *      sur les hébergements mutualisés où l'on ne peut pas définir de variable.
 * Les valeurs par défaut correspondent au MySQL local de WAMP. Aucun mot de passe
 * n'est écrit dans ce fichier, qui peut donc rester dans le dépôt.
 */

$localFile = __DIR__ . '/database.local.php';
$local = is_file($localFile) ? require $localFile : [];
$local = is_array($local) ? $local : [];

$env = static function (string $key, string $default = ''): string {
    $value = getenv($key);

    if ($value === false || $value === '') {
        $value = $_SERVER[$key] ?? $_ENV[$key] ?? null;
    }

    return $value === null || $value === '' ? $default : (string) $value;
};

/* L'environnement reste prioritaire : il permet de surcharger un hébergement sans
   modifier le fichier local, et le fichier ne peut pas contourner l'opérateur. */
$pick = static function (string $envKey, string $fileKey, string $default) use ($env, $local): string {
    $fromEnv = $env($envKey, '');

    if ($fromEnv !== '') {
        return $fromEnv;
    }

    $fromFile = $local[$fileKey] ?? '';

    return is_scalar($fromFile) && (string) $fromFile !== '' ? (string) $fromFile : $default;
};

return [
    'host' => $pick('MIM_DB_HOST', 'host', '127.0.0.1'),
    'port' => (int) $pick('MIM_DB_PORT', 'port', '3306'),
    'name' => $pick('MIM_DB_NAME', 'name', 'mim_marrakech'),
    'user' => $pick('MIM_DB_USER', 'user', 'root'),
    'pass' => $pick('MIM_DB_PASS', 'pass', ''),
    'charset' => 'utf8mb4',
];
