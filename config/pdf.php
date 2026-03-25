<?php

return [
    'mode' => 'utf-8',
    'format' => 'A4',
    'author' => '',
    'subject' => '',
    'keywords' => '',
    'creator' => 'Laravel Pdf',
    'display_mode' => 'fullpage',
    'tempDir' => storage_path('app/temp/mpdf'),
    'font_path' => base_path('resources/fonts/'),
    'font_data' => [
        'tajawal' => [
            'R' => 'Tajawal-Regular.ttf',    // regular
            'B' => 'Tajawal-Bold.ttf',       // optional: bold
            'I' => 'Tajawal-Light.ttf',     // optional: italic
            'BI' => 'Tajawal-Medium.ttf', // optional: bold-italic
            'useOTL' => 0xFF,
            'useKashida' => 75,
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
        ],
        'dejavu sans' => [
            'R' => 'DejaVuSans.ttf',
            'B' => 'DejaVuSans-Bold.ttf',
            'I' => 'DejaVuSans-Oblique.ttf',
            'BI' => 'DejaVuSans-BoldOblique.ttf',
        ],
    ],
];
