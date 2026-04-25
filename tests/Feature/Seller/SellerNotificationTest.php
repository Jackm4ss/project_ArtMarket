<?php

namespace Tests\Feature\Seller;

use App\Models\Seller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SellerNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_seller_can_view_and_mark_own_notification_as_read(): void
    {
        [$sellerUser] = $this->sellerUser();
        $notification = $this->notificationFor($sellerUser, [
            'title' => 'Order Baru',
            'body' => 'Ada order baru yang perlu diproses.',
            'url' => route('seller.orders.index'),
        ]);

        $this->actingAs($sellerUser)
            ->get(route('seller.notifications.index'))
            ->assertOk()
            ->assertSee('Order Baru')
            ->assertSee('Ada order baru yang perlu diproses.');

        $this->actingAs($sellerUser)
            ->patch(route('seller.notifications.read', $notification))
            ->assertRedirect();

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_seller_cannot_mark_another_users_notification_as_read(): void
    {
        [$sellerUser] = $this->sellerUser();
        [$otherSellerUser] = $this->sellerUser();
        $notification = $this->notificationFor($otherSellerUser, ['title' => 'Rahasia Seller Lain']);

        $this->actingAs($sellerUser)
            ->patch(route('seller.notifications.read', $notification))
            ->assertNotFound();

        $this->assertNull($notification->fresh()->read_at);
    }

    public function test_mark_all_as_read_only_marks_current_seller_notifications(): void
    {
        [$sellerUser] = $this->sellerUser();
        [$otherSellerUser] = $this->sellerUser();
        $ownNotification = $this->notificationFor($sellerUser, ['title' => 'Notifikasi Sendiri']);
        $otherNotification = $this->notificationFor($otherSellerUser, ['title' => 'Notifikasi Seller Lain']);

        $this->actingAs($sellerUser)
            ->patch(route('seller.notifications.read-all'))
            ->assertRedirect();

        $this->assertNotNull($ownNotification->fresh()->read_at);
        $this->assertNull($otherNotification->fresh()->read_at);
    }

    /**
     * @return array{0: User, 1: Seller}
     */
    private function sellerUser(): array
    {
        Role::findOrCreate('seller');

        $user = User::factory()->create();
        $user->assignRole('seller');

        return [$user, Seller::factory()->create(['user_id' => $user->id])];
    }

    /**
     * @param array<string, mixed> $data
     */
    private function notificationFor(User $user, array $data): DatabaseNotification
    {
        return DatabaseNotification::query()->create([
            'id' => (string) Str::uuid(),
            'type' => 'SellerOperationalNotification',
            'notifiable_type' => $user->getMorphClass(),
            'notifiable_id' => $user->id,
            'data' => $data,
            'read_at' => null,
        ]);
    }
}
