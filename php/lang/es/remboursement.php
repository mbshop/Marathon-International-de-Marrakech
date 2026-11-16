<?php
declare(strict_types=1);

return [
    'title' => 'Reembolso, anulación y traslado — Maratón Internacional de Marrakech',
    'description' => 'Condiciones de reembolso de una inscripción en el Maratón Internacional de Marrakech: fecha límite, gastos retenidos, traslado de una edición a otra, cambio de prueba y anulación del evento.',
    'og_description' => 'Qué se reembolsa, hasta cuándo, en qué condiciones y qué es trasladable de una edición a otra.',

    'head.index' => '{cur} / {total}',
    'head.title' => 'Reembolso y anulación',
    'head.lede' => 'Una inscripción no es un bien como los demás: reserva un dorsal nominativo, avituallamiento y un lugar en el flujo de corredores. Las reglas siguientes indican qué puede devolverse, trasladarse o rechazarse.',
    'toc.title' => 'Índice',

    'next.label' => 'Página siguiente · {no}',
    'next.aria' => 'Volver a las inscripciones',

    'sections' => [
        [
            't' => 'Principio',
            'body' => [
                'Una inscripción confirmada es firme. No obstante, puede reembolsarse si la solicitud llega antes de la fecha indicada más abajo, y trasladarse a la edición siguiente en las mismas condiciones de fecha.',
                'Fecha límite para solicitar el reembolso: {refund_deadline}. Fecha límite para solicitar el traslado: {deferral_deadline}. Gastos aplicables al traslado: {deferral_fee}.',
            ],
        ],
        [
            't' => 'Qué cubre el reembolso',
            'body' => [
                'El reembolso recae sobre el dorsal tal como se cobró en {billing}. En cambio, se retienen los gastos ya incurridos por cuenta del corredor y no recuperables: elaboración de la camiseta y del dorsal nominativos, grabado de la medalla y comisión del proveedor de pagos.',
                'El reembolso se realiza en el medio de pago usado al inscribirse, nunca en efectivo ni en otra cuenta. El plazo en que vuelve a aparecer en el extracto depende del banco emisor, generalmente de uno a dos ciclos.',
            ],
        ],
        [
            't' => 'Cómo solicitarlo',
            'body' => [
                'La solicitud se envía a <a href="mailto:{email_support}">{email_support}</a> desde el correo indicado durante la inscripción. Debe indicar el nombre del titular, la prueba, la fecha del pago y, si está disponible, la referencia de la transacción.',
                'Se da respuesta en el plazo de diez días hábiles. Se envía un acuse de recibo en cuanto se registra la solicitud. Si no hay respuesta en ese plazo, el corredor puede insistir indicando la fecha del primer mensaje.',
            ],
        ],
        [
            't' => 'Traslado de una edición a otra',
            'body' => [
                'El traslado mantiene la inscripción en la edición siguiente, sin un nuevo procedimiento de admisión. Debe solicitarse antes del {deferral_deadline} y genera unos gastos de {deferral_fee} destinados a cubrir la gestión del expediente.',
                'El traslado es nominativo y no puede cederse a otra persona. No es compatible con un reembolso: el corredor elige una de las dos opciones.',
                'En caso de lesión o enfermedad que impida participar, se acepta un traslado presentando un justificante médico, incluso después de la fecha límite, siempre que el dorsal no se haya retirado.',
            ],
        ],
        [
            't' => 'Cambio de prueba',
            'body' => [
                'Pasar del maratón a la media maratón, o de la media maratón a la Carrera para Todos, es posible mientras no se agoten los dorsales de la nueva prueba. Se exige un suplemento si la nueva prueba cuesta más; la diferencia se reembolsa en las condiciones del apartado anterior si cuesta menos.',
                'El cambio de distancia no modifica la fecha límite aplicable: siempre es la fecha de la primera inscripción la que sirve de referencia.',
            ],
        ],
        [
            't' => 'Rechazo de inscripción y retirada del dorsal',
            'body' => [
                'Una inscripción puede rechazarse o un dorsal retirarse sin indemnización en caso de expediente médico no conforme, fraude, reventa de dorsal o un comportamiento que ya haya supuesto una sanción disciplinaria.',
                'El importe del dorsal se restituye entonces íntegramente, ya que la sanción recae sobre la participación y no sobre el precio pagado, salvo que el contrato ya se haya ejecutado en parte.',
            ],
        ],
        [
            't' => 'Anulación o traslado de la prueba',
            'body' => [
                'Si la prueba no se celebra —decisión de las autoridades, fuerza mayor, imposibilidad sanitaria—, cada corredor inscrito recibe una propuesta de traslado a la fecha siguiente o de reembolso del dorsal. Se deja al corredor elegir durante un periodo anunciado, generalmente de treinta días.',
                'Si la prueba se anula después de la retirada de los dorsales o tras la salida de alguna de las carreras, los gastos ya asumidos por cuenta del corredor quedan en poder de la organización y el reembolso es parcial, previa justificación.',
                'Un cambio de recorrido, de horario de salida o de formato impuesto por la seguridad o por restricciones urbanas no es una anulación, siempre que la carrera se celebre.',
            ],
        ],
        [
            't' => 'No tomar la salida el día de la carrera',
            'body' => [
                'No presentarse a la salida, abandonar la carrera o ser descalificado no da derecho a ningún reembolso: el dorsal, el chip, el avituallamiento y la medalla reservada se pusieron a disposición del corredor.',
                'La retirada del dorsal equivale a una participación reservada: no es posible reembolsar una inscripción cuyo dorsal ya se ha retirado.',
            ],
        ],
        [
            't' => 'Ausencia de derecho de desistimiento',
            'body' => [
                'La inscripción a un evento deportivo con fecha fija es un contrato de servicio prestado en una fecha concreta. Por ello entra en las excepciones al derecho de desistimiento de catorce días previsto para las ventas a distancia: el plazo de reembolso indicado arriba sustituye a ese derecho.',
                'Esta norma no impide los reembolsos voluntarios concedidos antes de la fecha límite, ni los traslados aceptados con justificante.',
            ],
        ],
        [
            't' => 'Seguro de anulación',
            'body' => [
                'Un seguro de anulación, si se ofrece como opción en el carrito, se contrata con una aseguradora externa. Sus coberturas, sus franquicias y su procedimiento de declaración le son propios y no se rigen por esta política.',
                'El sitio indica con claridad, al contratarla, el nombre de la aseguradora y el coste aparte de esta opción, que no está incluido en el precio de referencia de la inscripción.',
            ],
        ],
        [
            't' => 'Litigios',
            'body' => [
                'Cualquier dificultad se dirige primero a <a href="mailto:{email_support}">{email_support}</a>. A falta de una solución amistosa, el corredor-consumidor puede acudir a un mediador competente o al tribunal de su domicilio, conforme a las normas imperativas de su país de residencia.',
            ],
        ],
    ],
];
