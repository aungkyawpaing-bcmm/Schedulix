<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm uppercase tracking-[0.35em] text-sky-200/70">{{ __('ui.exports.title') }}</p>
            <h2 class="mt-2 text-3xl font-semibold text-white">{{ __('ui.exports.subtitle') }}</h2>
        </div>
    </x-slot>

    <div class="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
        <section class="rounded-3xl border border-white/10 bg-white/5 p-6">
            <h3 class="text-lg font-semibold text-white">{{ __('ui.exports.create_export') }}</h3>
            <p class="mt-1 text-sm text-slate-400">{{ __('ui.exports.create_note') }}</p>

            @foreach ($projects as $project)
                <form method="POST" action="{{ route('exports.store', $project) }}" class="mt-6 rounded-2xl border border-white/10 bg-slate-900/40 p-4">
                    @csrf
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="font-semibold text-white">{{ $project->name }}</p>
                            <p class="text-sm text-slate-400">{{ $project->code }}</p>
                        </div>
                        <button class="rounded-2xl bg-sky-400 px-4 py-3 text-sm font-semibold text-slate-950">{{ __('ui.exports.export') }}</button>
                    </div>
                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm text-slate-300">{{ __('ui.exports.file_name') }}</label>
                            <input name="file_name" value="{{ $project->code }}-wbs-export" class="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-white" />
                        </div>
                        <div>
                            <label class="mb-2 block text-sm text-slate-300">{{ __('ui.exports.locale') }}</label>
                            <select name="export_locale" class="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-white">
                                @foreach (config('wbs.supported_locales') as $key => $label)
                                    <option value="{{ $key }}">{{ __("ui.locales.$key") }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <input type="hidden" name="export_type" value="xlsx" />
                    <div class="mt-4 flex gap-6 text-sm text-slate-300">
                        <input type="hidden" name="include_formula" value="0" />
                        <input type="hidden" name="include_critical_path" value="0" />
                        <label class="flex items-center gap-2"><input type="checkbox" name="include_formula" value="1" checked> {{ __('ui.exports.include_formula') }}</label>
                        <label class="flex items-center gap-2"><input type="checkbox" name="include_critical_path" value="1"> {{ __('ui.exports.include_critical_path') }}</label>
                    </div>
                </form>
            @endforeach
        </section>

        <section class="rounded-3xl border border-white/10 bg-white/5 p-6">
            <h3 class="text-lg font-semibold text-white">{{ __('ui.exports.history') }}</h3>
            <div class="mt-4 overflow-hidden rounded-2xl border border-white/10">
                <table class="min-w-full divide-y divide-white/10 text-sm">
                    <thead class="bg-white/5 text-left text-slate-300">
                        <tr>
                            <th class="px-4 py-3">{{ __('ui.exports.file') }}</th>
                            <th class="px-4 py-3">{{ __('ui.common.project') }}</th>
                            <th class="px-4 py-3">{{ __('ui.exports.requested_by') }}</th>
                            <th class="px-4 py-3">{{ __('ui.common.status') }}</th>
                            <th class="px-4 py-3">{{ __('ui.exports.generated') }}</th>
                            <th class="px-4 py-3 text-right">{{ __('ui.common.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10">
                        @forelse ($exports as $export)
                            <tr class="text-slate-200">
                                <td class="px-4 py-3 font-semibold text-white">{{ $export->file_name }}</td>
                                <td class="px-4 py-3">{{ $export->project?->name }}</td>
                                <td class="px-4 py-3">{{ $export->user?->name }}</td>
                                <td class="px-4 py-3">{{ __("ui.statuses.$export->status") }}</td>
                                <td class="px-4 py-3">{{ $export->exported_at?->format('Y-m-d H:i') ?: '-' }}</td>
                                <td class="px-4 py-3 text-right">
                                    @if ($export->status === 'completed')
                                        <a href="{{ route('exports.download', $export) }}" class="text-sky-300">{{ __('ui.exports.download') }}</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-8 text-center text-slate-400">{{ __('ui.exports.no_exports') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $exports->links() }}</div>
        </section>
    </div>
</x-app-layout>
