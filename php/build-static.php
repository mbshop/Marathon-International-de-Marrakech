<?php
declare(strict_types=1);

/*
 * Copie statique du site, pour un hébergement qui n'exécute pas PHP.
 *
 * Le rendu vient des pages PHP : textes, langues et données structurées gardent une seule
 * source de vérité. Le script interroge le serveur de développement puis réécrit les
 * adresses .php?lang=… vers les fichiers produits.
 *
 *   php -S 127.0.0.1:8123 -t .
 *   php build-static.php [--server=http://127.0.0.1:8123] [--origin=https://exemple.com] [--out=../web]
 */

require __DIR__ . '/inc/bootstrap.php';

/* Outil de construction : hors ligne de commande, il n'a rien à répondre au visiteur. */
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$options = getopt('', ['server::', 'origin::', 'out::']);

$server = rtrim((string) ($options['server'] ?? 'http://127.0.0.1:8123'), '/');
$origin = rtrim((string) ($options['origin'] ?? 'https://marrakech-marathon-9lspqlyyj34.qoder.website'), '/');
$outDir = (string) ($options['out'] ?? dirname(__DIR__) . '/web');

if (!is_dir($outDir)) {
    fwrite(STDERR, "Dossier de sortie absent : $outDir\n");
    exit(1);
}

if (!str_starts_with($server, 'http://') && !str_starts_with($server, 'https://')) {
    fwrite(STDERR, "--server doit être une adresse http:// ou https://\n");
    exit(1);
}

$registrationUrl = mim_site()['registration_url'];
$locales = array_keys(MIM_LOCALES);

/** Scripts publics, menu et pages légales, dans l'ordre du plan de site. */
$scripts = [];
foreach (MIM_NAV as $key) {
    $scripts[$key . '.php'] = $key;
}
foreach (mim_legal_pages() as $key => $script) {
    $scripts[$script] = $key;
}

/**
 * Une page par langue, dans un seul dossier : français sans suffixe pour ne pas casser
 * les adresses déjà partagées, langue ajoutée pour les trois autres.
 */
$staticName = static function (string $script, string $locale) use ($locales): string {
    $base = basename($script, '.php');

    return $locale === $locales[0] ? $base . '.html' : $base . '--' . $locale . '.html';
};

$fetch = static function (string $url, ?string $cookie) use ($server): string {
    $headers = ['Accept: text/html', 'User-Agent: MIM-Build-Static'];
    if ($cookie !== null) {
        $headers[] = 'Cookie: ' . $cookie;
    }

    $body = @file_get_contents($url, false, stream_context_create([
        'http' => [
            'header' => implode("\r\n", $headers),
            'ignore_errors' => true,
            'timeout' => 30,
        ],
    ]));

    if ($body === false) {
        throw new RuntimeException("Le serveur de développement n'a pas répondu : $url\nLancez d'abord : php -S 127.0.0.1:8123 -t .");
    }

    $status = 0;
    foreach ($http_response_header ?? [] as $line) {
        if (preg_match('#^HTTP/\S+\s+(\d{3})#', $line, $match)) {
            $status = (int) $match[1];
        }
    }

    if ($status !== 200) {
        throw new RuntimeException("$url a répondu HTTP $status.");
    }

    foreach (['Fatal error', 'Warning:', 'Deprecated:', 'Notice:'] as $needle) {
        if (str_contains($body, $needle)) {
            throw new RuntimeException("$url contient « $needle » : le rendu PHP est cassé.");
        }
    }

    return $body;
};

/**
 * Adresses du site PHP -> adresses de la copie statique.
 *
 * @param string $locale Langue de la page en cours de réécriture : un lien interne sans
 *                       langue doit rester dans la langue du visiteur, alors qu'une URL
 *                       absolue « x-default » désigne la version de référence.
 */
$rewrite = static function (string $html, string $locale, bool $isPage) use (
    $server,
    $origin,
    $staticName,
    $registrationUrl,
    $locales
): string {
    /* 1. Le rendu local ignore le domaine publié : on remonte les URL absolues. */
    $html = str_replace($server, $origin, $html);
    if (!$isPage) {
        /* 2. robots.txt et sitemap.xml n'ont que des URL absolues à corriger. */
        $html = preg_replace_callback(
            '#(?:' . preg_quote($origin, '#') . ')/([a-z][a-z0-9_-]*)\.php\?lang=([a-z]{2})#',
            static function (array $match) use ($origin, $staticName): string {
                return $origin . '/' . $staticName($match[1] . '.php', $match[2]);
            },
            $html
        );

        return preg_replace_callback(
            '#(?:' . preg_quote($origin, '#') . ')/([a-z][a-z0-9_-]*)\.php#',
            static function (array $match) use ($origin, $staticName, $locales): string {
                return $origin . '/' . $staticName($match[1] . '.php', $locales[0]);
            },
            $html
        );
    }

    /*
     * 3. Le proxy d'inscription est un script PHP : le cadre pointe sur l'application elle-même.
     *    Hors de la même origine, la hauteur ne peut plus être reprise au contenu, le cadre
     *    garde donc sa taille et laisse défiler le formulaire à l'intérieur.
     */
    if ($registrationUrl !== '') {
        $html = str_replace('src="reg-app.php"', 'src="' . e($registrationUrl) . '"', $html);
        $html = str_replace(' scrolling="no"', '', $html);
    }

    /* 4. Le back-office n'a rien à faire dans une copie publique. */
    $html = preg_replace('#<a class="foot-top" href="adminbtx\.php">.*?</a>#s', '', $html);

    /* 5. Le sélecteur de langue part de SCRIPT_NAME, donc de la racine du serveur : la copie
     *    se feuillette depuis son propre dossier, les chemins deviennent relatifs. */
    $html = str_replace(['href="/', 'src="/'], ['href="', 'src="'], $html);

    /* 6. Liens avec langue explicite (sélecteur, hreflang, canonical). */
    $html = preg_replace_callback(
        '#(?<![A-Za-z0-9_-])([a-z][a-z0-9_-]*)\.php\?lang=([a-z]{2})#',
        static function (array $match) use ($staticName, $locales): string {
            if (!in_array($match[2], $locales, true)) {
                return $match[0];
            }

            return $staticName($match[1] . '.php', $match[2]);
        },
        $html
    );

    /* 7. URL absolues sans langue : la version de référence, celle du français. */
    $html = preg_replace_callback(
        '#(' . preg_quote($origin, '#') . ')/([a-z][a-z0-9_-]*)\.php#',
        static function (array $match) use ($origin, $staticName, $locales): string {
            return $origin . '/' . $staticName($match[2] . '.php', $locales[0]);
        },
        $html
    );

    /* 8. Liens internes relatifs : ils restent dans la langue de la page. */
    $html = preg_replace_callback(
        '#(?<![\w/.-])([a-z][a-z0-9_-]*)\.php#',
        static function (array $match) use ($staticName, $locale): string {
            return $staticName($match[1] . '.php', $locale);
        },
        $html
    );

    /* 9. La copie statique ne pose pas de cookie : un script la remplace. */
    $html = str_replace(
        '</body>',
        '    <script src="static-i18n.js" defer></script>' . "\n  </body>",
        $html
    );

    return $html;
};

$written = [];

foreach ($scripts as $script => $key) {
    foreach ($locales as $locale) {
        $isEntry = $script === 'index.php' && $locale === $locales[0];
        /*
         * Le sélecteur de première visite disparaît dès qu'une langue est demandée, en cookie
         * ou en paramètre : seule l'entrée du site est donc rendue sans rien demander d'autre.
         */
        $html = $isEntry
            ? $fetch($server . '/' . $script, null)
            : $fetch($server . '/' . $script . '?lang=' . $locale, 'mim_lang=' . $locale);
        $name = $staticName($script, $locale);
        file_put_contents($outDir . '/' . $name, $rewrite($html, $locale, true));
        $written[] = $name;
    }
}

/* Le plan de site vient du générateur PHP, avec les mêmes réécritures que les pages. */
file_put_contents($outDir . '/sitemap.xml', $rewrite($fetch($server . '/sitemap.php', null), $locales[0], false));

file_put_contents(
    $outDir . '/robots.txt',
    "User-agent: *\nAllow: /\n\nSitemap: $origin/sitemap.xml\n"
);

file_put_contents($outDir . '/static-i18n.js', <<<'JS'
/*
 * Choix de langue d'une copie sans serveur : la préférence est gardée dans localStorage,
 * le sélecteur de première visite ne se montre qu'à l'entrée et un retour sur index.html
 * repart dans la langue retenue.
 */
(function () {
  var KEY = "mim_lang";
  var file = location.pathname.split("/").pop() || "index.html";
  var suffixed = /^(.+)--(fr|en|ar|es)\.html$/.exec(file);
  var here = suffixed ? suffixed[2] : "fr";

  function read() {
    try {
      var saved = (localStorage.getItem(KEY) || "").toLowerCase();
      return /^(fr|en|ar|es)$/.test(saved) ? saved : "";
    } catch (error) {
      return "";
    }
  }

  function save(code) {
    try {
      localStorage.setItem(KEY, code);
    } catch (error) {}
  }

  document.addEventListener("click", function (event) {
    var link = event.target && event.target.closest ? event.target.closest(".lang-switch a, .lang-gate-option") : null;
    if (link && link.getAttribute("lang")) {
      save(link.getAttribute("lang"));
    }
  });

  var saved = read();

  if (saved !== "" && saved !== here && file === "index.html") {
    location.replace("index--" + saved + ".html");
    return;
  }

  if (saved !== "") {
    var gate = document.querySelector(".lang-gate");
    if (gate) {
      gate.remove();
    }
  }
})();
JS);

/* Les fichiers partagés : feuilles de style, script et médias. */
$copy = static function (string $from, string $to): int {
    $count = 0;
    if (!is_dir($from)) {
        return 0;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($from, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $item) {
        $target = $to . '/' . $iterator->getSubPathname();
        if ($item->isDir()) {
            if (!is_dir($target)) {
                mkdir($target, 0775, true);
            }

            continue;
        }

        $dir = dirname($target);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        copy($item->getPathname(), $target);
        $count++;
    }

    return $count;
};

foreach (['styles.css', 'app.js'] as $file) {
    copy(__DIR__ . '/' . $file, $outDir . '/' . $file);
    $written[] = $file;
}

$mediaCopied = $copy(__DIR__ . '/assets', $outDir . '/assets');

printf("%d pages écrites dans %s\n", count($written), $outDir);
printf("médias copiés : %d, sitemap.xml, robots.txt, static-i18n.js\n", $mediaCopied);
