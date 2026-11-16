<?php
declare(strict_types=1);

return [
    'title' => 'Refunds, Cancellation and Deferral — Marathon International de Marrakech',
    'description' => 'Conditions for refunding a registration at the Marathon International de Marrakech: deadline, fees retained, deferral from one edition to the next, changing event and cancellation of the event.',
    'og_description' => 'What is refundable, until when, on what conditions, and what can be deferred from one edition to the next.',

    'head.index' => '{cur} / {total}',
    'head.title' => 'Refunds and Cancellation',
    'head.lede' => 'A race entry is not a product like any other: it reserves a personalised bib, refreshments and a place in the flow of runners. The rules below say what can be returned, deferred or refused.',
    'toc.title' => 'Contents',

    'next.label' => 'Next page · {no}',
    'next.aria' => 'Back to registration',

    'sections' => [
        [
            't' => 'Principle',
            'body' => [
                'A confirmed registration is firm. It can nevertheless be refunded if the request arrives before the date announced below, and deferred to the next edition under the same date conditions.',
                'Refund request deadline: {refund_deadline}. Deferral request deadline: {deferral_deadline}. Fee applying to a deferral: {deferral_fee}.',
            ],
        ],
        [
            't' => 'What the refund covers',
            'body' => [
                'The refund covers the race entry as it was charged in {billing}. Retained, on the other hand, are the costs already incurred on the runner’s behalf and not recoverable: making of the personalised t-shirt and bib, engraving of the medal, the payment provider’s commission.',
                'A refund is made to the payment method used at registration, never in cash or to another account. The time for it to reappear on the statement depends on the issuing bank, typically one to two billing cycles.',
            ],
        ],
        [
            't' => 'How to make a request',
            'body' => [
                'The request is written to <a href="mailto:{email_support}">{email_support}</a> from the address given at registration. It states the holder’s name, the event, the payment date and, if available, the transaction reference.',
                'A response is given within ten working days. An acknowledgement is sent as soon as the request is recorded. Without a response in that time, the runner may follow up, quoting the date of the first message.',
            ],
        ],
        [
            't' => 'Deferral from one edition to the next',
            'body' => [
                'A deferral keeps the registration on the next edition, with no new admission procedure. It is requested before {deferral_deadline} and incurs a fee of {deferral_fee} to cover handling of the file.',
                'The deferral is personal and cannot be transferred to another person. It cannot be combined with a refund: the runner chooses one of the two options.',
                'In case of injury or illness preventing participation, a deferral is accepted on presentation of medical proof, even after the deadline, as long as the bib has not been collected.',
            ],
        ],
        [
            't' => 'Changing event',
            'body' => [
                'Moving from the marathon to the half marathon, or from the half marathon to the Fun Run, is possible while bibs for the new event have not run out. A top-up is requested if the new event costs more; the difference is refunded under the conditions of the previous paragraph if it costs less.',
                'Changing distance does not change the applicable deadline: it is always the date of the original registration that counts.',
            ],
        ],
        [
            't' => 'Refused registration and withdrawn bib',
            'body' => [
                'A registration may be refused, or a bib withdrawn without compensation, in cases of a non-compliant medical file, fraud, bib resale, or conduct that has already led to a disciplinary sanction.',
                'The entry fee is then returned in full, since the sanction concerns participation and not the price paid, except where the contract has already been partly performed.',
            ],
        ],
        [
            't' => 'Cancellation or postponement of the event',
            'body' => [
                'If the event does not take place — decision of the authorities, force majeure, health impossibility — each registered runner receives a proposal to defer to the next date or to be refunded for the entry. The choice is left to the runner for an announced period, generally thirty days.',
                'If the event is cancelled after bib collection or after one of the races has started, the costs already incurred on the runner’s behalf remain with the organisation and the refund is partial, subject to justification.',
                'A change of course, start time or format imposed by safety or urban constraints is not a cancellation, as long as the race actually takes place.',
            ],
        ],
        [
            't' => 'Not starting on race day',
            'body' => [
                'Failing to appear at the start, abandoning during the race or being disqualified gives no right to any refund: the bib, the chip, the refreshments and the reserved medal were made available.',
                'Collecting a bib counts as a reserved place: a registration whose bib has been collected cannot be refunded.',
            ],
        ],
        [
            't' => 'No right of withdrawal',
            'body' => [
                'Registering for a dated sporting event is a service contract supplied on a specific day. It therefore falls within the exceptions to the fourteen-day right of withdrawal provided for distance sales: the refund deadline stated above replaces that right.',
                'This rule does not stand in the way of voluntary refunds granted before the deadline, or of deferrals accepted on medical proof.',
            ],
        ],
        [
            't' => 'Cancellation insurance',
            'body' => [
                'Cancellation insurance, if offered as an option in the basket, is taken out with a third-party insurer. Its cover, its excesses and its claims procedure are its own and do not follow this policy.',
                'The site clearly states at the time of subscription the insurer’s name and the separate cost of this option, which is not included in the reference price of the registration.',
            ],
        ],
        [
            't' => 'Disputes',
            'body' => [
                'Any difficulty should first be raised with <a href="mailto:{email_support}">{email_support}</a>. Failing an amicable solution, the runner, as a consumer, may refer the matter to a competent mediator or to the courts of their home, in accordance with the mandatory rules of their country of residence.',
            ],
        ],
    ],
];
