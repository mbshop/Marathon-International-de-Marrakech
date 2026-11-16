<?php
declare(strict_types=1);

return [
    'title' => 'Privacy Policy — Marathon International de Marrakech',
    'description' => 'Data collected when registering for the Marathon International de Marrakech: purposes, lawful bases, recipients, retention periods, transfers outside the European Union and runner rights.',
    'og_description' => 'What we keep when you register, why, for how long, and how to exercise your rights.',

    'head.index' => '{cur} / {total}',
    'head.title' => 'Privacy Policy',
    'head.lede' => 'Registering for a race involves unusual data: identity, age, club, performance, and often a medical certificate. This page says precisely what is kept, by whom, and for what purpose.',
    'toc.title' => 'Contents',

    'next.label' => 'Next page · {no}',
    'next.aria' => 'Next page',

    'sections' => [
        [
            't' => 'Data controller',
            'body' => [
                'Registration data is processed by {entity}, the structure that commercialises the race entries, address: {address}. For any question about data protection: <a href="mailto:{email_legal}">{email_legal}</a>.',
                'The sports organisation, run together with {association}, receives the data strictly necessary for the race: start list, age categories, results, emergency contacts.',
            ],
        ],
        [
            't' => 'Data collected',
            'body' => [
                'The registration form asks for the data useful to the race and to the runner’s safety. Nothing beyond this is collected at this stage.',
            ],
            'list' => [
                'Identity and profile: last name, first name, date of birth, sex, nationality, club, city and country of residence.',
                'Contact: e-mail address and phone number, for the confirmation, the collection instructions and alerts if the event is disrupted.',
                'Sporting: chosen event, reference time if any, size and shoe size for the t-shirt, chip number.',
                'Health and safety: for the long distances, a medical certificate or licence still valid, an emergency contact, and any medical information useful to responders.',
                'Payment: amount, currency, date, transaction reference and the name appearing on the payment instrument. Card numbers neither pass through nor are stored by the site.',
            ],
        ],
        [
            't' => 'Purposes and lawful bases',
            'body' => [
                'Performing the registration contract: confirmation, bib assignment, timing, delivery of the results, cloakroom management. This is the main basis for almost all of the data above.',
                'Complying with a legal and regulatory obligation: checking the medical certification required to take part, keeping records for the insurer and the authorities, producing the official rankings.',
                'Legitimate interest: informing the runner of the practical information for the current edition, preventing an incident, and combating the illicit resale of bibs.',
                'Consent: marketing communications after the current edition, and the placement of non-essential audience-measurement cookies. This consent can be withdrawn at any time.',
            ],
        ],
        [
            't' => 'Recipients',
            'body' => [
                'The data is not sold. It is shared only with the parties needed to run the event and to take payment.',
            ],
            'list' => [
                'Payment provider: processes the transaction in {billing} and returns the debit confirmation.',
                'Official timekeeper and manager of the bib database.',
                'Event insurer and, in case of incident, emergency services and the authorities.',
                'Federation and sports organiser, {association}, for the competition side.',
                'Technical providers of the site, within the limits of their task and on instruction.',
            ],
        ],
        [
            't' => 'Retention periods',
            'body' => [
                'Identity, results and registration history are kept for as long as needed to organise future editions and to defend rights in court, that is five years from the end of the edition concerned.',
                'Medical records are kept only for the duration of legal and insurance obligations, then destroyed. Data strictly necessary for accounting follows the legal retention obligation for accounting records.',
                'An early erasure request is handled within the limits these obligations allow: the race results, a historical fact of the event, may remain in anonymised form.',
            ],
        ],
        [
            't' => 'Transfers outside the European Union',
            'body' => [
                'The structure that issues the invoice, the payment provider and some hosting providers are established in the United States. A transfer outside the European Economic Area is therefore inherent to registration.',
                'These transfers rely on the standard contractual clauses of the European Commission and on the safeguards offered by the payment network. A copy of the safeguards can be requested from <a href="mailto:{email_legal}">{email_legal}</a>.',
            ],
        ],
        [
            't' => 'Your rights',
            'body' => [
                'You may request access to your data, its rectification, its erasure, the restriction of its processing, its portability, object to processing based on legitimate interest, and withdraw a consent.',
                'The request goes to <a href="mailto:{email_legal}">{email_legal}</a>, with proof of identity. It is handled within one month, extendable by two months in the case of a complex request.',
                'If the response is not satisfactory, a complaint may be lodged with the competent data protection authority: the CNDP in Morocco, or the CNIL in the European Union for a European resident.',
            ],
        ],
        [
            't' => 'Cookies and audience measurement',
            'body' => [
                'The site places a single language-preference cookie, <code>mim_lang</code>, which remembers the language chosen at the first visit for twelve months. It contains no personal data and is not used for advertising.',
                'No advertising cookie or third-party tracker is placed without prior consent. Audience measurement, if implemented, uses truncated IP addresses and a short retention period.',
            ],
        ],
        [
            't' => 'Security',
            'body' => [
                'Access to registration data is restricted to the people who need it for their task. The site is served over HTTPS, administration areas are protected by hashed passwords and attempt limits, and backups are encrypted.',
                'Payment itself is out of the site’s reach: it is carried out by the payment provider, which is answerable for the security of that step.',
            ],
        ],
        [
            't' => 'Minors',
            'body' => [
                'Events open to minors require the agreement of the legal representative, who provides their contact details and the certificate required. A minor’s data is never used for marketing communications.',
            ],
        ],
        [
            't' => 'Changes to this policy',
            'body' => [
                'This policy may be modified to reflect a change in the law, a new provider or a change to the format of the event. The date of the last update appears at the top of the page.',
            ],
        ],
    ],
];
