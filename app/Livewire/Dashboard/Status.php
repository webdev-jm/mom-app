<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\MomDetail;
use Illuminate\Support\Facades\DB;

class Status extends Component
{
    use InteractsWithDashboardFilters;

    public $statusColors = [
        'Overdue' => '#e15759',
        'Extended' => '#f28e2c',
        'On time' => '#59a14f',
        'Open' => '#4e79a7',
        'completed' => '#59a14f',
        'open' => '#4e79a7',
    ];

    public function render()
    {
        // Base query for MomDetail, applying user role and dashboard filters
        $baseQuery = MomDetail::query()
            ->whereHas('mom', function ($query) {
                $query->where('status', '<>', 'draft');
                $this->applyMeetingDateFilters($query);
            })
            ->when(!empty($this->filter_user_id), function ($query) {
                $query->whereHas('responsibles', function ($qry) {
                    $qry->where('id', $this->filter_user_id);
                });
            })
            ->when(!empty($this->filter_status), function ($query) {
                $query->whereRaw($this->derivedStatusSql() . ' = ?', [$this->filter_status]);
            });

        // Apply user-specific filtering if not superadmin or admin
        if (!auth()->user()->hasRole('superadmin') && !auth()->user()->hasRole('admin')) {
            $baseQuery->whereHas('mom', function ($qry) {
                $qry->whereHas('participants', function ($qry1) {
                    $qry1->where('id', auth()->user()->id);
                });
            });
        }

        // Get the aggregated status data
        $data = (clone $baseQuery)->select(
            DB::raw($this->derivedStatusSql() . ' as derived_status'),
            DB::raw('COUNT(*) as total')
        )
        ->groupBy('derived_status')
        ->get();

        $chartData = [];
        $drilldownData = [];
        $totalTopic = 0;

        foreach ($data as $val) {
            $derivedStatus = $val->derived_status;
            $total = $val->total;

            // Apply the status color, defaulting to a fallback if not found
            $color = $this->statusColors[$derivedStatus] ?? '#8a8d93';

            $chartData[] = [
                'name' => $derivedStatus,
                'y' => $total,
                'drilldown' => $derivedStatus, // Link to drilldown data
                'color' => $color // Include the color property for the chart
            ];

            // --- Prepare drilldown data for the current derived status ---
            $responsibleCounts = [];

            // Get the MomDetails that fall under this derived status
            $momDetailsForStatus = (clone $baseQuery)
                ->whereRaw($this->derivedStatusSql() . ' = ?', [$derivedStatus])
                ->with('responsibles')
                ->get();

            // Aggregate responsible persons for this derived status
            foreach ($momDetailsForStatus as $momDetail) {
                foreach ($momDetail->responsibles as $responsible) {
                    $name = $responsible->name ?? 'Unassigned'; // Fallback for unassigned or missing name
                    $responsibleCounts[$name] = ($responsibleCounts[$name] ?? 0) + 1;
                }
            }

            // Convert aggregated counts to the format required by Highcharts drilldown
            $drilldownItems = [];
            foreach ($responsibleCounts as $name => $count) {
                $drilldownItems[] = [$name, $count];
            }

            $drilldownData[] = [
                'id' => $derivedStatus,
                'data' => $drilldownItems
            ];

            $totalTopic += $total;
        }

        $this->dispatch('update-chart-1', data: $chartData, totalTopic: $totalTopic, drilldownData: $drilldownData);

        return view('livewire.dashboard.status');
    }
}
