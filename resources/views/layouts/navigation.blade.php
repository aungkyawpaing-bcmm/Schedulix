@php
    $contextProject = \App\Models\Project::query()
        ->when(! auth()->user()->isOwner(), function ($query) {
            $query->where(function ($scopedQuery) {
                $scopedQuery
                    ->where('project_manager_id', auth()->id())
                    ->orWhereHas('members', fn ($members) => $members->where('user_id', auth()->id()));
            });
        })
        ->latest()
        ->first();

    $links = [
        ['label' => __('ui.navigation.dashboard'), 'route' => 'dashboard'],
        ['label' => __('ui.navigation.projects'), 'route' => 'projects.index'],
        ['label' => __('ui.navigation.task_master'), 'route' => 'task-master.index'],
        ['label' => __('ui.navigation.holidays'), 'route' => 'holidays.index'],
        ['label' => __('ui.navigation.working_hours'), 'route' => 'working-hours.index'],
        ['label' => __('ui.navigation.assignments'), 'route' => $contextProject ? route('assignments.index', $contextProject) : null],
        ['label' => __('ui.navigation.wbs_builder'), 'route' => $contextProject ? route('wbs-builder.index', $contextProject) : null],
        ['label' => __('ui.navigation.schedule'), 'route' => $contextProject ? route('schedule.show', $contextProject) : null],
        ['label' => __('ui.navigation.exports'), 'route' => 'exports.index'],
        ['label' => __('ui.navigation.notifications'), 'route' => 'notifications.index'],
        ['label' => __('ui.navigation.settings'), 'route' => 'settings.index'],
    ];

    if (auth()->user()->isOwner()) {
        array_splice($links, 2, 0, [[
            'label' => __('ui.navigation.pics'),
            'route' => 'pics.index',
        ]]);
    }
@endphp

<aside class="hidden shrink-0 transition-all duration-300 lg:block" :class="collapsed ? 'w-24' : 'w-72'">
    <div class="schedulix-panel sticky top-4 space-y-4 rounded-3xl p-5">
        <div class="flex items-start justify-between gap-3 rounded-2xl bg-gradient-to-br from-sky-500/20 via-cyan-400/10 to-emerald-400/10 p-4">
            <div class="min-w-0" x-show="!collapsed" x-transition.opacity>
                <p class="text-xs uppercase tracking-[0.35em] text-sky-200/70">{{ __('ui.app_name') }}</p>
                <h1 class="mt-2 text-2xl font-semibold text-white">{{ __('ui.navigation.tagline_title') }}</h1>
                <p class="mt-2 text-sm text-slate-300">{{ __('ui.navigation.tagline_body') }}</p>
            </div>
            <div class="flex flex-col items-center gap-3" :class="collapsed ? 'w-full' : ''">
                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white/70 text-lg font-semibold text-slate-900">S</div>
                <button
                    type="button"
                    @click="toggleSidebar()"
                    class="flex h-10 w-10 items-center justify-center rounded-2xl border border-white/10 bg-white/70 text-lg font-semibold text-slate-900 transition hover:bg-white"
                    :aria-expanded="(!collapsed).toString()"
                    aria-label="{{ __('ui.common.toggle_side_menu') }}"
                >
                    <span x-text="collapsed ? '»' : '«'"></span>
                </button>
            </div>
        </div>

        <nav class="space-y-1">
            @foreach ($links as $link)
                @can('viewAny', \App\Models\Project::class)
                    @if (! empty($link['route']))
                        <a
                            href="{{ str_contains($link['route'], 'http') ? $link['route'] : route($link['route']) }}"
                            class="{{ request()->url() === $link['route'] || request()->routeIs($link['route']) ? 'bg-sky-500/20 text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white' }} flex items-center rounded-2xl px-4 py-3 text-sm font-medium transition"
                            :class="collapsed ? 'justify-center px-2' : ''"
                            title="{{ $link['label'] }}"
                        >
                            <span class="schedulix-nav-badge shrink-0">{{ mb_substr($link['label'], 0, 1) }}</span>
                            <span class="ml-3 truncate" x-show="!collapsed" x-transition.opacity>{{ $link['label'] }}</span>
                        </a>
                    @else
                        <span class="flex items-center rounded-2xl px-4 py-3 text-sm font-medium text-slate-500" :class="collapsed ? 'justify-center px-2' : ''" title="{{ $link['label'] }}">
                            <span class="schedulix-nav-badge shrink-0">{{ mb_substr($link['label'], 0, 1) }}</span>
                            <span class="ml-3 truncate" x-show="!collapsed" x-transition.opacity>{{ $link['label'] }}</span>
                        </span>
                    @endif
                @endcan
            @endforeach
        </nav>

        <div class="rounded-2xl border border-white/10 bg-white/5 p-4 text-sm text-slate-300" :class="collapsed ? 'text-center' : ''">
            <p class="font-semibold text-white" x-show="!collapsed" x-transition.opacity>{{ auth()->user()->name }}</p>
            <p class="font-semibold text-white" x-show="collapsed">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</p>
            <p class="mt-1" x-show="!collapsed" x-transition.opacity>{{ __("ui.roles.".auth()->user()->system_role) }}</p>
            <p class="mt-1 text-xs uppercase tracking-[0.25em] text-slate-400" x-show="!collapsed" x-transition.opacity>{{ auth()->user()->timezone }}</p>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full rounded-2xl border border-white/10 px-4 py-3 text-sm font-medium text-slate-200 transition hover:bg-white/5" :class="collapsed ? 'px-2' : ''" title="{{ __('ui.common.logout') }}">
                <span x-show="!collapsed" x-transition.opacity>{{ __('ui.common.logout') }}</span>
                <span x-show="collapsed">↪</span>
            </button>
        </form>
    </div>
</aside>
