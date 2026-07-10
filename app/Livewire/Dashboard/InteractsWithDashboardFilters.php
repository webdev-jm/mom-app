<?php

namespace App\Livewire\Dashboard;

use Livewire\Attributes\On;

/**
 * Shared dashboard filter state for the dashboard chart components.
 *
 * Listens for the `dashboard-filter-changed` event dispatched by the
 * Dashboard\Filter component and stores the selected values so each
 * chart can constrain its query on the next render.
 */
trait InteractsWithDashboardFilters
{
    public $filter_year = '';
    public $filter_month_from = '';
    public $filter_month_to = '';
    public $filter_user_id = '';
    public $filter_status = '';

    public function mountInteractsWithDashboardFilters(): void
    {
        $this->filter_year = (string) now()->year;
    }

    #[On('dashboard-filter-changed')]
    public function applyDashboardFilters($year = '', $month_from = '', $month_to = '', $user_id = '', $status = ''): void
    {
        $this->filter_year = $year;
        $this->filter_month_from = $month_from;
        $this->filter_month_to = $month_to;
        $this->filter_user_id = $user_id;
        $this->filter_status = $status;
    }

    /**
     * Constrain a query by the selected year and month range.
     */
    protected function applyMeetingDateFilters($query, string $column = 'meeting_date')
    {
        return $query
            ->when(!empty($this->filter_year), function ($qry) use ($column) {
                $qry->whereYear($column, $this->filter_year);
            })
            ->when(!empty($this->filter_month_from), function ($qry) use ($column) {
                $qry->whereMonth($column, '>=', $this->filter_month_from);
            })
            ->when(!empty($this->filter_month_to), function ($qry) use ($column) {
                $qry->whereMonth($column, '<=', $this->filter_month_to);
            });
    }

    /**
     * SQL expression deriving the display status of a mom detail row.
     */
    protected function derivedStatusSql(string $prefix = ''): string
    {
        return "
            CASE
                WHEN {$prefix}status = 'open' AND DATE(NOW()) > {$prefix}target_date THEN 'Overdue'
                WHEN {$prefix}status = 'completed' AND {$prefix}completed_date > {$prefix}target_date THEN 'Extended'
                WHEN {$prefix}status = 'completed' AND {$prefix}completed_date <= {$prefix}target_date THEN 'On time'
                WHEN {$prefix}status = 'open' AND DATE(NOW()) <= {$prefix}target_date THEN 'Open'
                ELSE {$prefix}status
            END";
    }
}
