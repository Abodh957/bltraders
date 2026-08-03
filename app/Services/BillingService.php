<?php

namespace App\Services;

use App\Models\Product;

/**
 * Single source of truth for money.
 *
 * Both the cart summary and the placed order run through here, so what the
 * customer is quoted on the cart screen is exactly what gets written to the
 * order — no drift between the two.
 */
class BillingService
{
    /**
     * Price the customer actually pays per unit (sale price wins when set).
     */
    public function unitPrice(Product $product): float
    {
        $price = (float) $product->price;
        $sale  = $product->sale_price !== null ? (float) $product->sale_price : null;

        return ($sale !== null && $sale > 0 && $sale < $price) ? $sale : $price;
    }

    /**
     * Break one line (product + quantity) into its billing components.
     *
     * @return array{mrp:float,unit_price:float,quantity:int,tax_type:string,
     *               gst_percentage:float,taxable_amount:float,gst_amount:float,
     *               line_total:float,discount_amount:float}
     */
    public function calculateLine(Product $product, int $quantity): array
    {
        $mrp       = round((float) $product->price, 2);
        $unitPrice = round($this->unitPrice($product), 2);
        $gstPercent = (float) ($product->gst_percentage ?? 0);

        // Gross amount the customer sees against this line.
        $gross = round($unitPrice * $quantity, 2);

        $inclusiveWhenPaid = (bool) config('cart.gst.inclusive_when_gst_paid', true);
        $isGstPaid         = (bool) $product->is_gst_paid;

        if ($gstPercent <= 0) {
            $taxType  = 'none';
            $taxable  = $gross;
            $gstAmount = 0.0;
            $lineTotal = $gross;
        } elseif ($isGstPaid === $inclusiveWhenPaid) {
            // GST already sits inside the price — split it out, add nothing.
            $taxType   = 'inclusive';
            $taxable   = round($gross / (1 + ($gstPercent / 100)), 2);
            $gstAmount = round($gross - $taxable, 2);
            $lineTotal = $gross;
        } else {
            // GST is charged on top of the price.
            $taxType   = 'exclusive';
            $taxable   = $gross;
            $gstAmount = round($gross * $gstPercent / 100, 2);
            $lineTotal = round($taxable + $gstAmount, 2);
        }

        return [
            'mrp'             => $mrp,
            'unit_price'      => $unitPrice,
            'quantity'        => $quantity,
            'tax_type'        => $taxType,
            'gst_percentage'  => $gstPercent,
            'taxable_amount'  => $taxable,
            'gst_amount'      => $gstAmount,
            'line_total'      => $lineTotal,
            'discount_amount' => round(max(0, $mrp - $unitPrice) * $quantity, 2),
        ];
    }

    /**
     * Roll a set of calculated lines up into the order-level billing summary.
     *
     * @param  array<int,array>  $lines  output of calculateLine()
     */
    public function summarise(array $lines): array
    {
        $itemsCount = 0;
        $mrpTotal   = 0.0;
        $subtotal   = 0.0;
        $taxAmount  = 0.0;
        $discount   = 0.0;
        $gstBreakup = [];

        foreach ($lines as $line) {
            $itemsCount += $line['quantity'];
            $mrpTotal   += $line['mrp'] * $line['quantity'];
            $subtotal   += $line['taxable_amount'];
            $taxAmount  += $line['gst_amount'];
            $discount   += $line['discount_amount'];

            if ($line['gst_amount'] > 0) {
                $slab = number_format($line['gst_percentage'], 2, '.', '');
                $gstBreakup[$slab] ??= ['gst_percentage' => (float) $slab, 'taxable_amount' => 0.0, 'gst_amount' => 0.0];
                $gstBreakup[$slab]['taxable_amount'] += $line['taxable_amount'];
                $gstBreakup[$slab]['gst_amount']     += $line['gst_amount'];
            }
        }

        $subtotal  = round($subtotal, 2);
        $taxAmount = round($taxAmount, 2);
        $shipping  = $this->shippingCharge($subtotal);
        $total     = round($subtotal + $taxAmount + $shipping, 2);

        $gstBreakup = array_values(array_map(fn($row) => [
            'gst_percentage' => $row['gst_percentage'],
            'taxable_amount' => round($row['taxable_amount'], 2),
            'gst_amount'     => round($row['gst_amount'], 2),
        ], $gstBreakup));

        return [
            'items_count'     => $itemsCount,
            'mrp_total'       => round($mrpTotal, 2),
            'discount_amount' => round($discount, 2),
            'subtotal'        => $subtotal,
            'tax_amount'      => $taxAmount,
            'shipping_charge' => $shipping,
            'total_amount'    => $total,
            'gst_breakup'     => $gstBreakup,
        ];
    }

    /**
     * Flat shipping, waived once the taxable value crosses the threshold.
     */
    public function shippingCharge(float $subtotal): float
    {
        if ($subtotal <= 0) {
            return 0.0;
        }

        $charge = (float) config('cart.shipping.charge', 0);
        $freeAbove = (float) config('cart.shipping.free_shipping_above', 0);

        if ($charge <= 0) {
            return 0.0;
        }

        if ($freeAbove > 0 && $subtotal >= $freeAbove) {
            return 0.0;
        }

        return round($charge, 2);
    }
}
