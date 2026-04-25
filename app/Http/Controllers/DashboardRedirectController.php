<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardRedirectController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user?->hasRole('admin')) {
            return redirect('/admin');
        }

        if ($user?->hasRole('seller')) {
            return redirect('/seller');
        }

        return redirect('/user');
    }
}
