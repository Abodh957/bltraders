<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Max quantity per cart line
    |--------------------------------------------------------------------------
    | Hard ceiling applied on add/update, on top of the product stock check.
    */
    'max_quantity_per_item' => 99,

    /*
    |--------------------------------------------------------------------------
    | Shipping
    |--------------------------------------------------------------------------
    | Flat shipping charge per order. Orders whose taxable value is >=
    | free_shipping_above ship free. Set 'charge' to 0 to disable shipping fees.
    */
    'shipping' => [
        'charge'              => 0,
        'free_shipping_above' => 0,
    ],

    /*
    |--------------------------------------------------------------------------
    | GST handling
    |--------------------------------------------------------------------------
    | products.is_gst_paid ("GST Paid" in the admin form) tells us whether GST
    | is already covered inside products.price.
    |
    |   is_gst_paid = true   -> price is GST INCLUSIVE. The GST is shown as a
    |                           breakup only; nothing extra is added to total.
    |   is_gst_paid = false  -> price is GST EXCLUSIVE. GST is added on top.
    |
    | Flip 'inclusive_when_gst_paid' to false if the business rule is the
    | opposite. Everything else in the billing pipeline follows this flag.
    */
    'gst' => [
        'inclusive_when_gst_paid' => true,
    ],

];
