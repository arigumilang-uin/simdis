<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RulesEngineSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'label',
        'description',
        'category',
        'data_type',
        'validation_rules',
        'display_order',
    ];

    protected $casts = [
        'display_order' => 'integer',
    ];

    /**
     * Relationship: Setting memiliki banyak history
     */
    public function histories(): HasMany
    {
        return $this->hasMany(RulesEngineSettingHistory::class, 'setting_id');
    }

    /**
     * Scope: Filter by category
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope: Ordered by display_order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order');
    }

    /**
     * Get value as integer (untuk threshold poin dan frekuensi)
     */
    public function asInt(): int
    {
        return (int) $this->value;
    }

    /**
     * Get value as float (jika diperlukan untuk future expansion)
     */
    public function asFloat(): float
    {
        return (float) $this->value;
    }

    /**
     * Get value as boolean (jika diperlukan untuk future expansion)
     */
    public function asBool(): bool
    {
        return filter_var($this->value, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Static helper: Get setting value by key
     */
    public static function getValue(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Static helper: Get setting as integer by key
     */
    public static function getIntValue(string $key, int $default = 0): int
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->asInt() : $default;
    }

    /**
     * Static helper: Set setting value by key
     */
    public static function setValue(string $key, $value, ?int $changedBy = null): bool
    {
        $setting = static::where('key', $key)->first();
        
        if (!$setting) {
            return false;
        }

        $oldValue = $setting->value;
        $setting->value = $value;
        $setting->save();

        // Create history record
        RulesEngineSettingHistory::create([
            'setting_id' => $setting->id,
            'old_value' => $oldValue,
            'new_value' => $value,
            'changed_by' => $changedBy,
        ]);

        return true;
    }
}
