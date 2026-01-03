<?php

namespace App\Notifications;

use App\Models\TindakLanjut;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notifikasi untuk Kepala Sekolah saat ada kasus baru yang butuh approval.
 * 
 * Triggered when:
 * - Surat 3 atau Surat 4 dibuat (status: Menunggu Persetujuan)
 * 
 * Channels:
 * - Email: Immediate notification
 * - Database: For in-app badge counter
 */
class KasusButuhApprovalNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @var TindakLanjut
     */
    protected $tindakLanjut;

    /**
     * Create a new notification instance.
     */
    public function __construct(TindakLanjut $tindakLanjut)
    {
        $this->tindakLanjut = $tindakLanjut;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $siswa = $this->tindakLanjut->siswa;
        $surat = $this->tindakLanjut->suratPanggilan;
        $tipeSurat = $surat ? $surat->tipe_surat : 'N/A';
        
        return (new MailMessage)
            ->subject("Kasus Baru Butuh Persetujuan: {$siswa->nama_siswa}")
            ->greeting("Yth. Bapak/Ibu Kepala Sekolah,")
            ->line("Ada kasus pelanggaran siswa yang memerlukan persetujuan Anda.")
            ->line("**Detail Kasus:**")
            ->line("- Siswa: {$siswa->nama_siswa} ({$siswa->nisn})")
            ->line("- Kelas: {$siswa->kelas->nama_kelas}")
            ->line("- Tipe Surat: {$tipeSurat}")
            ->line("- Pemicu: {$this->tindakLanjut->pemicu}")
            ->action('Tinjau Kasus', route('kepala-sekolah.approvals.show', $this->tindakLanjut->id))
            ->line('Mohon segera ditinjau untuk kelancaran proses pembinaan siswa.')
            ->salutation('Hormat kami, Sistem Kedisiplinan SMKN 1 Siak');
    }

    /**
     * Get the array representation of the notification (for database storage).
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $siswa = $this->tindakLanjut->siswa;
        $surat = $this->tindakLanjut->suratPanggilan;
        
        return [
            'tindak_lanjut_id' => $this->tindakLanjut->id,
            'siswa_id' => $siswa->id,
            'siswa_nama' => $siswa->nama_siswa,
            'siswa_nisn' => $siswa->nisn,
            'kelas' => $siswa->kelas->nama_kelas ?? 'N/A',
            'tipe_surat' => $surat ? $surat->tipe_surat : 'N/A',
            'pemicu' => $this->tindakLanjut->pemicu,
            'url' => route('kepala-sekolah.approvals.show', $this->tindakLanjut->id),
        ];
    }
}
