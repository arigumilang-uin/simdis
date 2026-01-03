<?php

namespace App\Traits;

use Illuminate\Support\Collection;

/**
 * Trait HasStatistics
 * 
 * Provides common statistics calculation methods
 * Reduces code duplication in dashboard and data controllers
 */
trait HasStatistics
{
    /**
     * Calculate basic statistics from a collection
     * 
     * @param Collection $data
     * @param string $field
     * @return array
     */
    protected function calculateStats(Collection $data, string $field = 'poin'): array
    {
        if ($data->isEmpty()) {
            return [
                'total' => 0,
                'average' => 0,
                'max' => 0,
                'min' => 0,
                'sum' => 0,
            ];
        }

        return [
            'total' => $data->count(),
            'average' => round($data->avg($field), 2),
            'max' => $data->max($field),
            'min' => $data->min($field),
            'sum' => $data->sum($field),
        ];
    }

    /**
     * Group data by period (month/week/day)
     * 
     * @param Collection $data
     * @param string $dateField
     * @param string $period ('month', 'week', 'day')
     * @return array
     */
    protected function groupByPeriod(Collection $data, string $dateField = 'created_at', string $period = 'month'): array
    {
        $grouped = [];

        foreach ($data as $item) {
            $date = $item->{$dateField};
            
            switch ($period) {
                case 'month':
                    $key = $date->format('Y-m');
                    break;
                case 'week':
                    $key = $date->format('Y-W');
                    break;
                case 'day':
                    $key = $date->format('Y-m-d');
                    break;
                default:
                    $key = $date->format('Y-m');
            }

            if (!isset($grouped[$key])) {
                $grouped[$key] = [];
            }

            $grouped[$key][] = $item;
        }

        return $grouped;
    }

    /**
     * Calculate percentage
     * 
     * @param int|float $part
     * @param int|float $total
     * @param int $decimals
     * @return float
     */
    protected function calculatePercentage($part, $total, int $decimals = 2): float
    {
        if ($total == 0) {
            return 0;
        }

        return round(($part / $total) * 100, $decimals);
    }

    /**
     * Get top N items from collection
     * 
     * @param Collection $data
     * @param string $field
     * @param int $limit
     * @param string $order ('desc' or 'asc')
     * @return Collection
     */
    protected function getTopItems(Collection $data, string $field, int $limit = 10, string $order = 'desc'): Collection
    {
        return $order === 'desc' 
            ? $data->sortByDesc($field)->take($limit)
            : $data->sortBy($field)->take($limit);
    }

    /**
     * Calculate growth rate between two values
     * 
     * @param int|float $current
     * @param int|float $previous
     * @return array ['value' => float, 'percentage' => float, 'direction' => string]
     */
    protected function calculateGrowth($current, $previous): array
    {
        if ($previous == 0) {
            return [
                'value' => $current,
                'percentage' => $current > 0 ? 100 : 0,
                'direction' => $current > 0 ? 'up' : 'neutral',
            ];
        }

        $difference = $current - $previous;
        $percentage = round(($difference / $previous) * 100, 2);

        return [
            'value' => $difference,
            'percentage' => abs($percentage),
            'direction' => $difference > 0 ? 'up' : ($difference < 0 ? 'down' : 'neutral'),
        ];
    }

    /**
     * Format statistics for dashboard cards
     * 
     * @param string $title
     * @param int|float $value
     * @param string $icon
     * @param string $color
     * @param array $additional
     * @return array
     */
    protected function formatStatCard(string $title, $value, string $icon = 'fa-chart-line', string $color = 'primary', array $additional = []): array
    {
        return array_merge([
            'title' => $title,
            'value' => $value,
            'icon' => $icon,
            'color' => $color,
        ], $additional);
    }
}
