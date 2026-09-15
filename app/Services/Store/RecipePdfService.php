<?php

namespace App\Services\Store;

use App\Models\Order;
use App\Models\Recipe;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class RecipePdfService
{
    /**
     * Generate and stream or download an A5 Recipe PDF.
     *
     * @param  Recipe      $recipe
     * @param  Order|null  $order
     * @param  bool        $stream  True for browser inline preview, false for file download
     * @return \Illuminate\Http\Response
     */
    public function generateRecipePdf(Recipe $recipe, ?Order $order = null, bool $stream = false): Response
    {
        $currency = activeCurrency();

        $data = [
            'recipe'   => $recipe,
            'order'    => $order,
            'currency' => $currency,
            'appName'  => config('app.name', 'TVR Store'),
        ];

        $pdf = Pdf::loadView('pdf.recipe', $data);

        // Explicitly enforce A5 format (148mm x 210mm) portrait
        $pdf->setPaper('a5', 'portrait');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled'      => true,
            'defaultFont'          => 'sans-serif',
            'dpi'                  => 150,
        ]);

        $fileName = 'recipe-' . $recipe->slug . '-A5.pdf';

        if ($stream) {
            return $pdf->stream($fileName);
        }

        return $pdf->download($fileName);
    }

    /**
     * Generate and stream or download an A5 Order Receipt PDF.
     *
     * @param  Order  $order
     * @param  bool   $stream
     * @return \Illuminate\Http\Response
     */
    public function generateOrderReceiptPdf(Order $order, bool $stream = false): Response
    {
        $order->loadMissing(['details.product.thumbnail', 'shippingAddress', 'payments.gateway']);
        $currency = activeCurrency();
        $unlockedRecipes = $order->unlockedRecipes();

        $data = [
            'order'           => $order,
            'currency'        => $currency,
            'unlockedRecipes' => $unlockedRecipes,
            'appName'         => config('app.name', 'TVR Store'),
        ];

        $pdf = Pdf::loadView('pdf.order-receipt', $data);

        // Explicitly enforce A5 format (148mm x 210mm) portrait
        $pdf->setPaper('a5', 'portrait');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled'      => true,
            'defaultFont'          => 'sans-serif',
            'dpi'                  => 150,
        ]);

        $fileName = 'receipt-order-' . $order->id . '-A5.pdf';

        if ($stream) {
            return $pdf->stream($fileName);
        }

        return $pdf->download($fileName);
    }
}
