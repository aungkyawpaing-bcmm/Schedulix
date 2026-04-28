@props(['for'])

@php
    $keys = \Illuminate\Support\Arr::wrap($for);
    $messages = collect($keys)
        ->flatMap(fn ($key) => $errors->get($key))
        ->filter()
        ->unique()
        ->values()
        ->all();
@endphp

<x-input-error :messages="$messages" {{ $attributes }} />
