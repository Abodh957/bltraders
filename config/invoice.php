<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Seller details printed on the invoice
    |--------------------------------------------------------------------------
    | Set these in .env. Blank values are simply left off the PDF — nothing is
    | invented, so a missing GSTIN never shows up as a fake number.
    */
    'seller' => [
        'name'    => env('INVOICE_SELLER_NAME', 'BL Traders'),
        'address' => env('INVOICE_SELLER_ADDRESS', ''),
        'phone'   => env('INVOICE_SELLER_PHONE', ''),
        'email'   => env('INVOICE_SELLER_EMAIL', ''),
        'gstin'   => env('INVOICE_SELLER_GSTIN', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Shareable link lifetime
    |--------------------------------------------------------------------------
    | GET /api/orders/{id}/invoice/link returns a signed URL that opens the PDF
    | without a login token (for WhatsApp / browser). It stops working after
    | this many minutes.
    */
    'link_ttl_minutes' => (int) env('INVOICE_LINK_TTL', 30),

    /* Logo, relative to public/. Skipped silently if the file is missing. */
    'logo' => 'adminAssets/assets/images/logo-abbr.png',

];
