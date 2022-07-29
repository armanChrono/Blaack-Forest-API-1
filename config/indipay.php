<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Indipay Service Config
    |--------------------------------------------------------------------------
    |   gateway = CCAvenue / PayUMoney / EBS / Citrus / InstaMojo / ZapakPay / Paytm / Mocker
    */

    'gateway' => 'CCAvenue',                // Replace with the name of default gateway you want to use

    'testMode'  => true,                   // True for Testing the Gateway [For production false]

    'ccavenue' => [                         // CCAvenue Parameters
        'merchantId'  => env("INDIPAY_MERCHANT_ID", "66219"),
        'accessCode'  => env('INDIPAY_ACCESS_CODE', 'AVBZ69JB73AW63ZBWA'),       
        'workingKey' => env('INDIPAY_WORKING_KEY', 'D311F41C4E80ACBEE377A3BA750CD2AE'),
                                                    
        // Should be route address for url() function
        'redirectUrl' => env('INDIPAY_REDIRECT_URL', 'ccavenue/response'),
        'cancelUrl' => env('INDIPAY_CANCEL_URL', 'ccavenue/response'),

        'currency' => env('INDIPAY_CURRENCY', 'INR'),
        'language' => env('INDIPAY_LANGUAGE', 'EN'),
    ],
    // Add your response link here. In Laravel 5.2+ you may use the VerifyCsrf Middleware.
    'remove_csrf_check' => [
        'indipay/response'
    ],





];
