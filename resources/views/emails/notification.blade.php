<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <title>{{ $notification->title }}</title>
    </head>
    <body style="font-family: Arial, sans-serif; color: #163828; line-height: 1.6;">
        <h1 style="margin-bottom: 12px;">{{ $notification->title }}</h1>

        <p>{{ $notification->message }}</p>

        @if ($notification->project)
            <p><strong>{{ __('ui.common.project') }}:</strong> {{ $notification->project->name }}</p>
        @endif

        @if ($notification->assignment?->wbsItem)
            <p><strong>{{ __('ui.notifications.assignment') }}:</strong> {{ $notification->assignment->wbsItem->wbs_number }} - {{ $notification->assignment->wbsItem->item_name }}</p>
        @endif

        @if ($notification->action_url)
            <p>
                <a href="{{ $notification->action_url }}" style="display: inline-block; padding: 10px 16px; background: #85f2a1; color: #163828; text-decoration: none; border-radius: 8px;">
                    {{ __('ui.common.open') }}
                </a>
            </p>
        @endif

        <p style="margin-top: 20px; color: #4b6358;">{{ __('ui.app_name') }}</p>
    </body>
</html>
