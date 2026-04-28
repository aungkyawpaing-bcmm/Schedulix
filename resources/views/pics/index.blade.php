<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm uppercase tracking-[0.35em] text-sky-200/70">{{ __('ui.pics.title') }}</p>
                <h2 class="mt-2 text-3xl font-semibold text-white">{{ __('ui.pics.subtitle') }}</h2>
            </div>
            <a href="{{ route('pics.create') }}" class="rounded-2xl bg-sky-400 px-4 py-3 text-sm font-semibold text-slate-950">{{ __('ui.pics.create') }}</a>
        </div>
    </x-slot>

    <div class="overflow-hidden rounded-3xl border border-white/10 bg-white/5">
        <table class="min-w-full divide-y divide-white/10 text-sm">
            <thead class="bg-white/5 text-left text-slate-300">
                <tr>
                    <th class="px-4 py-3">{{ __('ui.common.name') }}</th>
                    <th class="px-4 py-3">{{ __('ui.common.role') }}</th>
                    <th class="px-4 py-3">{{ __('ui.common.availability') }}</th>
                    <th class="px-4 py-3">{{ __('ui.common.status') }}</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/10">
                @foreach ($users as $user)
                    <tr class="text-slate-200">
                        <td class="px-4 py-4">
                            <p class="font-semibold text-white">{{ $user->name }}</p>
                            <p class="text-slate-400">{{ $user->email }}</p>
                        </td>
                        <td class="px-4 py-4">{{ __("ui.roles.$user->system_role") }}</td>
                        <td class="px-4 py-4">{{ $user->is_available ? __('ui.pics.available') : __('ui.pics.unavailable') }}</td>
                        <td class="px-4 py-4">{{ $user->is_active ? __('ui.pics.active') : __('ui.pics.inactive') }}</td>
                        <td class="px-4 py-4 text-right"><a href="{{ route('pics.edit', $user) }}" class="text-sky-300">{{ __('ui.common.edit') }}</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $users->links() }}</div>
</x-app-layout>
