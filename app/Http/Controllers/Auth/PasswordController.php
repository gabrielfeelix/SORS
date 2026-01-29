<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Rules\StrongPassword;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PasswordController extends Controller
{
    private function isLocalWithoutDatabaseDriver(): bool
    {
        return app()->environment('local') && ! extension_loaded('pdo_mysql') && ! extension_loaded('pdo_sqlite');
    }

    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        if ($this->isLocalWithoutDatabaseDriver()) {
            return back();
        }

        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', new StrongPassword()],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back();
    }
}
