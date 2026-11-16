<?php
declare(strict_types=1);

/**
 * Catalogue des photos de la galerie : table mim_photo quand MySQL est installé, sinon
 * data/gallery.json ; fichiers dans assets/galerie/. Une photo n'est publiée que si
 * l'organisateur l'a marquée « publiée » ; rien n'est généré ni inventé, les textes
 * manquants restent vides à l'écran.
 */

const MIM_GALLERY_DIR = 'assets/galerie';
const MIM_GALLERY_THUMB_DIR = 'assets/galerie/thumbs';

/** Poids maximum d'un fichier importé, et dimensions de sortie après ré-encodage. */
const MIM_GALLERY_MAX_INPUT = 8388608;
const MIM_GALLERY_DISPLAY = 1600;
const MIM_GALLERY_THUMB = 480;

/** Formats acceptés à l'import : le SVG est exclu, il peut contenir du JavaScript. */
const MIM_GALLERY_MIMES = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp',
    'image/avif' => 'avif',
];

/**
 * @return array{quota_mb: int, photos: array<string, array{
 *     edition: int, published: bool, ref: string, credit: string, taken: string,
 *     alt: array<string, string>, caption: array<string, string>,
 *     thumb: string, width: int, height: int, size: int, thumb_size: int, added: int
 * }>}
 */
function mim_gallery_data(): array
{
    static $data = null;

    if ($data !== null) {
        return $data;
    }

    $data = ['quota_mb' => 200, 'photos' => []];
    $decoded = mim_store_read('gallery');

    if (is_array($decoded)) {
        $quota = (int) ($decoded['quota_mb'] ?? 0);
        if ($quota >= 1 && $quota <= 102400) {
            $data['quota_mb'] = $quota;
        }
        if (isset($decoded['photos']) && is_array($decoded['photos'])) {
            foreach ($decoded['photos'] as $name => $photo) {
                if (!is_string($name) || !is_array($photo) || !str_starts_with($name, MIM_GALLERY_DIR . '/')) {
                    continue;
                }
                $data['photos'][$name] = mim_gallery_normalize($photo);
            }
        }
    }

    return $data;
}

/** @param array<string, mixed> $photo @return array<string, mixed> */
function mim_gallery_normalize(array $photo): array
{
    $texts = static function (string $field) use ($photo): array {
        $value = $photo[$field] ?? [];
        if (is_string($value)) {
            $value = ['fr' => $value];
        }
        $clean = [];
        foreach (array_keys(MIM_LOCALES) as $locale) {
            $clean[$locale] = mb_substr(trim((string) ($value[$locale] ?? '')), 0, $field === 'caption' ? 500 : 255);
        }

        return $clean;
    };

    return [
        'edition' => max(0, min(999, (int) ($photo['edition'] ?? 0))),
        'published' => !empty($photo['published']),
        'ref' => mb_substr(trim((string) ($photo['ref'] ?? '')), 0, 255),
        'credit' => mb_substr(trim((string) ($photo['credit'] ?? '')), 0, 120),
        'taken' => mim_is_date((string) ($photo['taken'] ?? '')) ? (string) $photo['taken'] : '',
        'alt' => $texts('alt'),
        'caption' => $texts('caption'),
        'thumb' => is_string($photo['thumb'] ?? null) && str_starts_with($photo['thumb'], MIM_GALLERY_THUMB_DIR . '/')
            ? $photo['thumb']
            : '',
        'width' => max(0, (int) ($photo['width'] ?? 0)),
        'height' => max(0, (int) ($photo['height'] ?? 0)),
        'size' => max(0, (int) ($photo['size'] ?? 0)),
        'thumb_size' => max(0, (int) ($photo['thumb_size'] ?? 0)),
        'added' => max(0, (int) ($photo['added'] ?? 0)),
    ];
}

/** @param array{quota_mb: int, photos: array<string, array<string, mixed>>} $data */
function mim_gallery_save(array $data): bool
{
    return mim_store_write('gallery', [
        'quota_mb' => max(1, (int) $data['quota_mb']),
        'photos' => $data['photos'],
    ]);
}

/** Texte d'une photo dans la langue du visiteur, avec repli sur le français. */
function mim_gallery_text(array $photo, string $field): string
{
    $locale = mim_lang();
    $value = (string) ($photo[$field][$locale] ?? '');

    return $value !== '' ? $value : (string) ($photo[$field]['fr'] ?? '');
}

/**
 * Texte alternatif toujours rempli : une image sans alt est une image inaccessible.
 * Le repli reprend la marque (SEO) et la référence interne ou le nom du fichier.
 */
function mim_gallery_alt(array $photo): string
{
    $text = mim_gallery_text($photo, 'alt');
    if ($text !== '') {
        return $text;
    }

    $ref = (string) ($photo['ref'] ?? '');
    $subject = $ref !== '' ? $ref : pathinfo((string) ($photo['file'] ?? ''), PATHINFO_FILENAME);

    return 'Marathon International de Marrakech — ' . $subject;
}

/**
 * Photos publiées, triées par édition décroissante puis par date d'ajout décroissante.
 *
 * @return array<string, array<string, mixed>>
 */
function mim_gallery_photos(?int $edition = null): array
{
    $sorted = [];

    foreach (mim_gallery_data()['photos'] as $file => $photo) {
        if (!$photo['published'] || !is_file(MIM_ROOT . '/' . $file)) {
            continue;
        }
        if ($edition !== null && $photo['edition'] !== $edition) {
            continue;
        }
        $photo['file'] = $file;
        $sorted[$file] = $photo;
    }

    uasort($sorted, static function (array $a, array $b): int {
        return [$b['edition'], $b['added']] <=> [$a['edition'], $a['added']];
    });

    return $sorted;
}

/**
 * Albums prêts à afficher, un par édition.
 *
 * @return array<int, array{edition: int, photos: array<string, array<string, mixed>>}>
 */
function mim_gallery_albums(?int $edition = null): array
{
    $albums = [];
    foreach (mim_gallery_photos($edition) as $file => $photo) {
        $albums[$photo['edition']]['photos'][$file] = $photo;
        $albums[$photo['edition']]['edition'] = $photo['edition'];
    }
    krsort($albums);

    return array_values($albums);
}

/** Place occupée par la galerie, photos et miniatures comprises. */
function mim_gallery_used_bytes(): int
{
    $used = 0;
    foreach (mim_gallery_data()['photos'] as $photo) {
        $used += $photo['size'] + $photo['thumb_size'];
    }

    return $used;
}

/** Éditions déjà utilisées, pour le filtre et le sélecteur d'upload. @return int[] */
function mim_gallery_editions(): array
{
    $editions = [];
    foreach (mim_gallery_data()['photos'] as $photo) {
        if ($photo['edition'] > 0) {
            $editions[$photo['edition']] = true;
        }
    }
    $list = array_keys($editions);
    rsort($list);

    return $list;
}

/** Réduit une image à $max pixels sur le plus grand côté, sans jamais l'agrandir. */
function mim_gallery_resize(GdImage $image, int $width, int $height, int $max): GdImage
{
    if ($width <= $max && $height <= $max) {
        return $image;
    }

    $scale = $max / max($width, $height);
    $target = imagecreatetruecolor((int) round($width * $scale), (int) round($height * $scale));

    if ($target === false) {
        return $image;
    }

    imagealphablending($target, false);
    imagesavealpha($target, true);
    imagecopyresampled($target, $image, 0, 0, 0, 0, imagesx($target), imagesy($target), $width, $height);

    return $target;
}

/** Écrit une image ré-encodée en WebP, sinon en JPEG, et renvoie son poids. */
function mim_gallery_write_image(GdImage $image, string $relative): int
{
    $path = MIM_ROOT . '/' . $relative;
    $directory = dirname($path);

    if (!is_dir($directory) && !mkdir($directory, 0o755, true)) {
        return 0;
    }

    if (function_exists('imagewebp')) {
        imagewebp($image, $path, 82);
    } else {
        // Le JPEG ignore la transparence : on la remplace par le fond clair du site.
        $flat = imagecreatetruecolor(imagesx($image), imagesy($image));
        imagefill($flat, 0, 0, imagecolorallocate($flat, 251, 250, 247));
        imagecopy($flat, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));
        imagejpeg($flat, $path, 86);
    }

    return is_file($path) ? (int) filesize($path) : 0;
}

/**
 * Importe une photo : contrôle le fichier, le ré-encode en deux tailles et écrit la fiche.
 * Le ré-encodage supprime les métadonnées EXIF — dont les coordonnées GPS de l'appareil.
 *
 * @return array{0: string, 1: array<string, mixed>} message d'erreur (vide si réussi) et fiche
 */
function mim_gallery_store(string $tmpPath, int $edition, string $ref): array
{
    $bytes = is_file($tmpPath) ? (int) filesize($tmpPath) : 0;

    if ($bytes < 1) {
        return ['Le fichier importé est vide ou dépasse la limite d’upload de PHP.', []];
    }
    if ($bytes > MIM_GALLERY_MAX_INPUT) {
        return ['Photo trop lourde : ' . mim_format_bytes($bytes) . ' pour une limite de ' . mim_format_bytes(MIM_GALLERY_MAX_INPUT) . '.', []];
    }

    $mime = (string) (new finfo(FILEINFO_MIME_TYPE))->file($tmpPath);
    if (!isset(MIM_GALLERY_MIMES[$mime])) {
        return ['Format non accepté (' . $mime . ') : JPEG, PNG, WebP ou AVIF uniquement.', []];
    }

    $info = @getimagesize($tmpPath);
    if ($info === false) {
        return ['Le fichier n’est pas une image lisible.', []];
    }

    [$width, $height] = $info;
    if ($width < 640 || $height < 420) {
        return ['Image trop petite pour la galerie : ' . $width . '×' . $height . ' pixels, minimum 640×420.', []];
    }
    if ($width > 12000 || $height > 12000) {
        return ['Image trop grande : ' . $width . '×' . $height . ' pixels.', []];
    }

    $raw = file_get_contents($tmpPath);
    $source = $raw === false ? false : @imagecreatefromstring($raw);

    if (!$source instanceof GdImage) {
        return ['Impossible de décoder l’image avec la librairie graphique du serveur.', []];
    }

    if (!imageistruecolor($source)) {
        imagepalettetotruecolor($source);
    }
    imagesavealpha($source, true);

    $display = mim_gallery_resize($source, $width, $height, MIM_GALLERY_DISPLAY);
    $thumb = mim_gallery_resize($display, imagesx($display), imagesy($display), MIM_GALLERY_THUMB);

    $extension = function_exists('imagewebp') ? 'webp' : 'jpg';
    $base = sprintf('ed%03d-%s-%s', max(0, $edition), date('Ymd'), bin2hex(random_bytes(4)));
    $file = MIM_GALLERY_DIR . '/' . $base . '.' . $extension;
    $thumbFile = MIM_GALLERY_THUMB_DIR . '/' . $base . '.' . $extension;

    $size = mim_gallery_write_image($display, $file);
    $thumbSize = mim_gallery_write_image($thumb, $thumbFile);
    $displayWidth = imagesx($display);
    $displayHeight = imagesy($display);

    if ($size < 1 || $thumbSize < 1) {
        foreach ([$file, $thumbFile] as $written) {
            if (is_file(MIM_ROOT . '/' . $written)) {
                unlink(MIM_ROOT . '/' . $written);
            }
        }

        return ['Le dossier assets/galerie/ n’est pas accessible en écriture.', []];
    }

    return ['', [
        'file' => $file,
        'edition' => $edition,
        'published' => true,
        'ref' => $ref,
        'credit' => '',
        'taken' => '',
        'alt' => ['fr' => '', 'en' => '', 'es' => '', 'ar' => ''],
        'caption' => ['fr' => '', 'en' => '', 'es' => '', 'ar' => ''],
        'thumb' => $thumbFile,
        'width' => $displayWidth,
        'height' => $displayHeight,
        'size' => $size,
        'thumb_size' => $thumbSize,
        'added' => time(),
    ]];
}

/** Supprime une photo et sa miniature, en restant dans assets/galerie/. */
function mim_gallery_remove(string $file): void
{
    $photo = mim_gallery_data()['photos'][$file] ?? null;

    if ($photo === null) {
        return;
    }

    foreach ([$file, $photo['thumb']] as $target) {
        if ($target === '' || !str_starts_with($target, MIM_GALLERY_DIR . '/')) {
            continue;
        }
        $path = MIM_ROOT . '/' . $target;
        if (is_file($path)) {
            unlink($path);
        }
    }
}
