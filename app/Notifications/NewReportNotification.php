<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Laporan;

class NewReportNotification extends Notification
{
    use Queueable;

    protected $laporan;

    /**
     * Create a new notification instance.
     */
    public function __construct(Laporan $laporan)
    {
        $this->laporan = $laporan;
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
            'title'   => 'Laporan Baru Masuk',
            'message' => 'Laporan #' . $this->laporan->id . ' telah dibuat oleh ' . ($this->laporan->user->name ?? 'Warga') . '. Silakan periksa dan validasi.',
            'link'    => route('admin.laporan.show', $this->laporan->id),
            'type'    => 'new_report'
        ];
    }
}
