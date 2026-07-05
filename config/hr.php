<?php

return [
    // Domain email untuk username login HR, otomatis dibuat dari nama saat Owner menambah HR baru
    'email_domain' => env('HR_EMAIL_DOMAIN', 'warungsatelanud.id'),
    'default_password' => env('HR_DEFAULT_PASSWORD', 'password123'),
];