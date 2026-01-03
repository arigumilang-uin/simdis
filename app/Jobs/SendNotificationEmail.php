<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

/**
 * Send Notification Email Job
 * 
 * Asynchronous job untuk mengirim notifikasi email.
 * 
 * DEVELOPMENT MODE:
 * - Menggunakan Log::info() untuk simulasi pengiriman
 * - Mail::to() di-comment sebagai TODO untuk production
 * 
 * PRODUCTION MODE:
 * - Uncomment Mail::to() dan configure SMTP
 * - Remove/comment Log::info() simulation
 */
class SendNotificationEmail implements ShouldQueue
{
    use Queueable;

    /**
     * Email recipient.
     *
     * @var string
     */
    protected string $email;

    /**
     * Email message/subject.
     *
     * @var string
     */
    protected string $message;

    /**
     * Additional data (optional).
     *
     * @var array
     */
    protected array $data;

    /**
     * Number of times the job may be attempted.
     *
     * @var int
     */
    public int $tries = 3;

    /**
     * Number of seconds to wait before retrying.
     *
     * @var int
     */
    public int $backoff = 60;

    /**
     * Create a new job instance.
     *
     * @param string $email
     * @param string $message
     * @param array $data
     */
    public function __construct(string $email, string $message, array $data = [])
    {
        $this->email = $email;
        $this->message = $message;
        $this->data = $data;
    }

    /**
     * Execute the job.
     * 
     * DEVELOPMENT: Simulate email sending dengan logging.
     */
    public function handle(): void
    {
        // DEVELOPMENT MODE: Simulasi pengiriman dengan logging
        Log::info("📧 [QUEUE JOB] Enqueuing email to {$this->email}: {$this->message}", [
            'email' => $this->email,
            'message' => $this->message,
            'data' => $this->data,
            'job_id' => $this->job?->getJobId(),
            'timestamp' => now()->toDateTimeString(),
        ]);

        // TODO: PRODUCTION MODE - Uncomment untuk kirim email asli
        // Mail::to($this->email)->send(new NotificationMail($this->message, $this->data));

        // Simulate processing time (untuk demo purpose)
        sleep(2);

        Log::info("✅ [QUEUE JOB] Email processing completed for {$this->email}");
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable $exception
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("❌ [QUEUE JOB] Failed to send email to {$this->email}", [
            'email' => $this->email,
            'message' => $this->message,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);

        // TODO: Notify admin about failed email
    }
}
