<?php

namespace App\Http\Controllers;

use App\Http\Requests\SettingsRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index(Request $request): View
    {
        return view('settings.index', [
            'locales' => config('wbs.supported_locales'),
            'roles' => config('wbs.system_roles'),
            'preferences' => [
                'default_locale' => $request->user()->locale,
                'default_timezone' => $request->user()->timezone,
                'date_format' => session('date_format', 'Y-m-d'),
                'rows_per_page' => session('rows_per_page', 10),
            ],
        ]);
    }

    public function update(SettingsRequest $request): RedirectResponse
    {
        $request->user()->update([
            'locale' => $request->string('default_locale')->toString(),
            'timezone' => $request->string('default_timezone')->toString(),
        ]);

        session([
            'date_format' => $request->string('date_format')->toString(),
            'rows_per_page' => (int) $request->input('rows_per_page'),
        ]);

        return redirect()->route('settings.index')->with('status', __('ui.common.settings_saved'));
    }
}
