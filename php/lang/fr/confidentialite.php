<?php
declare(strict_types=1);

return [
    'title' => 'Politique de confidentialité — Marathon International de Marrakech',
    'description' => 'Données collectées à l’inscription au Marathon International de Marrakech, finalités, bases légales, destinataires, durées de conservation, transferts hors Union européenne et droits du coureur.',
    'og_description' => 'Ce que nous conservons quand vous vous inscrivez, pourquoi, pendant combien de temps, et comment exercer vos droits.',

    'head.index' => '{cur} / {total}',
    'head.title' => 'Politique de confidentialité',
    'head.lede' => 'Une inscription à une course implique des données inhabituelles : identité, âge, club, performance, et souvent un certificat médical. Cette page dit précisément ce qui est gardé, par qui, et pour quoi faire.',
    'toc.title' => 'Sommaire',

    'next.label' => 'Page suivante · {no}',
    'next.aria' => 'Page suivante',

    'sections' => [
        [
            't' => 'Responsable du traitement',
            'body' => [
                'Les données d’inscription sont traitées par {entity}, structure qui commercialise les droits de départ, adresse : {address}. Pour toute question sur la protection des données : <a href="mailto:{email_legal}">{email_legal}</a>.',
                'L’organisation sportive, conduite avec {association}, reçoit les données strictement nécessaires à la course : liste de départ, catégories d’âge, résultats, contacts d’urgence.',
            ],
        ],
        [
            't' => 'Données collectées',
            'body' => [
                'Le formulaire d’inscription demande les données utiles à la course et à la sécurité du coureur. Rien au-delà n’est collecté à cette étape.',
            ],
            'list' => [
                'Identité et profil : nom, prénom, date de naissance, sexe, nationalité, club, ville et pays de résidence.',
                'Contact : adresse e-mail et téléphone, pour la confirmation, les instructions de retrait et l’alerte en cas d’empêchement.',
                'Sportif : épreuve choisie, temps de référence éventuel, taille et pointure pour le t-shirt, numéro de puce.',
                'Santé et sécurité : certificat médical ou licence en cours de validité pour les longues distances, contact d’urgence, informations médicales utiles en cas de secours.',
                'Paiement : montant, devise, date, référence de transaction et nom figurant sur le titre de paiement. Les numéros de carte ne transitent ni ne sont stockés par le site.',
            ],
        ],
        [
            't' => 'Finalités et bases légales',
            'body' => [
                'Exécuter le contrat d’inscription : confirmation, attribution du dossard, chronométrage, remise des résultats, gestion du vestiaire. C’est la base principale pour la quasi-totalité des données ci-dessus.',
                'Respecter une obligation légale et réglementaire : vérification de la certification médicale exigée pour la participation, conservation des pièces pour l’assurance et les autorités, établissement des classements officiels.',
                'Intérêt légitime : informer le coureur des informations pratiques de l’édition en cours, prévenir un incident, lutter contre la revente illicite de dossards.',
                'Consentement : communications commerciales après l’édition en cours, et dépôt des cookies de mesure d’audience non essentiels. Ce consentement se retire à tout moment.',
            ],
        ],
        [
            't' => 'Destinataires',
            'body' => [
                'Les données ne sont pas vendues. Elles sont partagées avec les seuls intervenants nécessaires au déroulement de l’épreuve et à l’encaissement.',
            ],
            'list' => [
                'Prestataire de paiement : traite la transaction en {billing} et communique la confirmation de débit.',
                'Chronométreur officiel et gestionnaire de la base de dossards.',
                'Assureur de l’événement et, en cas d’incident, services de secours et autorités.',
                'Fédération et organisateur sportif, {association}, pour la partie compétition.',
                'Prestataires techniques du site, dans la limite de leur mission et sur instruction.',
            ],
        ],
        [
            't' => 'Durées de conservation',
            'body' => [
                'Identité, résultats et historique d’inscription sont conservés le temps nécessaire à l’organisation des éditions suivantes et à la défense des droits en justice, soit cinq ans à compter de la fin de l’édition concernée.',
                'Les justificatifs médicaux sont conservés pour la seule durée des obligations légales et d’assurance, puis détruits. Les données strictement nécessaires à la comptabilité suivent l’obligation légale de conservation des pièces comptables.',
                'Une demande d’effacement anticipée est traitée dans les limites que permettent ces obligations : le classement sportif, fait historique de la course, peut subsister sous forme anonymisée.',
            ],
        ],
        [
            't' => 'Transferts hors Union européenne',
            'body' => [
                'La structure qui facture, le prestataire de paiement et certains hébergeurs sont établis aux États-Unis. Un transfert hors de l’Espace économique européen est donc inhérent à l’inscription.',
                'Ces transferts reposent sur les clauses contractuelles types de la Commission européenne et sur les garanties offertes par le réseau de paiement. Une copie des garanties peut être demandée à <a href="mailto:{email_legal}">{email_legal}</a>.',
            ],
        ],
        [
            't' => 'Vos droits',
            'body' => [
                'Vous pouvez demander l’accès à vos données, leur rectification, leur effacement, la limitation de leur traitement, leur portabilité, et vous opposer aux traitements fondés sur l’intérêt légitime ou retirer un consentement.',
                'La demande s’adresse à <a href="mailto:{email_legal}">{email_legal}</a>, avec un justificatif d’identité. Elle est traitée dans un délai d’un mois, prolongeable de deux mois en cas de demande complexe.',
                'Si la réponse ne convient pas, une réclamation peut être déposée auprès de l’autorité de protection des données compétente : la CNDP au Maroc, ou la CNIL dans l’Union européenne pour un résident européen.',
            ],
        ],
        [
            't' => 'Cookies et mesure d’audience',
            'body' => [
                'Le site dépose un cookie de préférence linguistique, <code>mim_lang</code>, qui mémorise la langue choisie lors de la première visite pendant douze mois. Il ne contient aucune donnée personnelle et n’est pas utilisé à des fins publicitaires.',
                'Aucun cookie publicitaire ni traceur tiers n’est déposé sans consentement préalable. Une mesure d’audience, si elle est mise en œuvre, l’est avec des adresses IP tronquées et une durée de conservation courte.',
            ],
        ],
        [
            't' => 'Sécurité',
            'body' => [
                'L’accès aux données d’inscription est restreint aux personnes qui en ont besoin pour leur mission. Le site est servi en HTTPS, les espaces d’administration sont protégés par mot de passe haché et limitation des tentatives, et les sauvegardes sont chiffrées.',
                'Le paiement lui-même est hors de portée du site : il est opéré par le prestataire de paiement, qui lui répond de la sécurité de cette étape.',
            ],
        ],
        [
            't' => 'Mineurs',
            'body' => [
                'Les épreuves ouvertes aux mineurs supposent l’accord du représentant légal, qui fournit ses coordonnées et le certificat exigé. Les données d’un mineur ne font l’objet d’aucune communication commerciale.',
            ],
        ],
        [
            't' => 'Évolution de cette politique',
            'body' => [
                'Cette politique peut être modifiée pour tenir compte d’une évolution légale, d’un nouveau prestataire ou d’un changement du format de l’épreuve. La date de dernière mise à jour figure en tête de page.',
            ],
        ],
    ],
];
