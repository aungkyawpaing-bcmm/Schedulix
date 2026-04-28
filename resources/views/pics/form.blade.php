<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm uppercase tracking-[0.35em] text-sky-200/70">{{ __('ui.pics.title') }}</p>
            <h2 class="mt-2 text-3xl font-semibold text-white">{{ $pic->exists ? __('ui.pics.edit_title') : __('ui.pics.create_title') }}</h2>
        </div>
    </x-slot>

    <form method="POST" action="{{ $pic->exists ? route('pics.update', $pic) : route('pics.store') }}" class="space-y-6 rounded-3xl border border-white/10 bg-white/5 p-6">
        @csrf
        @if ($pic->exists)
            @method('PUT')
        @endif

        @if ($errors->any())
            <div class="rounded-2xl border border-rose-300/30 bg-rose-300/10 px-4 py-3 text-sm text-rose-200">
                {{ __('ui.common.review_errors') }}
            </div>
        @endif

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <input name="name" value="{{ old('name', $pic->name) }}" placeholder="{{ __('ui.common.name') }}" class="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-white" />
                <x-field-error for="name" class="mt-2" />
            </div>
            <div>
                <input type="email" name="email" value="{{ old('email', $pic->email) }}" placeholder="{{ __('ui.common.email') }}" class="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-white" />
                <x-field-error for="email" class="mt-2" />
            </div>
            <div>
                <input name="position" value="{{ old('position', $pic->position) }}" placeholder="{{ __('ui.common.position') }}" class="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-white" />
                <x-field-error for="position" class="mt-2" />
            </div>
            <div>
                <input name="timezone" value="{{ old('timezone', $pic->timezone ?: 'Asia/Yangon') }}" placeholder="{{ __('ui.common.timezone') }}" class="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-white" />
                <x-field-error for="timezone" class="mt-2" />
            </div>
            <div>
                <select name="system_role" class="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-white">
                    @foreach (config('wbs.system_roles') as $role)
                        <option value="{{ $role }}" @selected(old('system_role', $pic->system_role ?: 'member') === $role)>{{ __("ui.roles.$role") }}</option>
                    @endforeach
                </select>
                <x-field-error for="system_role" class="mt-2" />
            </div>
            <div>
                <select name="locale" class="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-white">
                    @foreach (config('wbs.supported_locales') as $key => $label)
                        <option value="{{ $key }}" @selected(old('locale', $pic->locale ?: 'en') === $key)>{{ __("ui.locales.$key") }}</option>
                    @endforeach
                </select>
                <x-field-error for="locale" class="mt-2" />
            </div>
            <div>
                <input type="date" name="available_from" value="{{ old('available_from', optional($pic->available_from)->format('Y-m-d')) }}" class="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-white" />
                <x-field-error for="available_from" class="mt-2" />
            </div>
            <div>
                <input type="password" name="password" placeholder="{{ $pic->exists ? __('ui.pics.password_hint') : __('ui.pics.password') }}" class="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-white" />
                <x-field-error for="password" class="mt-2" />
            </div>
            <div>
                <input type="password" name="password_confirmation" placeholder="{{ __('ui.pics.password_confirmation') }}" class="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-white" />
                <x-field-error for="password_confirmation" class="mt-2" />
            </div>
            <div>
                <select name="is_active" class="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-white">
                    <option value="1" @selected(old('is_active', $pic->exists ? (int) $pic->is_active : 1) == 1)>{{ __('ui.pics.active') }}</option>
                    <option value="0" @selected(old('is_active', (int) $pic->is_active) == 0)>{{ __('ui.pics.inactive') }}</option>
                </select>
                <x-field-error for="is_active" class="mt-2" />
            </div>
            <div>
                <select name="is_available" class="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-white">
                    <option value="1" @selected(old('is_available', $pic->exists ? (int) $pic->is_available : 1) == 1)>{{ __('ui.pics.available') }}</option>
                    <option value="0" @selected(old('is_available', (int) $pic->is_available) == 0)>{{ __('ui.pics.unavailable') }}</option>
                </select>
                <x-field-error for="is_available" class="mt-2" />
            </div>
        </div>

        <button class="rounded-2xl bg-sky-400 px-5 py-3 text-sm font-semibold text-slate-950">{{ $pic->exists ? __('ui.pics.save') : __('ui.pics.create') }}</button>
    </form>
</x-app-layout>
