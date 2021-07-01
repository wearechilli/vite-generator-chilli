<?php

return array(
    '*' => array(
        // The domain name where Fathom is hosted. This is also the URL where the tracker code is pointed to.
        'baseUri' => '$FATHOM_BASE_URI',

        // The tracking ID of this site. You can find the ID in your tracking code snippet, e.g.: ABCDE
        'trackingId' => '$FATHOM_TRACKING_ID',

        'username' => '$FATHOM_USERNAME',

        'password' => '$FATHOM_PASSWORD',

        // Automatically inject a tracking script in your site
        'injectTracking' => false,
    ),
    'production' => [
        'injectTracking' => true,
	],
);
