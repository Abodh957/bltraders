<?php

namespace App\Services;

use App\Models\Order;
use App\Support\AmountInWords;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * Everything an invoice needs, built once from an order.
 *
 * The JSON invoice endpoint and the PDF read from the same numbers, so what
 * the app shows on screen and what the customer shares can never disagree.
 * All figures come from the order snapshot — never from live product prices.
 */
class InvoiceService
{
    /** Relations the invoice reads. Load these before calling data()/pdf(). */
    public const RELATIONS = ['items', 'store', 'shop'];

    /** GST grouped by slab, e.g. all 18% lines together. */
    public function gstBreakup(Order $order): array
    {
        $slabs = [];

        foreach ($order->items as $item) {
            if ((float) $item->gst_amount <= 0) {
                continue;
            }
            $key = number_format((float) $item->gst_percentage, 2, '.', '');
            $slabs[$key] ??= ['gst_percentage' => (float) $key, 'taxable_amount' => 0.0, 'gst_amount' => 0.0];
            $slabs[$key]['taxable_amount'] += (float) $item->taxable_amount;
            $slabs[$key]['gst_amount']     += (float) $item->gst_amount;
        }

        return array_values(array_map(fn($r) => [
            'gst_percentage' => $r['gst_percentage'],
            'taxable_amount' => round($r['taxable_amount'], 2),
            'gst_amount'     => round($r['gst_amount'], 2),
        ], $slabs));
    }

    /** View data for the PDF template. */
    public function data(Order $order): array
    {
        return [
            'order'       => $order,
            'seller'      => config('invoice.seller'),
            'logo'        => $this->logoDataUri(),
            'items'       => $order->items,
            'gstBreakup'  => $this->gstBreakup($order),
            'amountWords' => AmountInWords::rupees((float) $order->total_amount),
            'invoiceDate' => ($order->placed_at ?? $order->created_at)?->format('d M Y, h:i A'),
        ];
    }

    /** Rendered PDF object; call ->stream() / ->download() / ->output() on it. */
    public function pdf(Order $order)
    {
        return Pdf::loadView('pdf.invoice', $this->data($order))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont'          => 'DejaVu Sans', // has the ₹ glyph
                'isRemoteEnabled'      => false,         // never fetch URLs while rendering
                'isHtml5ParserEnabled' => true,
                'dpi'                  => 96,
            ]);
    }

    public function filename(Order $order): string
    {
        return 'Invoice-' . preg_replace('/[^A-Za-z0-9\-]/', '', $order->order_number) . '.pdf';
    }

    /**
     * Logo embedded as a small base64 PNG. Remote loading is off, and the
     * source file is 512px / ~350KB — shrinking it keeps the PDF light.
     */
    private function logoDataUri(): ?string
    {
        $path = public_path(config('invoice.logo'));

        if (!is_file($path) || !function_exists('imagecreatefrompng')) {
            return null;
        }

        $src = @imagecreatefrompng($path);
        if (!$src) {
            return null;
        }

        $size = 140;
        $dst  = imagecreatetruecolor($size, $size);
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        imagefill($dst, 0, 0, imagecolorallocatealpha($dst, 0, 0, 0, 127));
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $size, $size, imagesx($src), imagesy($src));

        ob_start();
        imagepng($dst, null, 9);
        $png = ob_get_clean();

        return 'data:image/png;base64,' . base64_encode($png);
    }
}
