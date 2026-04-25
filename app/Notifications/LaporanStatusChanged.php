<?php

namespace App\Notifications;

use App\Models\Laporan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LaporanStatusChanged extends Notification
{
    use Queueable;

    public $laporan;
    public $message;

    /**
     * Create a new notification instance.
     */
    public function __construct(Laporan $laporan, $message)
    {
        $this->laporan = $laporan;
        $this->message = $message;
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
    public function toArray(object $notifiable): array
    {
        return [
            'laporan_id' => $this->laporan->id,
            'judul' => $this->laporan->judul,
            'status' => $this->laporan->status,
            'message' => $this->message,
        ];
    }
}
