<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class NotificationController extends Controller
{
    public function __construct(private readonly NotificationService $notifications)
    {
    }

    public function index(): View
    {
        return view('notifications.index', [
            'notifications' => Notification::query()->with('project', 'assignment.wbsItem', 'user')->latest()->paginate(10),
        ]);
    }

    public function generate(string $type): RedirectResponse
    {
        $created = match ($type) {
            'daily' => $this->notifications->createDailyTaskNotifications(),
            'overdue' => $this->notifications->createOverdueNotifications(),
            'risk' => $this->notifications->createRiskNotifications(),
            'summary' => $this->notifications->createPmSummaryNotifications(),
            default => null,
        };

        abort_if($created === null, 404);

        return redirect()->route('notifications.index')->with('status', __('ui.notifications.generated_status', ['count' => $created]));
    }
}
