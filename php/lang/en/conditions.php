<?php
declare(strict_types=1);

return [
    'title' => 'Terms of Use — Marathon International de Marrakech',
    'description' => 'Conditions applying to registration and to the purchase of a race entry at the Marathon International de Marrakech: seller, prices in {billing}, payment, runner obligations, cancellation and applicable law.',
    'og_description' => 'What you accept when buying a race entry: identified seller, reference prices, payment and cancellation terms.',

    'head.index' => '{cur} / {total}',
    'head.title' => 'Terms of Use',
    'head.lede' => 'These terms govern the purchase of a race entry on the site. They say who sells, at what price, in which currency, and what each party can expect from the other.',
    'toc.title' => 'Contents',

    'next.label' => 'Next page · {no}',
    'next.aria' => 'Next page',

    'sections' => [
        [
            't' => 'Purpose and scope',
            'body' => [
                'By completing a registration on the Marathon International de Marrakech website, the runner accepts these terms of use. They apply to the marathon, the half marathon and the Fun Run, as well as to any paid service sold in the same basket.',
                'They do not replace the sporting regulations of the event, which govern how the race is run on the day. The two documents complement each other: the terms govern the purchase, the regulations govern the race.',
            ],
        ],
        [
            't' => 'Seller of the race entry',
            'body' => [
                'The race entry is sold by {entity}. Its tax reference and postal address are {entity_block}. This is the identity the payment gateway shows on the runner’s bank statement.',
                'The website is editorial: it presents the event and redirects to the registration application. Payment is processed by a third-party payment provider; the site stores no card numbers.',
            ],
        ],
        [
            't' => 'The commercial party and the sporting party',
            'body' => [
                'Two actors, two roles. {entity} is the commercial structure: it collects the entry fees, issues the confirmations and handles refund requests. {association} is the sporting partner, {association_role}.',
                'The runner therefore contracts with {entity} for the purchase. Sporting decisions — course, start times, results, disqualification — rest with the sports organisation and the federation, not with the seller of entries.',
            ],
        ],
        [
            't' => 'Who can register',
            'body' => [
                'Registration is personal. It requires a minimum age set by each event’s regulations, a valid e-mail address for the confirmation, and, for the long distances, a medical certificate or a licence authorising competitive running.',
                'An entry is non-transferable: it cannot be passed to a third party, resold or exchanged between runners. The bib carries the runner’s name and may be withdrawn in cases of proven fraud.',
            ],
        ],
        [
            't' => 'Reference prices and billing currency',
            'body' => [
                'Entry fees are set and charged in {billing}. The reference prices displayed before the form are: marathon {price_marathon}, half marathon {price_semi}, Fun Run {price_tous}.',
                'These amounts correspond to the rate of the current period. They may change until registration closes; only the amount shown at the time of final payment binds both parties.',
            ],
            'list' => [
                'What the price includes: the bib, the timing chip, access to the start and finish areas, refreshments along the course, the medal and the official t-shirt.',
                'What the price does not include: transport, accommodation, visa, personal cancellation insurance and any paid option added separately.',
            ],
        ],
        [
            't' => 'Payment, currencies and conversion',
            'body' => [
                'Payment is due in full at registration. Accepted cards are processed by the chosen payment provider; the transaction is confirmed when the authorisation is granted.',
                'For a payment in dirhams (MAD) or euros (EUR), the reference amount in {billing} is converted at the rate applied by the card network. Rate source: {fx_source}. Moment of conversion: {fx_moment}.',
                'The site adds no currency conversion fee. The debit actually recorded on the bank statement may nonetheless differ from the displayed price, as a result of the exchange rate and the fees charged by the issuing bank.',
            ],
        ],
        [
            't' => 'Registration confirmation',
            'body' => [
                'A registration is only final once the payment has been accepted. The runner receives a confirmation at the address given, which serves as the ticket. A refused registration or a failed payment gives no right to a bib.',
                'If no confirmation arrives within the hours following the debit, the runner writes to {email_support} with their name, the payment date and, if possible, the transaction reference.',
            ],
        ],
        [
            't' => 'Bib collection',
            'body' => [
                'Collection takes place at the village, during the announced slots, on presentation of photo ID and the confirmation. No bib is issued on the day itself for the long distances.',
                'A proxy is accepted with a copy of the ID of both the holder and the proxy. Collection remains possible throughout the announced period, while bibs last.',
            ],
        ],
        [
            't' => 'Runner obligations',
            'body' => [
                'The runner declares being in a state of health compatible with the effort required, having read the sporting regulations, and accepting the start times, cut-off times and the instructions of the organisers and the authorities.',
                'The runner also accepts wearing the bib visibly, chip timing, and the publication of name, age, club and time in the official results.',
            ],
        ],
        [
            't' => 'Cancellation, refunds and deferral',
            'body' => [
                'The rules for refunds, changing event and deferral from one edition to the next are not in this document: they form a policy in its own right, set out on the <a href="remboursement.php">Refunds and cancellation</a> page.',
                'A useful summary before paying: a refund request must arrive before {refund_deadline}, and a deferral must be requested before {deferral_deadline}, with a fee of {deferral_fee}.',
            ],
        ],
        [
            't' => 'When the event cannot go ahead',
            'body' => [
                'If the event is cancelled by decision of the authorities or by force majeure, or if it is postponed to another date, registered runners are informed by e-mail and receive a proposal to defer to the new date or to be refunded, according to the policy announced.',
                'A change to the course, the start times or an event format, imposed by safety or urban constraints, does not constitute a cancellation and gives no right to a refund as long as the race actually takes place.',
            ],
        ],
        [
            't' => 'Images and personal data',
            'body' => [
                'The data collected at registration is used to organise the race, produce the results and meet legal and insurance obligations. Its processing, its recipients and your rights are described in the <a href="confidentialite.php">privacy policy</a>.',
                'Photographs and video taken during the event may be used to promote it. An objection can be raised at {email_legal}.',
            ],
        ],
        [
            't' => 'Liability',
            'body' => [
                'The seller of the entry is answerable for the performance of its own obligations: issuing the bib, accuracy of the price, handling of requests. It is not answerable for the technical decisions of the sports organisation or for the acts of a third party independent of the contract.',
                'The loss of a valuable item in the cloakroom, damage resulting from the runner’s failure to follow the regulations, or an underwhelming race performance cannot engage the seller’s liability.',
            ],
        ],
        [
            't' => 'Changes to these terms',
            'body' => [
                'The terms may be adapted to comply with a legal obligation, a requirement of the payment gateway or an evolution of the event format. The applicable version is the one displayed on the day of payment.',
                'A change does not affect a registration already confirmed, unless a legal obligation requires otherwise.',
            ],
        ],
        [
            't' => 'Applicable law and complaints',
            'body' => [
                'Purchase relations are governed by the law of the state under which the invoicing structure is registered, without depriving the runner, as a consumer, of the mandatory rules of their country of residence.',
                'Any complaint should first be addressed to {email_support}. Failing a satisfactory response, the runner may refer the matter to a competent consumer mediator or to the courts of their home.',
            ],
        ],
        [
            't' => 'Contact',
            'body' => [
                'Registration and payment assistance: <a href="mailto:{email_support}">{email_support}</a>. Questions about personal data: <a href="mailto:{email_legal}">{email_legal}</a>. General enquiries: <a href="mailto:{email_contact}">{email_contact}</a>.',
                'Postal address of the seller: {address}.',
            ],
        ],
    ],
];
