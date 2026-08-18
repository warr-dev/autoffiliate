<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\SocialAccount;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Settings/Index', [
            'settings' => Setting::all()->pluck('value', 'key'),
            'socialAccounts' => SocialAccount::all(),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->except(['_token']);

        foreach ($data as $key => $value) {
            Setting::set($key, (string) $value);
        }

        return back()->with('success', 'Settings saved successfully.');
    }
}
