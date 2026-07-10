<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\Mom;
use Illuminate\Support\Facades\DB;

class UserCompleted extends Component
{
    use InteractsWithDashboardFilters;

    public function render()
    {
        $data = Mom::query()
            ->select(
                'u.name',
                DB::raw('COUNT(md.id) as total'),
                DB::raw("SUM(CASE WHEN md.status = 'Completed' THEN 1 ELSE 0 END) as completed_total")
            )
            ->leftJoin('mom_details as md', 'md.mom_id', '=', 'moms.id')
            ->leftJoin('mom_responsibles as mr', 'mr.mom_detail_id', '=', 'md.id')
            ->leftJoin('users as u', 'u.id', '=', 'mr.user_id')
            ->whereNotNull('u.name')
            ->where('moms.status', '<>', 'draft')
            ->tap(function ($query) {
                $this->applyMeetingDateFilters($query, 'moms.meeting_date');
            })
            ->when(!empty($this->filter_user_id), function ($query) {
                $query->where('mr.user_id', $this->filter_user_id);
            })
            ->when(!empty($this->filter_status), function ($query) {
                $query->whereRaw($this->derivedStatusSql('md.') . ' = ?', [$this->filter_status]);
            })
            ->when(!auth()->user()->hasRole('superadmin') && !auth()->user()->hasRole('admin'), function($query) {
                $query->where(function($qry) {
                    $qry->whereHas('participants', function($qry1) {
                        $qry1->where('id', auth()->user()->id);
                    })
                    ->orWhere('moms.user_id', auth()->user()->id);
                });
            })
            ->groupBy('u.name')
            ->get();

        $categories = $data->pluck('name')->unique()->values()->all();

        $series = [
            [
                'name' => 'Open',
                'data' => [],
            ],
            [
                'name' => 'Completed',
                'data' => [],
            ],
        ];

        foreach ($data as $val) {
            $series[0]['data'][] = (int) $val->total - (int) $val->completed_total;
            $series[1]['data'][] = (int) $val->completed_total;
        }

        $chartData = [
            'categories' => $categories,
            'series' => $series,
        ];

        $this->dispatch('update-chart-2', data: $chartData);

        return view('livewire.dashboard.user-completed');
    }
}
