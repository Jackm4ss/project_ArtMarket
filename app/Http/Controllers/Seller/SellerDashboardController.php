<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class SellerDashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('seller.dashboard');
    }
}
