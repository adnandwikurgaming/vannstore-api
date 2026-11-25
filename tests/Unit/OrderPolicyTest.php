<?php

namespace Tests\Unit;

use App\Enums\UserRole;
use App\Models\Order;
use App\Models\User;
use App\Policies\OrderPolicy;
use PHPUnit\Framework\TestCase;

class OrderPolicyTest extends TestCase
{
    private OrderPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new OrderPolicy();
    }

    public function test_only_buyer_can_create_order(): void
    {
        $buyer = new User();
        $buyer->role = UserRole::Pembeli;

        $seller = new User();
        $seller->role = UserRole::Penjual;

        $this->assertTrue($this->policy->create($buyer)->allowed());
        $this->assertFalse($this->policy->create($seller)->allowed());
        $this->assertEquals(
            'Anda tidak memiliki akses',
            $this->policy->create($seller)->message()
        );
    }

    public function test_order_can_only_be_viewed_by_related_users_or_admin(): void
    {
        $order = new Order();
        $order->buyer_id = 1;
        $order->seller_id = 2;

        $buyer = new User();
        $buyer->id = 1;
        $buyer->role = UserRole::Pembeli;

        $seller = new User();
        $seller->id = 2;
        $seller->role = UserRole::Penjual;

        $admin = new User();
        $admin->id = 3;
        $admin->role = UserRole::Admin;

        $otherUser = new User();
        $otherUser->id = 4;
        $otherUser->role = UserRole::Pembeli;

        $this->assertTrue($this->policy->view($buyer, $order)->allowed());
        $this->assertTrue($this->policy->view($seller, $order)->allowed());
        $this->assertTrue($this->policy->view($admin, $order)->allowed());
        $this->assertFalse($this->policy->view($otherUser, $order)->allowed());
        $this->assertEquals(
            'Anda tidak memiliki akses',
            $this->policy->view($otherUser, $order)->message()
        );
    }

    public function test_only_order_seller_can_update_status(): void
    {
        $order = new Order();
        $order->seller_id = 2;

        $seller = new User();
        $seller->id = 2;
        $seller->role = UserRole::Penjual;

        $otherSeller = new User();
        $otherSeller->id = 3;
        $otherSeller->role = UserRole::Penjual;

        $buyer = new User();
        $buyer->id = 1;
        $buyer->role = UserRole::Pembeli;

        $this->assertTrue($this->policy->updateStatus($seller, $order)->allowed());
        $this->assertFalse($this->policy->updateStatus($otherSeller, $order)->allowed());
        $this->assertFalse($this->policy->updateStatus($buyer, $order)->allowed());
        $this->assertEquals(
            'Anda tidak memiliki akses',
            $this->policy->updateStatus($otherSeller, $order)->message()
        );
    }
}
