<?php

use craft\helpers\App;

return [
    'useDevServer' => App::env('ENVIRONMENT') === 'dev',
    'manifestPath' => '@webroot/dist/manifest.json',
    'devServerPublic' => App::env('VITE_PRIMARY_SITE_URL'),
    'serverPublic' => App::env('PRIMARY_SITE_URL') . '/dist/',
    'errorEntry' => '/src/js/app.js',
    'cacheKeySuffix' => '',
    'devServerInternal' => 'http://localhost:3000/',
    'checkDevServer' => true,
];
