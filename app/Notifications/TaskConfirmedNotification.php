<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Laporan;

class TaskConfirmedNotification extends Notification
{
    use Queueable;

    protected $laporan;
    protected $isRevisi;

    /**
     * Create a new notification instance.
     */
    public function __construct(Laporan $laporan, $isRevisi = false)
    {
        $this->laporan = $laporan;
        $this->isRevisi = $isRevisi;
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
        if ($this->isRevisi) {
            return [
                'title'   => 'Revisi Tugas Diminta',
                'message' => 'Warga meminta revisi untuk laporan #' . $this->laporan->id . '. Silakan cek detail laporan.',
                'link'    => route('petugas.tugas.show', $this->laporan->penugasan->id ?? 0),
                'type'    => 'task_revision'
            ];
        }

        return [
            'title'   => 'Laporan Dikonfirmasi Selesai',
            'message' => 'Warga telah mengonfirmasi penyelesaian tugas laporan #' . $this->laporan->id . ' dan memberikan ulasan.',
            'link'    => route('petugas.tugas.show', $this->laporan->penugasan->id ?? 0),
            'type'    => 'task_confirmed'
        ];
    }
}
