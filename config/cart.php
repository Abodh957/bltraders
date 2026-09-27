<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Max quantity per cart line
    |--------------------------------------------------------------------------
    | Hard ceiling applied on add/update. Set to 0 for no ceiling at all.
    */
    'max_quantity_per_item' => (int) env('CART_MAX_QTY_PER_ITEM', 0),

    /*
    |--------------------------------------------------------------------------
    | Enforce stock
    |--------------------------------------------------------------------------
    | true  -> a customer can never add or order more than products.stock
    | false -> stock is informational only; any quantity can be added and
    |          ordered, and products.stock may go negative (backorder).
    |
    | Turned OFF by the shop owner: customers may order more than is in stock.
    | Stock is still decremented, so a negative value shows how much is owed.
    */
    'enforce_stock' => (bool) env('CART_ENFORCE_STOCK', false),

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
