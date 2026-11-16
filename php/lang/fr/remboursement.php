<?php
declare(strict_types=1);

return [
    'title' => 'Remboursement, annulation et report — Marathon International de Marrakech',
    'description' => 'Conditions de remboursement d’une inscription au Marathon International de Marrakech : date limite, frais conservés, report d’une édition à l’autre, changement d’épreuve et annulation de l’événement.',
    'og_description' => 'Ce qui est remboursé, jusqu’à quand, à quelles conditions, et ce qui est reportable d’une édition à l’autre.',

    'head.index' => '{cur} / {total}',
    'head.title' => 'Remboursement et annulation',
    'head.lede' => 'Un droit de départ n’est pas un bien comme un autre : il réserve un dossard nominatif, un ravitaillement et une place dans un flux de coureurs. Les règles ci-dessous disent ce qui peut être rendu, reporté ou refusé.',
    'toc.title' => 'Sommaire',

    'next.label' => 'Page suivante · {no}',
    'next.aria' => 'Retour aux inscriptions',

    'sections' => [
        [
            't' => 'Principe',
            'body' => [
                'Une inscription confirmée est ferme. Elle peut néanmoins être remboursée si la demande parvient avant la date annoncée ci-dessous, et reportée sur l’édition suivante dans les mêmes conditions de date.',
                'Date limite de demande de remboursement : {refund_deadline}. Date limite de demande de report : {deferral_deadline}. Frais applicables au report : {deferral_fee}.',
            ],
        ],
        [
            't' => 'Ce que le remboursement couvre',
            'body' => [
                'Le remboursement porte sur le droit de départ tel qu’il a été débité en {billing}. Sont en revanche conservés les frais déjà exposés pour le compte du coureur et non récupérables : confection du t-shirt et du dossard nominatifs, gravage de la médaille, commission du prestataire de paiement.',
                'Un remboursement est effectué sur le moyen de paiement utilisé à l’inscription, jamais en espèces ni sur un autre compte. Le délai de réapparition sur le relevé dépend de la banque émettrice, généralement une à deux cycles.',
            ],
        ],
        [
            't' => 'Comment demander',
            'body' => [
                'La demande s’écrit à <a href="mailto:{email_support}">{email_support}</a> depuis l’adresse indiquée lors de l’inscription. Elle indique le nom du titulaire, l’épreuve, la date du paiement et, si elle est disponible, la référence de transaction.',
                'Une réponse est apportée dans les dix jours ouvrés. Un accusé de réception est envoyé dès l’enregistrement de la demande. Sans réponse dans ce délai, le coureur peut relancer en précisant la date du premier message.',
            ],
        ],
        [
            't' => 'Report d’une édition à l’autre',
            'body' => [
                'Le report conserve l’inscription sur l’édition suivante, sans nouvelle procédure d’admission. Il est demandé avant le {deferral_deadline} et donne lieu à des frais de {deferral_fee} destinés à couvrir la gestion du dossier.',
                'Le report est nominatif et ne peut pas être cédé à une autre personne. Il n’est pas cumulable avec un remboursement : le coureur choisit l’une des deux options.',
                'En cas de blessure ou de maladie empêchant la participation, un report est accepté sur présentation d’un justificatif médical, même après la date limite, dans la mesure où le dossard n’a pas été retiré.',
            ],
        ],
        [
            't' => 'Changement d’épreuve',
            'body' => [
                'Passer du marathon au semi-marathon, ou du semi-marathon à la Course pour Tous, est possible tant que les dossards de la nouvelle épreuve ne sont pas épuisés. Un complément est demandé si la nouvelle épreuve coûte plus cher ; l’écart est remboursé dans les conditions du paragraphe précédent si elle coûte moins cher.',
                'Le changement de distance ne change pas la date limite applicable : c’est toujours la date de la première inscription qui fait référence.',
            ],
        ],
        [
            't' => 'Refus d’inscription et retrait du dossard',
            'body' => [
                'Une inscription peut être refusée ou un dossard retiré sans indemnité en cas de dossier médical non conforme, de fraude, de revente de dossard, ou de comportement ayant déjà valu une sanction disciplinaire.',
                'Le droit de départ est alors intégralement restitué, la sanction portant sur la participation et non sur le prix payé, sauf lorsque le contrat a déjà été partiellement exécuté.',
            ],
        ],
        [
            't' => 'Annulation ou report de l’épreuve',
            'body' => [
                'Si l’épreuve n’a pas lieu — décision des autorités, force majeure, impossibilité sanitaire — chaque coureur inscrit reçoit une proposition de report sur la date suivante ou de remboursement du droit de départ. Le choix est laissé au coureur pendant une période annoncée, généralement trente jours.',
                'Si l’épreuve est annulée après retrait des dossards ou après le départ de l’une des courses, les frais déjà engagés pour le compte du coureur restent acquis à l’organisation et le remboursement est partiel, sur justification.',
                'Un changement de parcours, d’horaire de départ ou de format imposé par la sécurité ou par des contraintes urbaines n’est pas une annulation, dès lors que la course a bien lieu.',
            ],
        ],
        [
            't' => 'Non-partance le jour de la course',
            'body' => [
                'Ne pas se présenter au départ, abandonner en course ou être disqualifié ne donne droit à aucun remboursement : le dossard, la puce, le ravitaillement et la médaille réservée ont été mis à disposition.',
                'Un retrait du dossard vaut participation réservée : le remboursement d’une inscription dont le dossard a été retiré n’est pas possible.',
            ],
        ],
        [
            't' => 'Absence de droit de rétractation',
            'body' => [
                'L’inscription à une événement sportive datée est un contrat de service fourni à une date précise. Elle entre à ce titre dans les exceptions au droit de rétractation de quatorze jours prévu pour les ventes à distance : le délai de remboursement indiqué ci-dessus se substitue à ce droit.',
                'Cette règle ne fait pas obstacle aux remboursements volontaires accordés avant la date limite, ni aux reports acceptés sur justificatif.',
            ],
        ],
        [
            't' => 'Assurance annulation',
            'body' => [
                'Une assurance annulation, si elle est proposée en option lors du panier, est souscrite auprès d’un assureur tiers. Ses garanties, ses franchises et sa procédure de déclaration lui sont propres et ne suivent pas la présente politique.',
                'Le site indique clairement à la souscription le nom de l’assureur et le coût séparé de cette option, qui n’est pas comprise dans le prix de référence de l’inscription.',
            ],
        ],
        [
            't' => 'Litiges',
            'body' => [
                'Toute difficulté s’adresse d’abord à <a href="mailto:{email_support}">{email_support}</a>. À défaut de solution amiable, le coureur consommateur peut saisir un médiateur compétent ou la juridiction de son domicile, conformément aux règles impératives de son pays de résidence.',
            ],
        ],
    ],
];
