<?php

namespace App\Http\Controllers;

use App\Http\Requests\HolidayRequest;
use App\Models\Holiday;
use App\Repositories\HolidayRepository;
use App\Services\HolidayService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class HolidayController extends Controller
{
    public function __construct(
        private readonly HolidayRepository $holidays,
        private readonly HolidayService $holidayService,
    ) {
    }

    public function index(): View
    {
        return view('holidays.index', [
            'holidays' => $this->holidays->paginate(),
        ]);
    }

    public function create(): View
    {
        return view('holidays.form', [
            'holiday' => new Holiday(),
        ]);
    }

    public function store(HolidayRequest $request): RedirectResponse
    {
        $this->holidayService->create($request->validated());

        return redirect()->route('holidays.index')->with('status', 'Holiday saved.');
    }

    public function edit(Holiday $holiday): View
    {
        return view('holidays.form', compact('holiday'));
    }

    public function update(HolidayRequest $request, Holiday $holiday): RedirectResponse
    {
        $this->holidayService->update($holiday, $request->validated());

        return redirect()->route('holidays.index')->with('status', 'Holiday updated.');
    }
}
