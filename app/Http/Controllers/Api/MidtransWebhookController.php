<?php

namespace App\Http\Controllers\Api;

use App\Models\Pembayaran;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class MidtransWebhookController extends Controller
{
    /**
     * Handle Midtrans webhook callback.
     * Maps Midtrans transaction status to local status_pembayaran.
     */
    public function handle(Request $request)
    {
        $payload = $request->all();
        if (isset($payload['order_id']) && preg_match('/^pembayaran-(\d+)(?:-\d+)?$/', $payload['order_id'], $matches)) {
            $pembayaranId = $matches[1];
            $pembayaran = Pembayaran::find($pembayaranId);
            if ($pembayaran) {
                $status = $payload['transaction_status'] ?? null;
                switch ($status) {
                    case 'capture':
                    case 'settlement':
                        $pembayaran->status_pembayaran = 'Lunas';
                        break;
                    case 'deny':
                    case 'cancel':
                    case 'expire':
                        $pembayaran->status_pembayaran = 'Ditolak';
                        break;
                    default:
                        $pembayaran->status_pembayaran = 'Menunggu';
                }
                $pembayaran->save();
            }
        }
        return response('OK', 200);
    }
}
?>
