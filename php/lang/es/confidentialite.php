<?php
declare(strict_types=1);

return [
    'title' => 'Política de privacidad — Maratón Internacional de Marrakech',
    'description' => 'Datos recogidos al inscribirse en el Maratón Internacional de Marrakech, finalidades, bases legales, destinatarios, plazos de conservación, transferencias fuera de la Unión Europea y derechos del corredor.',
    'og_description' => 'Qué guardamos cuando te inscribes, por qué, durante cuánto tiempo y cómo ejercer tus derechos.',

    'head.index' => '{cur} / {total}',
    'head.title' => 'Política de privacidad',
    'head.lede' => 'Inscribirse en una carrera implica datos poco habituales: identidad, edad, club, rendimiento y, a menudo, un certificado médico. Esta página indica con precisión qué se guarda, por quién y con qué finalidad.',
    'toc.title' => 'Índice',

    'next.label' => 'Página siguiente · {no}',
    'next.aria' => 'Página siguiente',

    'sections' => [
        [
            't' => 'Responsable del tratamiento',
            'body' => [
                'Los datos de inscripción los trata {entity}, la estructura que comercializa los dorsales, con domicilio en: {address}. Para cualquier cuestión sobre la protección de datos: <a href="mailto:{email_legal}">{email_legal}</a>.',
                'La organización deportiva, que actúa junto con {association}, recibe los datos estrictamente necesarios para la carrera: lista de salida, categorías de edad, resultados y contactos de emergencia.',
            ],
        ],
        [
            't' => 'Datos recogidos',
            'body' => [
                'El formulario de inscripción solicita los datos útiles para la carrera y la seguridad del corredor. En esta fase no se recoge nada más.',
            ],
            'list' => [
                'Identidad y perfil: nombre, apellidos, fecha de nacimiento, sexo, nacionalidad, club, ciudad y país de residencia.',
                'Contacto: correo electrónico y teléfono, para la confirmación, las instrucciones de retirada y el aviso en caso de impedimento.',
                'Deportivo: prueba elegida, tiempo de referencia si lo hay, talla y número de calzado para la camiseta, número del chip.',
                'Salud y seguridad: certificado médico o licencia en vigor para las distancias largas, contacto de emergencia e información médica útil en caso de auxilio.',
                'Pago: importe, moneda, fecha, referencia de la transacción y nombre que figura en el medio de pago. Los números de tarjeta no transitan ni se almacenan en el sitio.',
            ],
        ],
        [
            't' => 'Finalidades y bases legales',
            'body' => [
                'Ejecutar el contrato de inscripción: confirmación, asignación del dorsal, cronometraje, entrega de resultados y gestión del guardarropa. Es la base principal para casi todos los datos anteriores.',
                'Cumplir una obligación legal y reglamentaria: verificación de la certificación médica exigida para participar, conservación de documentos para el seguro y las autoridades, y elaboración de las clasificaciones oficiales.',
                'Interés legítimo: informar al corredor de la información práctica de la edición en curso, prevenir un incidente y luchar contra la reventa ilícita de dorsales.',
                'Consentimiento: comunicaciones comerciales después de la edición en curso y depósito de cookies de medición de audiencia no esenciales. Este consentimiento se puede retirar en cualquier momento.',
            ],
        ],
        [
            't' => 'Destinatarios',
            'body' => [
                'Los datos no se venden. Se comparten únicamente con los intervinientes necesarios para el desarrollo de la prueba y el cobro.',
            ],
            'list' => [
                'Proveedor de pagos: procesa la transacción en {billing} y comunica la confirmación del cargo.',
                'Cronometrador oficial y gestor de la base de dorsales.',
                'Asegurador del evento y, en caso de incidente, servicios de auxilio y autoridades.',
                'Federación y organizador deportivo, {association}, para la parte de competición.',
                'Proveedores técnicos del sitio, dentro del límite de su encargo y siguiendo instrucciones.',
            ],
        ],
        [
            't' => 'Plazos de conservación',
            'body' => [
                'La identidad, los resultados y el historial de inscripción se conservan el tiempo necesario para organizar las ediciones siguientes y defender los derechos en justicia, es decir, cinco años a partir del final de la edición correspondiente.',
                'Los justificantes médicos se conservan solo durante el plazo de las obligaciones legales y de seguro, y después se destruyen. Los datos estrictamente necesarios para la contabilidad siguen la obligación legal de conservación de los documentos contables.',
                'Una solicitud de supresión anticipada se tramita dentro de los límites que permiten estas obligaciones: la clasificación deportiva, como hecho histórico de la carrera, puede mantenerse de forma anonimizada.',
            ],
        ],
        [
            't' => 'Transferencias fuera de la Unión Europea',
            'body' => [
                'La estructura que factura, el proveedor de pagos y algunos alojamientos web están establecidos en Estados Unidos. Por ello, una transferencia fuera del Espacio Económico Europeo es inherente a la inscripción.',
                'Estas transferencias se basan en las cláusulas contractuales tipo de la Comisión Europea y en las garantías que ofrece la red de pago. Se puede solicitar una copia de las garantías a <a href="mailto:{email_legal}">{email_legal}</a>.',
            ],
        ],
        [
            't' => 'Sus derechos',
            'body' => [
                'Puede solicitar el acceso a sus datos, su rectificación, su supresión, la limitación de su tratamiento y su portabilidad, así como oponerse a los tratamientos basados en el interés legítimo o retirar un consentimiento.',
                'La solicitud se dirige a <a href="mailto:{email_legal}">{email_legal}</a>, acompañada de un documento que acredite su identidad. Se tramita en el plazo de un mes, ampliable en dos meses en caso de solicitud compleja.',
                'Si la respuesta no es satisfactoria, puede presentarse una reclamación ante la autoridad de protección de datos competente: la CNDP en Marruecos, o la CNIL en la Unión Europea para un residente europeo.',
            ],
        ],
        [
            't' => 'Cookies y medición de audiencia',
            'body' => [
                'El sitio deposita una cookie de preferencia de idioma, <code>mim_lang</code>, que recuerda la lengua elegida en la primera visita durante doce meses. No contiene ningún dato personal y no se usa con fines publicitarios.',
                'No se deposita ninguna cookie publicitaria ni rastreador de terceros sin consentimiento previo. La medición de audiencia, si se lleva a cabo, se realiza con direcciones IP truncadas y un plazo de conservación breve.',
            ],
        ],
        [
            't' => 'Seguridad',
            'body' => [
                'El acceso a los datos de inscripción se limita a las personas que los necesitan para su función. El sitio se sirve mediante HTTPS, los espacios de administración están protegidos con contraseña cifrada y límite de intentos, y las copias de seguridad están cifradas.',
                'El propio pago escapa al control del sitio: lo realiza el proveedor de pagos, que responde de la seguridad de esta fase.',
            ],
        ],
        [
            't' => 'Menores',
            'body' => [
                'Las pruebas abiertas a menores requieren la autorización del representante legal, que facilita sus datos y el certificado exigido. Los datos de un menor no son objeto de ninguna comunicación comercial.',
            ],
        ],
        [
            't' => 'Evolución de esta política',
            'body' => [
                'Esta política puede modificarse para tener en cuenta una evolución legal, un nuevo proveedor o un cambio en el formato de la prueba. La fecha de la última actualización aparece al principio de la página.',
            ],
        ],
    ],
];
