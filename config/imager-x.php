<?php

use craft\helpers\App;

return [
    '*' => [
        'jpegQuality' => '100',
        'pngCompressionLevel' => '0',
        'imagerUrl' => App::env('PRIMARY_SITE_URL') . '/imager/',
        'webpQuality' => '90',
        'removeMetadata' => true,
        'hashPath' => true,
        'hashRemoteUrl' => true,
        'smartResizeEnabled' => true,
        'interlace' => true,
        'skipExecutableExistCheck' => true,
        'optimizers' => ['jpegoptim', 'optipng'],
        'optimizerConfig' => [
            'jpegoptim' => [
                'extensions' => ['jpg'],
                'path' => '/usr/bin/jpegoptim',
                'optionString' => '-m90 --all-progressive -s',
            ],
            'optipng' => [
                'extensions' => ['png'],
                'path' => '/usr/bin/optipng',
                'optionString' => '-o2',
            ]
        ]
    ]
];
