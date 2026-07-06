<?php

return [
    'logo_filename' => env('COMPANY_LOGO_FILENAME', 'logo.png'),
    'logo_path' => storage_path('app/public/logo/' . env('COMPANY_LOGO_FILENAME', 'logo.png')),
];