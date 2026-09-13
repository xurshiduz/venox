<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'lidaz_factory' => [
        'url' => env('LIDAZ_FACTORY_URL', 'https://sklad.simmaautostar.uz'),
        'supplier_name' => env('LIDAZ_SUPPLIER_NAME', 'LIDAZ MCHJ'),
        'expense_type_name' => env('LIDAZ_EXPENSE_TYPE_NAME', 'Оплата поставщику'),
        'default_payment_type_id' => env('LIDAZ_DEFAULT_PAYMENT_TYPE_ID', 1),
        'user_id' => env('LIDAZ_SYNC_USER_ID', 1),
    ],

];
