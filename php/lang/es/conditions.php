<?php
declare(strict_types=1);

return [
    'title' => 'Condiciones generales de uso — Maratón Internacional de Marrakech',
    'description' => 'Condiciones aplicables a la inscripción y a la compra de un dorsal para el Maratón Internacional de Marrakech: vendedor, precios en {billing}, pago, compromisos del corredor, anulación y normativa aplicable.',
    'og_description' => 'Lo que aceptas al comprar un dorsal: vendedor identificado, precios de referencia y condiciones de pago y de anulación.',

    'head.index' => '{cur} / {total}',
    'head.title' => 'Condiciones generales de uso',
    'head.lede' => 'Estas condiciones regulan la compra de un dorsal a través del sitio. Indican quién vende, a qué precio, en qué moneda y qué puede esperar cada parte de la otra.',
    'toc.title' => 'Índice',

    'next.label' => 'Página siguiente · {no}',
    'next.aria' => 'Página siguiente',

    'sections' => [
        [
            't' => 'Objeto y ámbito de aplicación',
            'body' => [
                'Al confirmar una inscripción en el sitio del Maratón Internacional de Marrakech, el corredor acepta estas condiciones generales. Se aplican al maratón, a la media maratón y a la Carrera para Todos, así como a cualquier servicio de pago vendido en el mismo carrito.',
                'No sustituyen al reglamento deportivo de la prueba, que rige el desarrollo de la carrera el día de la salida. Ambos documentos son complementarios: las condiciones regulan la compra y el reglamento regula la carrera.',
            ],
        ],
        [
            't' => 'Vendedor del dorsal',
            'body' => [
                'El dorsal lo vende {entity}. Su referencia fiscal y su dirección de correspondencia son {entity_block}. Esta es la identificación que la pasarela de pago refleja en el extracto bancario del corredor.',
                'El sitio es editorial: presenta la prueba y redirige a la aplicación de inscripción. El pago lo procesa un proveedor de pagos externo; el sitio no conserva ningún número de tarjeta.',
            ],
        ],
        [
            't' => 'Parte comercial y parte asociativa',
            'body' => [
                'Dos actores, dos papeles. {entity} es la estructura comercial: cobra las tasas de inscripción, emite las confirmaciones y tramita las solicitudes de reembolso. {association} es el socio deportivo, {association_role}.',
                'Por tanto, el corredor contrata con {entity} para su compra. Las decisiones deportivas —recorrido, horarios, clasificación o descalificación— corresponden a la organización deportiva y a la federación, y no al vendedor del dorsal.',
            ],
        ],
        [
            't' => 'Quién puede inscribirse',
            'body' => [
                'La inscripción es nominativa. Exige una edad mínima fijada por el reglamento de cada prueba, un correo electrónico válido para la confirmación y, en las distancias largas, un certificado médico o una licencia que autorice la práctica de la carrera en competición.',
                'La inscripción es intransferible: no puede cederse a un tercero, revenderse ni canjearse entre corredores. El dorsal es nominativo y puede retirarse en caso de fraude demostrado.',
            ],
        ],
        [
            't' => 'Precios de referencia y moneda facturada',
            'body' => [
                'Las tasas de inscripción se fijan y se cobran en {billing}. Los precios de referencia que se muestran antes del formulario son: maratón {price_marathon}, media maratón {price_semi}, Carrera para Todos {price_tous}.',
                'Estas cifras corresponden a la tarifa del periodo en curso. Pueden variar hasta el cierre de las inscripciones; solo el importe mostrado en el momento del pago definitivo compromete a ambas partes.',
            ],
            'list' => [
                'Lo que incluye el precio: el dorsal, el chip de cronometraje, el acceso a las zonas de salida y de meta, el avituallamiento en el recorrido, la medalla y la camiseta oficial.',
                'Lo que el precio no incluye: transporte, alojamiento, visa, seguro personal de anulación y cualquier opción de pago añadida por separado.',
            ],
        ],
        [
            't' => 'Pago, monedas y conversión',
            'body' => [
                'El pago es exigible en su totalidad al inscribirse. Las tarjetas aceptadas las procesa el proveedor de pagos elegido; la transacción se confirma en el momento de la autorización.',
                'Para un pago en dírham (MAD) o en euro (EUR), el importe de referencia en {billing} se convierte al tipo aplicado por la red de la tarjeta. Origen del tipo de cambio: {fx_source}. Momento de la conversión: {fx_moment}.',
                'El sitio no añade ninguna comisión de cambio. El cargo que realmente aparece en el extracto bancario puede, no obstante, diferir del precio mostrado, por efecto del tipo de cambio y de las comisiones aplicadas por el banco emisor.',
            ],
        ],
        [
            't' => 'Confirmación de la inscripción',
            'body' => [
                'La inscripción solo es definitiva tras la aceptación del pago. El corredor recibe una confirmación en la dirección indicada, que hace las veces de entrada. Una inscripción rechazada o un pago no completado no dan derecho a ningún dorsal.',
                'Si no recibe confirmación en las horas posteriores al cargo, el corredor escribe a {email_support} con su nombre, la fecha del pago y, si es posible, la referencia de la transacción.',
            ],
        ],
        [
            't' => 'Retirada del dorsal',
            'body' => [
                'La retirada se hace en el village, en las franjas horarias anunciadas, presentando un documento de identidad y la confirmación. En las distancias largas no se entrega ningún dorsal el mismo día.',
                'Se acepta un poder de representación con una copia del documento de identidad del titular y del apoderado. La retirada sigue siendo posible durante el periodo anunciado, hasta agotar los dorsales disponibles.',
            ],
        ],
        [
            't' => 'Compromisos del corredor',
            'body' => [
                'El corredor declara estar en un estado de salud compatible con el esfuerzo exigido, haber leído el reglamento deportivo y aceptar los horarios de salida, los cierres de barreras y las indicaciones de los organizadores y de las autoridades.',
                'También acepta llevar el dorsal visible, el cronometraje con chip y que su nombre, su edad, su club y su tiempo se publiquen en los resultados oficiales.',
            ],
        ],
        [
            't' => 'Anulación, reembolso y traslado',
            'body' => [
                'Las reglas de reembolso, de cambio de prueba y de traslado de una edición a otra no están en este documento: conforman una política propia, que puede leerse en la página <a href="remboursement.php">Reembolso y anulación</a>.',
                'Resumen útil antes de pagar: la solicitud de reembolso debe llegar antes del {refund_deadline}, y el traslado de la inscripción debe solicitarse antes del {deferral_deadline}, con una comisión de {deferral_fee}.',
            ],
        ],
        [
            't' => 'Impedimento del evento',
            'body' => [
                'Si la prueba se anula por decisión de las autoridades o por fuerza mayor, o se traslada a otra fecha, los corredores inscritos reciben un aviso por correo y una propuesta de traslado a la nueva fecha o de reembolso, según la política anunciada.',
                'Una modificación del recorrido, de los horarios o del formato de una prueba, impuesta por la seguridad o por restricciones urbanas, no constituye una anulación y no da derecho a reembolso siempre que la carrera se celebre.',
            ],
        ],
        [
            't' => 'Imágenes y datos personales',
            'body' => [
                'Los datos recogidos en la inscripción sirven para organizar la carrera, elaborar los resultados y cumplir las obligaciones legales y de seguro. Su tratamiento, sus destinatarios y sus derechos se describen en la <a href="confidentialite.php">política de privacidad</a>.',
                'Las fotografías y vídeos realizados durante el evento pueden usarse para la promoción de la prueba. Puede oponerse escribiendo a {email_legal}.',
            ],
        ],
        [
            't' => 'Responsabilidad',
            'body' => [
                'El vendedor del dorsal responde del cumplimiento de sus propias obligaciones: entrega del dorsal, exactitud del precio y tramitación de las solicitudes. No responde de las decisiones técnicas de la organización deportiva ni de los hechos de un tercero ajeno al contrato.',
                'La pérdida de un objeto de valor en el guardarropa, un daño derivado del incumplimiento del reglamento por parte del corredor, o un mal resultado deportivo, no pueden comprometer al vendedor.',
            ],
        ],
        [
            't' => 'Modificación de estas condiciones',
            'body' => [
                'Estas condiciones pueden adaptarse para cumplir una obligación legal, un requisito de la pasarela de pago o una evolución del formato de la prueba. La versión aplicable es la que se muestra el día del pago.',
                'Una modificación no afecta a una inscripción ya confirmada, salvo obligación legal contraria.',
            ],
        ],
        [
            't' => 'Normativa aplicable y reclamación',
            'body' => [
                'Las relaciones de compra se rigen por la legislación del Estado en el que está registrada la estructura que factura, sin privar al corredor-consumidor de las normas imperativas de su país de residencia.',
                'Toda reclamación se dirige primero a {email_support}. En ausencia de una respuesta satisfactoria, el corredor puede recurrir a un mediador de consumo competente o al tribunal de su domicilio.',
            ],
        ],
        [
            't' => 'Contacto',
            'body' => [
                'Asistencia de inscripción y pago: <a href="mailto:{email_support}">{email_support}</a>. Consultas sobre datos personales: <a href="mailto:{email_legal}">{email_legal}</a>. Contacto general: <a href="mailto:{email_contact}">{email_contact}</a>.',
                'Dirección postal del vendedor: {address}.',
            ],
        ],
    ],
];
