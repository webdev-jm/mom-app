<?php

namespace Tests\Feature;

use App\Livewire\Dashboard\Filter;
use App\Livewire\Dashboard\Status;
use App\Livewire\Dashboard\Timeline;
use App\Livewire\Dashboard\UserCompleted;
use App\Models\Mom;
use App\Models\MomDetail;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Uses DatabaseTransactions (not RefreshDatabase) so the development
 * database is never wiped; all rows created here are rolled back.
 * Assertions are scoped to freshly created users so existing data
 * cannot affect the results.
 */
class DashboardFilterTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        Role::findOrCreate('superadmin', 'web');

        $this->admin = User::factory()->create();
        $this->admin->assignRole('superadmin');
    }

    protected function createMomWithDetail(string $meetingDate, string $detailStatus = 'open', ?User $responsible = null): MomDetail
    {
        $mom = Mom::factory()->create(['meeting_date' => $meetingDate]);

        $detail = MomDetail::create([
            'mom_id' => $mom->id,
            'topic' => 'Test topic',
            'next_step' => 'Test next step',
            'target_date' => now()->addWeek(),
            'completed_date' => $detailStatus === 'completed' ? now() : null,
            'status' => $detailStatus,
        ]);

        if ($responsible) {
            $detail->responsibles()->attach($responsible->id);
        }

        return $detail;
    }

    /** @test */
    public function filter_component_defaults_to_current_year_with_other_filters_empty(): void
    {
        Livewire::actingAs($this->admin)
            ->test(Filter::class)
            ->assertSet('year', (string) now()->year)
            ->assertSet('month_from', '')
            ->assertSet('month_to', '')
            ->assertSet('user_id', '')
            ->assertSet('status', '');
    }

    /** @test */
    public function filter_component_dispatches_event_when_a_filter_changes(): void
    {
        Livewire::actingAs($this->admin)
            ->test(Filter::class)
            ->set('status', 'Open')
            ->assertDispatched('dashboard-filter-changed', status: 'Open');
    }

    /** @test */
    public function filter_component_reset_restores_defaults_and_dispatches(): void
    {
        Livewire::actingAs($this->admin)
            ->test(Filter::class)
            ->set('month_from', '3')
            ->set('status', 'Overdue')
            ->call('resetFilters')
            ->assertSet('year', (string) now()->year)
            ->assertSet('month_from', '')
            ->assertSet('status', '')
            ->assertDispatched('dashboard-filter-changed', status: '');
    }

    /** @test */
    public function filter_component_shows_a_banner_of_active_filters(): void
    {
        $responsible = User::factory()->create();

        Livewire::actingAs($this->admin)
            ->test(Filter::class)
            ->assertSeeText('Showing:')
            ->assertSeeText('Year: ' . now()->year)
            ->set('month_from', '3')
            ->set('month_to', '6')
            ->set('user_id', (string) $responsible->id)
            ->set('status', 'Open')
            ->assertSeeText('Months: March - June')
            ->assertSeeText('User: ' . $responsible->name)
            ->assertSeeText('Status: Open');
    }

    /** @test */
    public function status_chart_defaults_to_current_year(): void
    {
        $responsible = User::factory()->create();

        $this->createMomWithDetail(now()->format('Y-m-d'), 'open', $responsible);
        $this->createMomWithDetail(now()->subYear()->format('Y-m-d'), 'open', $responsible);

        Livewire::actingAs($this->admin)
            ->test(Status::class)
            ->set('filter_user_id', (string) $responsible->id)
            ->assertDispatched('update-chart-1', function ($event, $params) {
                return $params['totalTopic'] === 1;
            });
    }

    /** @test */
    public function status_chart_applies_filters_from_the_filter_event(): void
    {
        $responsible = User::factory()->create();

        $this->createMomWithDetail(now()->format('Y-m-d'), 'open', $responsible);
        $this->createMomWithDetail(now()->format('Y-m-d'), 'completed', $responsible);

        Livewire::actingAs($this->admin)
            ->test(Status::class)
            ->dispatch('dashboard-filter-changed',
                year: (string) now()->year,
                month_from: '',
                month_to: '',
                user_id: (string) $responsible->id,
                status: 'Open',
            )
            ->assertSet('filter_user_id', (string) $responsible->id)
            ->assertSet('filter_status', 'Open')
            ->assertDispatched('update-chart-1', function ($event, $params) {
                return $params['totalTopic'] === 1
                    && $params['data'][0]['name'] === 'Open';
            });
    }

    /** @test */
    public function status_chart_month_range_filter_limits_results(): void
    {
        $year = now()->year;
        $responsible = User::factory()->create();

        $this->createMomWithDetail("{$year}-02-15", 'open', $responsible);
        $this->createMomWithDetail("{$year}-08-15", 'open', $responsible);

        Livewire::actingAs($this->admin)
            ->test(Status::class)
            ->dispatch('dashboard-filter-changed',
                year: (string) $year,
                month_from: '1',
                month_to: '3',
                user_id: (string) $responsible->id,
                status: '',
            )
            ->assertDispatched('update-chart-1', function ($event, $params) {
                return $params['totalTopic'] === 1;
            });
    }

    /** @test */
    public function user_completed_chart_filters_by_user(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $this->createMomWithDetail(now()->format('Y-m-d'), 'open', $userA);
        $this->createMomWithDetail(now()->format('Y-m-d'), 'completed', $userB);

        Livewire::actingAs($this->admin)
            ->test(UserCompleted::class)
            ->dispatch('dashboard-filter-changed',
                year: (string) now()->year,
                month_from: '',
                month_to: '',
                user_id: (string) $userA->id,
                status: '',
            )
            ->assertDispatched('update-chart-2', function ($event, $params) use ($userA) {
                return $params['data']['categories'] === [$userA->name];
            });
    }

    /** @test */
    public function timeline_chart_defaults_to_current_year(): void
    {
        $responsible = User::factory()->create();

        $currentYearDetail = $this->createMomWithDetail(now()->format('Y-m-d'), 'open', $responsible);
        $this->createMomWithDetail(now()->subYear()->format('Y-m-d'), 'open', $responsible);

        Livewire::actingAs($this->admin)
            ->test(Timeline::class)
            ->set('filter_user_id', (string) $responsible->id)
            ->assertDispatched('update-chart-3', function ($event, $params) use ($currentYearDetail) {
                return count($params['data']) === 1
                    && $params['data'][0]['name'] === $currentYearDetail->mom->mom_number;
            });
    }

    /** @test */
    public function timeline_completion_amount_is_rounded_to_two_decimals(): void
    {
        $responsible = User::factory()->create();
        $mom = Mom::factory()->create(['meeting_date' => now()->format('Y-m-d')]);

        foreach (['completed', 'completed', 'open'] as $status) {
            $detail = MomDetail::create([
                'mom_id' => $mom->id,
                'topic' => 'Test topic',
                'next_step' => 'Test next step',
                'target_date' => now()->addWeek(),
                'completed_date' => $status === 'completed' ? now() : null,
                'status' => $status,
            ]);

            $detail->responsibles()->attach($responsible->id);
        }

        Livewire::actingAs($this->admin)
            ->test(Timeline::class)
            ->set('filter_user_id', (string) $responsible->id)
            ->assertDispatched('update-chart-3', function ($event, $params) {
                return $params['data'][0]['completed']['amount'] === 0.67;
            });
    }

    /** @test */
    public function dashboard_page_renders_the_filter_and_chart_components(): void
    {
        $this->actingAs($this->admin)
            ->get(route('home'))
            ->assertStatus(200)
            ->assertSeeLivewire(Filter::class)
            ->assertSeeLivewire(Status::class)
            ->assertSeeLivewire(UserCompleted::class)
            ->assertSeeLivewire(Timeline::class);
    }
}
