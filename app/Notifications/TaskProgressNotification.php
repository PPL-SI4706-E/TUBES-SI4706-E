<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Penugasan;

class TaskProgressNotification extends Notification
{
    use Queueable;

    protected $penugasan;
    protected $statusBaru;
    protected $isSelesai;

    /**
     * Create a new notification instance.
     */
    public function __construct(Penugasan $penugasan, $statusBaru, $isSelesai = false)
    {
        $this->penugasan = $penugasan;
        $this->statusBaru = $statusBaru;
        $this->isSelesai = $isSelesai;
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
        if ($this->isSelesai) {
            return [
                'title'   => 'Laporan Selesai Dikerjakan',
                'message' => 'Petugas telah menyelesaikan perbaikan untuk laporan #' . $this->penugasan->laporan_id . '. Mohon konfirmasi penyelesaian tugas ini.',
                'link'    => route('warga.laporan.show', $this->penugasan->laporan_id),
                'type'    => 'task_completed'
            ];
        }

        return [
            'title'   => 'Progres Laporan Diperbarui',
            'message' => 'Status pengerjaan laporan #' . $this->penugasan->laporan_id . ' saat ini: ' . $this->statusBaru . '.',
            'link'    => route('warga.laporan.show', $this->penugasan->laporan_id),
            'type'    => 'task_progress'
        ];
    }
}
