<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\Mom;
use App\Models\User;

class Filter extends Component
{
    public $year = '';
    public $month_from = '';
    public $month_to = '';
    public $user_id = '';
    public $status = '';

    /**
     * Derived statuses used across the dashboard charts.
     *
     * @var array<int, string>
     */
    public $statuses = ['Open', 'Overdue', 'On time', 'Extended'];

    public function mount(): void
    {
        $this->year = (string) now()->year;
    }

    public function updated(): void
    {
        $this->dispatchFilters();
    }

    public function resetFilters(): void
    {
        $this->year = (string) now()->year;
        $this->month_from = '';
        $this->month_to = '';
        $this->user_id = '';
        $this->status = '';

        $this->dispatch('dashboard-filters-reset');
        $this->dispatchFilters();
    }

    protected function dispatchFilters(): void
    {
        $this->dispatch('dashboard-filter-changed',
            year: $this->year,
            month_from: $this->month_from,
            month_to: $this->month_to,
            user_id: $this->user_id,
            status: $this->status,
        );
    }

    public function render()
    {
        $years = Mom::query()
            ->where('status', '<>', 'draft')
            ->selectRaw('DISTINCT YEAR(meeting_date) as year')
            ->orderByDesc('year')
            ->pluck('year')
            ->filter()
            ->values();

        if (!$years->contains(now()->year)) {
            $years->prepend(now()->year);
        }

        $users = User::query()->orderBy('name')->get(['id', 'name']);

        return view('livewire.dashboard.filter')->with([
            'years' => $years,
            'users' => $users,
            'months' => [
                1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
            ],
        ]);
    }
}
