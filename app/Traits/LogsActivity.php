<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Traits\LogsActivity as SpatieLogsActivity;
use Spatie\Activitylog\LogOptions;

/**
 * Trait LogsActivity
 * 
 * Enhanced activity logging with custom methods
 * Extends Spatie's LogsActivity trait
 */
trait LogsActivity
{
    use SpatieLogsActivity;

    /**
     * Get the options for logging
     * 
     * @return LogOptions
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->getLogAttributes())
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => $this->getActivityDescription($eventName));
    }

    /**
     * Get attributes to log
     * Override this in your model to specify which attributes to log
     * 
     * @return array
     */
    protected function getLogAttributes(): array
    {
        // Default: log all fillable attributes
        return $this->fillable ?? ['*'];
    }

    /**
     * Get activity description based on event
     * 
     * @param string $eventName
     * @return string
     */
    protected function getActivityDescription(string $eventName): string
    {
        $modelName = class_basename($this);
        $userName = Auth::user()?->nama ?? 'System';

        return match($eventName) {
            'created' => "{$userName} membuat {$modelName} baru",
            'updated' => "{$userName} mengubah {$modelName}",
            'deleted' => "{$userName} menghapus {$modelName}",
            default => "{$userName} melakukan {$eventName} pada {$modelName}",
        };
    }

    /**
     * Log custom activity
     * 
     * @param string $description
     * @param array $properties
     * @return void
     */
    public function logCustomActivity(string $description, array $properties = []): void
    {
        activity()
            ->performedOn($this)
            ->causedBy(Auth::user())
            ->withProperties($properties)
            ->log($description);
    }

    /**
     * Log action with specific event name
     * 
     * @param string $event
     * @param string $description
     * @param array $properties
     * @return void
     */
    public function logAction(string $event, string $description, array $properties = []): void
    {
        activity()
            ->performedOn($this)
            ->causedBy(Auth::user())
            ->event($event)
            ->withProperties($properties)
            ->log($description);
    }

    /**
     * Get activity log for this model
     * 
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    public function getActivityLog(int $limit = 10)
    {
        return $this->activities()
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Check if model has any activity logs
     * 
     * @return bool
     */
    public function hasActivityLog(): bool
    {
        return $this->activities()->exists();
    }
}
