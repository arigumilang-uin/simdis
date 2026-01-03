<?php

namespace App\Notifications;

use App\Models\Siswa;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notifikasi untuk pembinaan internal siswa.
 * 
 * Dikirim ke pembina (Wali Kelas, Kaprodi, Waka, Kepsek) saat siswa
 * mencapai threshold poin tertentu yang memerlukan pembinaan internal.
 * 
 * Clean Architecture: Notification Layer
 */
class PembinaanInternalNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @param Siswa $siswa
     * @param array $rekomendasi
     */
    public function __construct(
        public Siswa $siswa,
        public array $rekomendasi
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Siswa Perlu Pembinaan Internal - ' . $this->siswa->nama)
            ->greeting('Halo, ' . $notifiable->nama)
            ->line('Siswa berikut memerlukan pembinaan internal:')
            ->line('**Nama Siswa:** ' . $this->siswa->nama)
            ->line('**Kelas:** ' . ($this->siswa->kelas->nama_kelas ?? '-'))
            ->line('**Total Poin:** ' . $this->rekomendasi['total_poin'] . ' poin')
            ->line('**Range:** ' . $this->rekomendasi['range_text'])
            ->line('')
            ->line('**Keterangan Pembinaan:**')
            ->line($this->rekomendasi['keterangan'])
            ->action('Lihat Detail Siswa', url('/siswa/' . $this->siswa->id))
            ->line('Harap segera melakukan tindak lanjut pembinaan sesuai keterangan di atas.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'pembinaan_internal',
            'siswa_id' => $this->siswa->id,
            'siswa_nama' => $this->siswa->nama,
            'siswa_kelas' => $this->siswa->kelas->nama_kelas ?? '-',
            'total_poin' => $this->rekomendasi['total_poin'],
            'range_text' => $this->rekomendasi['range_text'],
            'keterangan' => $this->rekomendasi['keterangan'],
            'pembina_roles' => $this->rekomendasi['pembina_roles'],
            'message' => sprintf(
                'Siswa %s (%s) dengan total %d poin perlu pembinaan: %s',
                $this->siswa->nama,
                $this->siswa->kelas->nama_kelas ?? '-',
                $this->rekomendasi['total_poin'],
                $this->rekomendasi['keterangan']
            ),
        ];
    }
}
