<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CreditCardPageController extends Controller
{
    public function show(Request $request, Account $account)
    {
        abort_unless((int) $account->user_id === (int) $request->user()->id, 404);
        abort_unless($account->type === 'credit_card', 404);

        return Inertia::render('CreditCards/Show', [
            'accountId' => (string) $account->id,
        ]);
    }
}

