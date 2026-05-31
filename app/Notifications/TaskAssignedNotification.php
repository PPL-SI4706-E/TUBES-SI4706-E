<?php

namespace App\Notifications;

use App\Models\Laporan;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * PBI-18 — Notifikasi dikirim ke Petugas ketika Admin menugaskan mereka ke laporan.
 */
class TaskAssignedNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected Laporan $laporan,
        protected User    $admin
    ) {}

    /**
     * Gunakan driver database (tabel notifications).
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Payload JSON yang disimpan di kolom `data`.
     * Format: { title, message, icon, link }
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type'    => 'new_task',
            'title'   => 'Tugas Baru Ditugaskan',
            'message' => "Anda ditugaskan ke laporan #{$this->laporan->id}: \"{$this->laporan->deskripsi_kerusakan}\" oleh Admin {$this->admin->name}.",
            'icon'    => 'wrench',
            'link'    => route('petugas.tugas.show', $this->laporan->penugasan?->id ?? 0),
        ];
    }
}
