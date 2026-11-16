<?php
declare(strict_types=1);

/**
 * Day and month names per locale, indexed 1..7 and 1..12, plus the order used when
 * spelling a date out. Numerals stay Latin everywhere: race times, distances and bib
 * numbers are shown the same way in all four languages.
 */
return [
    'fr' => [
        'weekdays' => [1 => 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche'],
        'weekdaysShort' => [1 => 'Lun.', 'Mar.', 'Mer.', 'Jeu.', 'Ven.', 'Sam.', 'Dim.'],
        'months' => [1 => 'janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'],
        'long' => '{weekday} {day} {month} {year}',
        'medium' => '{weekday} {day} {month}',
        'decimal' => ',',
        'timeSep' => 'h',
        'sup' => ['first' => 'er', 'other' => 'e'],
    ],
    'en' => [
        'weekdays' => [1 => 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
        'weekdaysShort' => [1 => 'Mon.', 'Tue.', 'Wed.', 'Thu.', 'Fri.', 'Sat.', 'Sun.'],
        'months' => [1 => 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
        'long' => '{weekday}, {month} {day}, {year}',
        'medium' => '{weekday}, {month} {day}',
        'decimal' => '.',
        'timeSep' => ':',
        'sup' => ['first' => 'st', 'other' => 'th'],
    ],
    'ar' => [
        'weekdays' => [1 => 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت', 'الأحد'],
        'weekdaysShort' => [1 => 'إثنين', 'ثلاثاء', 'أربعاء', 'خميس', 'جمعة', 'سبت', 'أحد'],
        'months' => [1 => 'يناير', 'فبراير', 'مارس', 'أبريل', 'ماي', 'يونيو', 'يوليوز', 'غشت', 'شتنبر', 'أكتوبر', 'نونبر', 'دجنبر'],
        'long' => '{weekday} {day} {month} {year}',
        'medium' => '{weekday} {day} {month}',
        'decimal' => ',',
        'timeSep' => ':',
        'sup' => ['first' => '', 'other' => ''],
    ],
    'es' => [
        'weekdays' => [1 => 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado', 'domingo'],
        'weekdaysShort' => [1 => 'Lun.', 'Mar.', 'Mié.', 'Jue.', 'Vie.', 'Sáb.', 'Dom.'],
        'months' => [1 => 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'],
        'long' => '{weekday}, {day} de {month} de {year}',
        'medium' => '{weekday}, {day} de {month}',
        'decimal' => ',',
        'timeSep' => ':',
        'sup' => ['first' => 'º', 'other' => 'º'],
    ],
];
