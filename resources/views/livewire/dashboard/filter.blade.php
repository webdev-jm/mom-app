<div>
    <div class="card dashboard-filter-card shadow-sm mb-3">
        <div class="card-body py-2">
            <div class="row align-items-end">
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="form-group mb-2">
                        <label for="filter-year" class="dashboard-filter-label">Year</label>
                        <select id="filter-year" class="form-control form-control-sm" wire:model.live="year" wire:loading.attr="disabled">
                            <option value="">All</option>
                            @foreach($years as $yearOption)
                                <option value="{{ $yearOption }}">{{ $yearOption }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="form-group mb-2">
                        <label for="filter-month-from" class="dashboard-filter-label">Month From</label>
                        <select id="filter-month-from" class="form-control form-control-sm" wire:model.live="month_from" wire:loading.attr="disabled">
                            <option value="">All</option>
                            @foreach($months as $num => $name)
                                <option value="{{ $num }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="form-group mb-2">
                        <label for="filter-month-to" class="dashboard-filter-label">Month To</label>
                        <select id="filter-month-to" class="form-control form-control-sm" wire:model.live="month_to" wire:loading.attr="disabled">
                            <option value="">All</option>
                            @foreach($months as $num => $name)
                                <option value="{{ $num }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="form-group mb-2" wire:ignore>
                        <label for="filter-user" class="dashboard-filter-label">User</label>
                        <select id="filter-user" class="form-control form-control-sm" style="width: 100%;">
                            <option value="">All</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="form-group mb-2">
                        <label for="filter-status" class="dashboard-filter-label">{{ ucfirst(__('adminlte::utilities.status')) }}</label>
                        <select id="filter-status" class="form-control form-control-sm" wire:model.live="status" wire:loading.attr="disabled">
                            <option value="">All</option>
                            @foreach($statuses as $statusOption)
                                <option value="{{ $statusOption }}">{{ $statusOption }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="form-group mb-2 text-right">
                        <button class="btn btn-sm btn-outline-secondary btn-block" wire:click.prevent="resetFilters" wire:loading.attr="disabled">
                            <i class="fa fa-undo"></i> Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @php
        $activeFilters = [];

        if (!empty($year)) {
            $activeFilters[] = ['label' => 'Year', 'value' => $year];
        }

        if (!empty($month_from) && !empty($month_to)) {
            $activeFilters[] = ['label' => 'Months', 'value' => $months[(int) $month_from] . ' - ' . $months[(int) $month_to]];
        } elseif (!empty($month_from)) {
            $activeFilters[] = ['label' => 'Months', 'value' => 'From ' . $months[(int) $month_from]];
        } elseif (!empty($month_to)) {
            $activeFilters[] = ['label' => 'Months', 'value' => 'Until ' . $months[(int) $month_to]];
        }

        if (!empty($user_id)) {
            $activeFilters[] = ['label' => 'User', 'value' => $users->firstWhere('id', (int) $user_id)->name ?? $user_id];
        }

        if (!empty($status)) {
            $activeFilters[] = ['label' => ucfirst(__('adminlte::utilities.status')), 'value' => $status];
        }
    @endphp

    <div class="dashboard-filter-banner shadow-sm mb-3">
        <span class="banner-lead">
            <i class="fa fa-filter"></i>
            <strong>Showing:</strong>
        </span>

        @forelse($activeFilters as $active)
            <span class="badge badge-pill filter-badge">{{ $active['label'] }}: {{ $active['value'] }}</span>
        @empty
            <span class="badge badge-pill filter-badge">All records</span>
        @endforelse

        <span class="banner-loading" wire:loading>
            <i class="fas fa-circle-notch fa-spin"></i> Applying filters...
        </span>
    </div>

    <script>
        document.addEventListener('livewire:init', function () {
            const $userFilter = $('#filter-user');

            $userFilter.select2({
                width: '100%'
            });

            $userFilter.on('change', function () {
                @this.set('user_id', this.value || '');
            });

            window.addEventListener('dashboard-filters-reset', () => {
                $userFilter.val('').trigger('change.select2');
            });
        });
    </script>
</div>
