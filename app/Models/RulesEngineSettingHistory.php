<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RulesEngineSettingHistory extends Model
{
    protected $table = 'rules_engine_settings_history';

    protected $fillable = [
        'setting_id',
        'old_value',
        'new_value',
        'changed_by',
    ];

    protected $casts = [
        'setting_id' => 'integer',
        'changed_by' => 'integer',
    ];

    /**
     * Relationship: History belongs to a setting
     */
    public function setting(): BelongsTo
    {
        return $this->belongsTo(RulesEngineSetting::class, 'setting_id');
    }

    /**
     * Relationship: History belongs to a user (who made the change)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    /**
     * Scope: Latest changes first
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Scope: Filter by setting
     */
    public function scopeBySetting($query, int $settingId)
    {
        return $query->where('setting_id', $settingId);
    }

    /**
     * Scope: Filter by user
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('changed_by', $userId);
    }
}
