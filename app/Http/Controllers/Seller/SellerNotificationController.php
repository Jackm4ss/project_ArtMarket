<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class SellerNotificationController extends Controller
{
    public function index(Request $request): View
    {
        return view('seller.notifications', [
            'notifications' => $request->user()
                ->notifications()
                ->latest()
                ->paginate(15),
            'unreadCount' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    public function markAsRead(Request $request, DatabaseNotification $notification): RedirectResponse
    {
        abort_unless(
            $notification->notifiable_id === $request->user()->id
            && $notification->notifiable_type === $request->user()->getMorphClass(),
            404
        );

        $notification->markAsRead();

        return back()->with('status', 'Notifikasi ditandai sudah dibaca.');
    }

    public function markAllAsRead(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return back()->with('status', 'Semua notifikasi ditandai sudah dibaca.');
    }
}
