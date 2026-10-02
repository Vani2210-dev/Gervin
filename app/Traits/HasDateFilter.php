<?php

namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait HasDateFilter
{
    /**
     * Parse date filter from request.
     * Supports:
     * - date_preset: today, yesterday, 7_days, this_month, last_month, this_year, all, custom
     * - start_date, end_date (or from_date, to_date, filter_start_date, filter_end_date)
     * - date_mode, date_val (legacy)
     */
    public function getDateFilterParams(?Request $request = null): array
    {
        $request = $request ?? request();

        $preset = $request->input('date_preset');
        $startDate = $request->input('start_date') 
            ?? $request->input('from_date') 
            ?? $request->input('filter_start_date');
        $endDate = $request->input('end_date') 
            ?? $request->input('to_date') 
            ?? $request->input('filter_end_date');

        // Check legacy date_mode & date_val
        $dateMode = $request->input('date_mode');
        $dateVal = $request->input('date_val');

        if (!$preset && $dateMode) {
            if ($dateMode === 'day' && $dateVal) {
                $startDate = $dateVal;
                $endDate = $dateVal;
                $preset = ($dateVal === Carbon::today()->toDateString()) ? 'today' : 'custom';
            } elseif ($dateMode === 'month' && $dateVal) {
                try {
                    $c = Carbon::parse($dateVal . '-01');
                    $startDate = $c->copy()->startOfMonth()->toDateString();
                    $endDate = $c->copy()->endOfMonth()->toDateString();
                    $preset = ($dateVal === Carbon::now()->format('Y-m')) ? 'this_month' : 'custom';
                } catch (\Exception $e) {}
            } elseif ($dateMode === 'year' && $dateVal) {
                $startDate = $dateVal . '-01-01';
                $endDate = $dateVal . '-12-31';
                $preset = ($dateVal === Carbon::now()->format('Y')) ? 'this_year' : 'custom';
            } elseif ($dateMode === 'all') {
                $preset = 'all';
                $startDate = null;
                $endDate = null;
            }
        }

        // Handle presets
        if ($preset) {
            switch ($preset) {
                case 'today':
                    $startDate = Carbon::today()->toDateString();
                    $endDate = Carbon::today()->toDateString();
                    break;
                case 'yesterday':
                    $startDate = Carbon::yesterday()->toDateString();
                    $endDate = Carbon::yesterday()->toDateString();
                    break;
                case '7_days':
                    $startDate = Carbon::today()->subDays(6)->toDateString();
                    $endDate = Carbon::today()->toDateString();
                    break;
                case 'this_month':
                    $startDate = Carbon::now()->startOfMonth()->toDateString();
                    $endDate = Carbon::now()->endOfMonth()->toDateString();
                    break;
                case 'last_month':
                    $startDate = Carbon::now()->subMonth()->startOfMonth()->toDateString();
                    $endDate = Carbon::now()->subMonth()->endOfMonth()->toDateString();
                    break;
                case 'this_year':
                    $startDate = Carbon::now()->startOfYear()->toDateString();
                    $endDate = Carbon::now()->endOfYear()->toDateString();
                    break;
                case 'all':
                    $startDate = null;
                    $endDate = null;
                    break;
                case 'custom':
                default:
                    // Keep start_date and end_date as provided
                    break;
            }
        } elseif ($startDate || $endDate) {
            $preset = 'custom';
        } else {
            $preset = 'all';
            $startDate = null;
            $endDate = null;
        }

        // Generate human-friendly label
        $label = 'Toàn thời gian';
        if ($startDate && $endDate) {
            if ($startDate === $endDate) {
                $label = 'Ngày ' . Carbon::parse($startDate)->format('d/m/Y');
            } else {
                $label = Carbon::parse($startDate)->format('d/m/Y') . ' - ' . Carbon::parse($endDate)->format('d/m/Y');
            }
        } elseif ($startDate) {
            $label = 'Từ ' . Carbon::parse($startDate)->format('d/m/Y');
        } elseif ($endDate) {
            $label = 'Đến ' . Carbon::parse($endDate)->format('d/m/Y');
        }

        return [
            'preset' => $preset,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'label' => $label,
            'is_filtered' => ($preset !== 'all' && ($startDate || $endDate)),
        ];
    }

    /**
     * Apply date filter query conditions to Eloquent builder.
     */
    public function applyDateFilter(Builder $query, ?Request $request = null, string $column = 'created_at', ?array &$dateFilterData = null): Builder
    {
        $params = $this->getDateFilterParams($request);
        $dateFilterData = $params;

        if ($params['start_date'] && $params['end_date']) {
            if ($params['start_date'] === $params['end_date']) {
                $query->whereDate($column, $params['start_date']);
            } else {
                $query->whereDate($column, '>=', $params['start_date'])
                      ->whereDate($column, '<=', $params['end_date']);
            }
        } elseif ($params['start_date']) {
            $query->whereDate($column, '>=', $params['start_date']);
        } elseif ($params['end_date']) {
            $query->whereDate($column, '<=', $params['end_date']);
        }

        return $query;
    }
}
