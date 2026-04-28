<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserManagementRequest;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Services\UserManagementService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class UserManagementController extends Controller
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly UserManagementService $userManagementService,
    ) {
    }

    public function index(): View|RedirectResponse
    {
        if ($redirect = $this->ensureOwner()) {
            return $redirect;
        }

        return view('pics.index', [
            'users' => $this->users->paginate(),
        ]);
    }

    public function create(): View|RedirectResponse
    {
        if ($redirect = $this->ensureOwner()) {
            return $redirect;
        }

        return view('pics.form', [
            'pic' => new User(),
        ]);
    }

    public function store(UserManagementRequest $request): RedirectResponse
    {
        $this->userManagementService->create($request->validated());

        return redirect()->route('pics.index')->with('status', 'PIC created.');
    }

    public function edit(User $pic): View|RedirectResponse
    {
        if ($redirect = $this->ensureOwner()) {
            return $redirect;
        }

        return view('pics.form', [
            'pic' => $pic,
        ]);
    }

    public function update(UserManagementRequest $request, User $pic): RedirectResponse
    {
        $this->userManagementService->update($pic, $request->validated());

        return redirect()->route('pics.index')->with('status', 'PIC updated.');
    }

    private function ensureOwner(): ?RedirectResponse
    {
        if (auth()->user()?->isOwner()) {
            return null;
        }

        return redirect()
            ->route('dashboard')
            ->with('status', 'Only the owner can manage PIC records.');
    }
}
