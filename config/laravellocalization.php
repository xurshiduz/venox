<?php

return [

    // Uncomment the languages that your site supports - or add new ones.
    // These are sorted by the native name, which is the order you might show them in a language selector.
    // Regional languages are sorted by their base language, so "British English" sorts as "English, British"
    'supportedLocales' => [
                      
        'uz'          => ['name' => "O'zbekcha",         'script' => 'Latn', 'native' => 'UZ', 'regional' => 'uz_UZ', 'post' => 'uz'],
        'ru'          => ['name' => 'Русский',           'script' => 'Cyrl', 'native' => 'RU', 'regional' => 'ru_RU', 'post' => 'ru'],
        //'en'          => ['name' => 'English',           'script' => 'Latn', 'native' => 'EN', 'regional' => 'en_GB', 'post' => 'en'],
        
    ],

    // Negotiate for the user locale using the Accept-Language header if it's not defined in the URL?
    // If false, system will take app.php locale attribute
    'useAcceptLanguageHeader' => true,

    // If LaravelLocalizationRedirectFilter is active and hideDefaultLocaleInURL
    // is true, the url would not have the default application language
    //
    // IMPORTANT - When hideDefaultLocaleInURL is set to true, the unlocalized root is treated as the applications default locale "app.locale".
    // Because of this language negotiation using the Accept-Language header will NEVER occur when hideDefaultLocaleInURL is true.
    //
    'hideDefaultLocaleInURL' => true,

    // If you want to display the locales in particular order in the language selector you should write the order here. 
    //CAUTION: Please consider using the appropriate locale code otherwise it will not work
    //Example: 'localesOrder' => ['es','en'],
    'localesOrder' => [],

];
