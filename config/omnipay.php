<?php

return [

    /*
     * Default Gateway
     */
    'default' => 'paypal',

    /*
     * Omnipay Gateways
     * Add each Gateway here
     */
    'gateways' => [

        'paypal_rest' => [
            'driver' => 'PayPal_Rest',
            'options' => [
                'clientId' => 'yourusernamehere',
                'secret' => 'yourpasswordhere',
            ]
        ],

        'paypal_express' => [
            'driver' => 'PayPal_Express',
            'options' => [
                'username' => '',
                'password' => '',
                'signature' => '',

                'solutionType' => '',
                'landingPage' => '',
                'headerImageUrl' => ''
            ]
        ]

    ]

];