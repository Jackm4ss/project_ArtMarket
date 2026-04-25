<?php

namespace App\Http\Middleware;

use App\Services\Cart\CartManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => fn () => $this->sharedUser($request),
            ],
            'cart' => fn () => [
                'total_items' => app(CartManager::class)->count(),
            ],
            'notifications' => fn () => [
                'unread_count' => $this->unreadNotificationCount($request),
            ],
            'messages' => fn () => [
                'unread_count' => $this->unreadMessageCount($request),
            ],
            'flash' => fn () => [
                'status' => $request->session()->get('status'),
            ],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function sharedUser(Request $request): ?array
    {
        $user = $request->user();

        if (! $user) {
            return null;
        }

        $user->loadMissing('seller');
        $roles = method_exists($user, 'getRoleNames') ? $user->getRoleNames()->values()->all() : [];

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'avatar_url' => $user->avatar_url ?? null,
            'roles' => $roles,
            'can_manage_store' => in_array('seller', $roles, true) || in_array('admin', $roles, true),
            'seller' => $user->seller ? [
                'id' => $user->seller->id,
                'store_name' => $user->seller->store_name,
                'slug' => $user->seller->slug,
            ] : null,
        ];
    }

    private function unreadNotificationCount(Request $request): int
    {
        $user = $request->user();

        if (! $user) {
            return 0;
        }

        return (int) $user->unreadNotifications()->count();
    }

    private function unreadMessageCount(Request $request): int
    {
        $user = $request->user();

        if (! $user) {
            return 0;
        }

        return (int) DB::table('messages')
            ->join('conversation_participants', function ($join) use ($user): void {
                $join->on('conversation_participants.conversation_id', '=', 'messages.conversation_id')
                    ->where('conversation_participants.user_id', '=', $user->id)
                    ->whereNull('conversation_participants.deleted_at');
            })
            ->where('messages.sender_id', '!=', $user->id)
            ->whereNull('messages.hidden_at')
            ->whereNull('messages.deleted_at')
            ->where(function ($query): void {
                $query->whereNull('conversation_participants.last_read_at')
                    ->orWhereColumn('messages.created_at', '>', 'conversation_participants.last_read_at');
            })
            ->count();
    }
}
