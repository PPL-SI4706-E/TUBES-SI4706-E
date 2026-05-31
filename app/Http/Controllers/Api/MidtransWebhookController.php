<?php

namespace App\Http\Controllers\Api;

use App\Models\Pembayaran;
use App\Models\User;
use App\Notifications\PaymentReceivedNotification;
use App\Notifications\AdminSystemNotification;
use App\Notifications\GeneralSystemNotification;
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
                        if ($pembayaran->status_pembayaran !== 'Lunas') {
                            $pembayaran->status_pembayaran = 'Lunas';
                            
                            // PBI-18: Notifikasi Admin pembayaran lunas
                            $admins = User::where('role', 'admin')->get();
                            \Illuminate\Support\Facades\Notification::send($admins, new PaymentReceivedNotification($pembayaran));

                            // PBI-18: Notifikasi ke Warga (Pembuat Pembayaran)
                            if ($pembayaran->user) {
                                $pembayaran->user->notify(new GeneralSystemNotification(
                                    'Pembayaran Berhasil',
                                    "Pembayaran Anda sebesar Rp " . number_format($pembayaran->harga, 0, ',', '.') . " untuk Laporan #{$pembayaran->laporan_id} telah lunas.",
                                    route('warga.pembayaran.index'),
                                    'success'
                                ));
                            }
                        }
                        break;
                    case 'deny':
                    case 'cancel':
                    case 'expire':
                        if ($pembayaran->status_pembayaran !== 'Ditolak') {
                            $pembayaran->status_pembayaran = 'Ditolak';
                            
                            // PBI-18: Notifikasi Admin pembayaran gagal/kadaluarsa
                            $admins = User::where('role', 'admin')->get();
                            \Illuminate\Support\Facades\Notification::send($admins, new AdminSystemNotification(
                                'Pembayaran Gagal/Kedaluwarsa',
                                "Pembayaran untuk laporan #{$pembayaran->laporan_id} gagal atau telah kedaluwarsa. Status: {$status}",
                                route('admin.laporan.show', $pembayaran->laporan_id),
                                'error'
                            ));
                        }
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
