<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SettingController extends Controller
{
    /**
     * All store settings with their effective values (DB value, falling
     * back to the env-configured default when never saved from the panel).
     */
    public function index()
    {
        return response()->json([
            'settings' => [
                'cashback_percent' => (float) Setting::getValue(
                    'cashback_percent',
                    config('app.cashback_percent', 0)
                ),
            ],
        ], Response::HTTP_OK);
    }

    public function update(Request $request)
    {
        $request->validate([
            'cashback_percent' => 'required|numeric|min:0|max:100',
        ]);

        Setting::setValue('cashback_percent', (float) $request->cashback_percent);

        activity('settings')->causedBy(auth()->user())
            ->log('cashback_percent set to ' . (float) $request->cashback_percent);

        return response()->json([
            'success' => true,
            'message' => 'Settings updated successfully',
        ], Response::HTTP_OK);
    }
}
