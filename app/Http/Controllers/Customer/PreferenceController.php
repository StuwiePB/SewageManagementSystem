<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PreferenceController extends Controller
{
    public function update(Request $request)
    {
        $request->validate([
            'appearance' => ['nullable', 'string', 'in:dark,light'],
            'language' => ['nullable', 'string', 'in:ms,en'],
            'anonymous' => ['nullable', 'string', 'in:nonanonymous,anonymous'],
        ]);

        $user = $request->user();
        $updates = [];

        if ($request->has('appearance')) {
            $updates['preference_appearance'] = $request->appearance;
        }
        if ($request->has('language')) {
            $updates['preference_language'] = $request->language;
        }
        if ($request->has('anonymous')) {
            $updates['preference_anonymous'] = $request->anonymous;
        }

        if (! empty($updates)) {
            $user->update($updates);
            $user->refresh();
        }

        return response()->json([
            'ok' => true,
            'appearance' => $user->preference_appearance ?? 'light',
            'language' => $user->preference_language ?? 'ms',
            'anonymous' => $user->preference_anonymous ?? 'nonanonymous',
        ]);
    }
}
