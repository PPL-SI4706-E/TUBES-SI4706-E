<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Pembayaran;

class PaymentReceivedNotification extends Notification
{
    use Queueable;

    protected $pembayaran;

    /**
     * Create a new notification instance.
     */
    public function __construct(Pembayaran $pembayaran)
    {
        $this->pembayaran = $pembayaran;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'title'   => 'Pembayaran Lunas',
            'message' => 'Pembayaran untuk laporan #' . $this->pembayaran->laporan_id . ' telah berhasil dilunasi sebesar Rp ' . number_format($this->pembayaran->harga, 0, ',', '.') . '.',
            'link'    => route('admin.laporan.show', $this->pembayaran->laporan_id),
            'type'    => 'payment_proof'
        ];
    }
}
