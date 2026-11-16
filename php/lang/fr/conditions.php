<?php
declare(strict_types=1);

return [
    'title' => 'Conditions générales d’utilisation — Marathon International de Marrakech',
    'description' => 'Conditions applicables à l’inscription et à l’achat d’un droit de départ au Marathon International de Marrakech : vendeur, prix en {billing}, paiement, engagements du coureur, annulation et droit applicable.',
    'og_description' => 'Ce que vous acceptez en achetant un droit de départ : vendeur identifié, prix de référence, modalités de paiement et d’annulation.',

    'head.index' => '{cur} / {total}',
    'head.title' => 'Conditions générales d’utilisation',
    'head.lede' => 'Les présentes conditions encadrent l’achat d’un droit de départ sur le site. Elles disent qui vend, à quel prix, dans quelle devise, et ce que chacun peut attendre de l’autre.',
    'toc.title' => 'Sommaire',

    'next.label' => 'Page suivante · {no}',
    'next.aria' => 'Page suivante',

    'sections' => [
        [
            't' => 'Objet et champ d’application',
            'body' => [
                'En validant une inscription sur le site du Marathon International de Marrakech, le coureur accepte les présentes conditions générales. Elles s’appliquent au marathon, au semi-marathon et à la Course pour Tous, ainsi qu’à tout service payant vendu sur le même panier.',
                'Elles ne remplacent pas le règlement sportif de l’épreuve, qui gouverne le déroulement de la course le jour du départ. Les deux documents sont complémentaires : les conditions régissent l’achat, le règlement régit la course.',
            ],
        ],
        [
            't' => 'Vendeur du droit de départ',
            'body' => [
                'Le droit de départ est vendu par {entity}. Sa référence fiscale et son adresse de correspondance sont {entity_block}. Cette identification est celle que la passerelle de paiement retrouve sur le relevé bancaire du coureur.',
                'Le site est éditorial : il présente l’épreuve et redirige vers l’application d’inscription. Le paiement est traité par un prestataire de paiement tiers ; le site ne conserve aucun numéro de carte.',
            ],
        ],
        [
            't' => 'Partie commerciale et partie associative',
            'body' => [
                'Deux acteurs, deux rôles. {entity} est la structure commerciale : elle encaisse les droits d’inscription, émet les confirmations et traite les demandes de remboursement. {association} est le partenaire sportif, {association_role}.',
                'Le coureur contracte donc avec {entity} pour son achat. Les décisions sportives — parcours, horaires, classement, disqualification — relèvent de l’organisation sportive et de la fédération, pas du vendeur de billets.',
            ],
        ],
        [
            't' => 'Qui peut s’inscrire',
            'body' => [
                'L’inscription est nominative. Elle exige un âge minimal fixé par le règlement de chaque épreuve, une adresse e-mail valide pour la confirmation, et pour les longues distances un certificat médical ou une licence autorisant la pratique de la course en compétition.',
                'Une inscription est incessible : elle ne peut pas être cédée à un tiers, revendue ou échangée entre coureurs. Le dossard est nominatif et peut être retiré en cas de fraude caractérisée.',
            ],
        ],
        [
            't' => 'Prix de référence et devise facturée',
            'body' => [
                'Les droits d’inscription sont fixés et débités en {billing}. Les prix de référence affichés avant le formulaire sont : marathon {price_marathon}, semi-marathon {price_semi}, Course pour Tous {price_tous}.',
                'Ces montants correspondent au tarif de la période en cours. Ils peuvent évoluer jusqu’à la clôture des inscriptions ; seul le montant affiché au moment du paiement définitif engage les deux parties.',
            ],
            'list' => [
                'Ce que le prix inclut : le dossard, la puce de chronométrage, l’accès aux zones de départ et d’arrivée, le ravitaillement sur le parcours, la médaille et le t-shirt officiel.',
                'Ce que le prix n’inclut pas : transport, hébergement, visa, assurance annulation individuelle et toute option payante ajoutée séparément.',
            ],
        ],
        [
            't' => 'Paiement, devises et conversion',
            'body' => [
                'Le paiement est exigible en totalité à l’inscription. Les cartes acceptées sont traitées par le prestataire de paiement choisi ; la transaction est confirmée au moment de l’autorisation.',
                'Pour un paiement en dirham (MAD) ou en euro (EUR), le montant de référence en {billing} est converti au taux appliqué par le réseau de la carte. Source du taux : {fx_source}. Moment de la conversion : {fx_moment}.',
                'Le site n’ajoute aucune commission de change. Le débit réellement constaté sur le relevé bancaire peut néanmoins différer du prix affiché, par l’effet du taux et des frais pratiqués par la banque émettrice.',
            ],
        ],
        [
            't' => 'Confirmation d’inscription',
            'body' => [
                'L’inscription n’est définitive qu’après acceptation du paiement. Le coureur reçoit une confirmation à l’adresse indiquée, qui vaut billet. Une inscription refusée ou un paiement non abouti n’ouvre droit à aucun dossard.',
                'En l’absence de confirmation dans les heures suivant le débit, le coureur écrit à {email_support} avec son nom, la date du paiement et, si possible, la référence de la transaction.',
            ],
        ],
        [
            't' => 'Retrait du dossard',
            'body' => [
                'Le retrait se fait au village, aux créneaux annoncés, sur présentation d’une pièce d’identité et de la confirmation. Aucun dossard n’est remis le jour même pour les longues distances.',
                'Une procuration est acceptée avec une copie de la pièce d’identité du titulaire et du mandataire. Le retrait reste possible sur la période annoncée, dans la limite des dossards disponibles.',
            ],
        ],
        [
            't' => 'Engagements du coureur',
            'body' => [
                'Le coureur déclare être en état de santé compatible avec l’effort demandé, avoir pris connaissance du règlement sportif, accepter les horaires de départ, les fermetures de barrière et les consignes des organisateurs et des autorités.',
                'Il accepte également le port du dossard visible, le chronométrage par puce et le fait que son nom, son âge, son club et son temps soient publiés dans les résultats officiels.',
            ],
        ],
        [
            't' => 'Annulation, remboursement et report',
            'body' => [
                'Les règles de remboursement, de changement d’épreuve et de report d’une édition à l’autre ne sont pas dans ce document : elles forment une politique à part entière, lisible sur la page <a href="remboursement.php">Remboursement et annulation</a>.',
                'Résumé utile avant de payer : la demande de remboursement doit parvenir avant le {refund_deadline}, et le report d’inscription doit être demandé avant le {deferral_deadline}, avec des frais de {deferral_fee}.',
            ],
        ],
        [
            't' => 'Empêchement de l’événement',
            'body' => [
                'Si l’épreuve est annulée par décision des autorités, par force majeure, ou si elle est reportée à une autre date, les coureurs inscrits sont informés par e-mail et reçoivent une proposition de report sur la nouvelle date ou de remboursement, selon la politique annoncée.',
                'Une modification du parcours, des horaires ou d’un format d’épreuve, imposée par la sécurité ou par des contraintes urbaines, ne constitue pas une annulation et n’ouvre pas droit à remboursement dès lors que la course a bien lieu.',
            ],
        ],
        [
            't' => 'Images et données personnelles',
            'body' => [
                'Les données collectées à l’inscription servent à organiser la course, à produire les résultats et à satisfaire aux obligations légales et d’assurance. Leur traitement, leurs destinataires et vos droits sont décrits dans la <a href="confidentialite.php">politique de confidentialité</a>.',
                'Photographies et vidéos réalisées pendant l’événement peuvent être utilisées pour la promotion de l’épreuve. Une opposition est possible à {email_legal}.',
            ],
        ],
        [
            't' => 'Responsabilité',
            'body' => [
                'Le vendeur du droit de départ répond de l’exécution de ses propres obligations : délivrance du dossard, exactitude du prix, traitement des demandes. Il ne répond pas des décisions techniques de l’organisation sportive ni des faits d’un tiers indépendant du contrat.',
                'La perte d’un objet de valeur au vestiaire, un dommage résultant du non-respect du règlement par le coureur, ou une contre-performance sportive, ne sauraient engager le vendeur.',
            ],
        ],
        [
            't' => 'Modification des présentes conditions',
            'body' => [
                'Les conditions peuvent être adaptées pour se conformer à une obligation légale, à une exigence de la passerelle de paiement ou à une évolution du format de l’épreuve. La version applicable est celle affichée le jour du paiement.',
                'Une modification ne remet pas en cause une inscription déjà confirmée, sauf obligation légale contraire.',
            ],
        ],
        [
            't' => 'Droit applicable et réclamation',
            'body' => [
                'Les relations d’achat sont régies par le droit de l’État sous lequel la structure qui facture est enregistrée, sans priver le coureur consommateur des règles impératives de son pays de résidence.',
                'Toute réclamation s’adresse d’abord à {email_support}. À défaut de réponse satisfaisante, le coureur peut saisir un médiateur de la consommation compétent ou la juridiction de son domicile.',
            ],
        ],
        [
            't' => 'Contact',
            'body' => [
                'Assistance inscription et paiement : <a href="mailto:{email_support}">{email_support}</a>. Questions relatives aux données personnelles : <a href="mailto:{email_legal}">{email_legal}</a>. Contact général : <a href="mailto:{email_contact}">{email_contact}</a>.',
                'Adresse postale du vendeur : {address}.',
            ],
        ],
    ],
];
