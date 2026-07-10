<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\Mom;

class Timeline extends Component
{
    use InteractsWithDashboardFilters;

    public function render()
    {
        // Define colors for different derived statuses
        $statusColors = [
            'Overdue' => '#e15759',
            'Extended' => '#f28e2c',
            'On time' => '#59a14f',
            'Open' => '#4e79a7',
            'completed' => '#59a14f',
            'open' => '#4e79a7',
        ];

        // Fetch Mom records, eager load details, and apply user role and dashboard filters
        $moms = Mom::orderBy('meeting_date')
             ->when(!auth()->user()->hasRole('superadmin') && !auth()->user()->hasRole('admin'), function($query) {
                 $query->where(function($qry) {
                     $qry->whereHas('participants', function($qry1) {
                         $qry1->where('id', auth()->user()->id);
                     })
                     ->orWhere('user_id', auth()->user()->id);
                 });
             })
             ->where('status', '<>', 'draft')
             ->tap(function ($query) {
                 $this->applyMeetingDateFilters($query);
             })
             ->when(!empty($this->filter_user_id), function ($query) {
                 $query->whereHas('details.responsibles', function ($qry) {
                     $qry->where('id', $this->filter_user_id);
                 });
             })
             ->when(!empty($this->filter_status), function ($query) {
                 $query->whereHas('details', function ($qry) {
                     $qry->whereRaw($this->derivedStatusSql() . ' = ?', [$this->filter_status]);
                 });
             })
             ->with('details.responsibles') // Eager load details to avoid N+1 query problem in the loop
             ->get();

        $chartData = [];
        $drilldownData = []; // Initialize drilldownData array for Highcharts drilldown

        foreach($moms as $mom) {
            // Keep only the details matching the selected user and status filters
            $details = $mom->details
                ->filter(function ($detail) {
                    if (!empty($this->filter_user_id) && !$detail->responsibles->contains('id', (int) $this->filter_user_id)) {
                        return false;
                    }

                    if (!empty($this->filter_status) && $this->deriveDetailStatus($detail) !== $this->filter_status) {
                        return false;
                    }

                    return true;
                })
                ->values();

            // Find the detail with the latest target_date for the 'end' point of the timeline bar
            $longestDetail = $details->sortByDesc('target_date')->first();

            if(!empty($longestDetail)) {
                // Calculate the percentage of completed details for this MOM
                $totalDetails = $details->count();
                $completedDetails = $details->where('status', 'completed')->count();
                $completionPercentage = $totalDetails > 0 ? $completedDetails / $totalDetails : 0;

                // Create a unique ID for this MOM's drilldown series
                $momDrilldownId = 'mom-' . $mom->id;

                // Add data for the main timeline chart
                $chartData[] = [
                    'start' => $mom->meeting_date,
                    'end'   => $longestDetail->target_date,
                    'completed' => [
                        'amount' => $completionPercentage,
                        'fill' => '#59a14f', // Color for the completion fill
                    ],
                    'color' => '#4e79a7', // Color for the main timeline bar
                    'name' => $mom->mom_number,
                    'title' => $mom->agenda ?? '',
                    'status' => $mom->status ?? '',
                    'drilldown' => $momDrilldownId, // Link to the drilldown data for this MOM
                ];

                // --- Prepare drilldown data for the current MOM's details ---
                $drilldownItems = [];

                foreach ($details as $detail) {
                    $derivedStatus = $this->deriveDetailStatus($detail);

                    // Add the individual MomDetail as a drilldown data point
                    $drilldownItems[] = [
                        'name' => $detail->topic,
                        'start' => $mom->meeting_date, // Start from the MoM meeting date
                        'end' => $detail->target_date, // End at the detail's target date
                        'color' => $statusColors[$derivedStatus] ?? '#8a8d93',
                        'next_step' => $detail->next_step,
                        'status'    => $derivedStatus
                    ];
                }

                // Add the drilldown series for this MOM
                $drilldownData[] = [
                    'id' => $momDrilldownId, // Must match the 'drilldown' key in chartData
                    'name' => 'Details for ' . $mom->mom_number, // Name for the drilldown series
                    'data' => $drilldownItems
                ];
            }
        }

        // Dispatch the event to update the chart, including the new drilldownData
        $this->dispatch('update-chart-3', data: $chartData, drilldownData: $drilldownData);

        return view('livewire.dashboard.timeline');
    }

    /**
     * Derive the display status of a detail, mirroring the SQL CASE expression.
     */
    protected function deriveDetailStatus($detail): string
    {
        if ($detail->status === 'open' && now()->greaterThan($detail->target_date)) {
            return 'Overdue';
        }

        if ($detail->status === 'completed' && !empty($detail->completed_date) && $detail->completed_date->greaterThan($detail->target_date)) {
            return 'Extended';
        }

        if ($detail->status === 'completed' && !empty($detail->completed_date) && $detail->completed_date->lessThanOrEqualTo($detail->target_date)) {
            return 'On time';
        }

        if ($detail->status === 'open' && now()->lessThanOrEqualTo($detail->target_date)) {
            return 'Open';
        }

        return $detail->status; // Fallback for any other custom statuses
    }
}
