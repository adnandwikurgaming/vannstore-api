<?php

namespace Tests\Unit;

use App\Enums\UserRole;
use App\Models\Product;
use App\Models\User;
use App\Policies\ProductPolicy;
use PHPUnit\Framework\TestCase;

class ProductPolicyTest extends TestCase
{
    private ProductPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new ProductPolicy();
    }

    public function test_only_seller_can_create_product(): void
    {
        $seller = new User();
        $seller->role = UserRole::Penjual;

        $buyer = new User();
        $buyer->role = UserRole::Pembeli;

        $this->assertTrue($this->policy->create($seller)->allowed());
        $this->assertFalse($this->policy->create($buyer)->allowed());

        $this->assertEquals(
            'Anda tidak memiliki akses',
            $this->policy->create($buyer)->message()
        );
    }

    public function test_only_product_owner_can_update_product(): void
    {
        $product = new Product();
        $product->seller_id = 1;

        $owner = new User();
        $owner->id = 1;
        $owner->role = UserRole::Penjual;

        $otherSeller = new User();
        $otherSeller->id = 2;
        $otherSeller->role = UserRole::Penjual;

        $this->assertTrue($this->policy->update($owner, $product)->allowed());
        $this->assertFalse($this->policy->update($otherSeller, $product)->allowed());

        $this->assertEquals(
            'Anda tidak memiliki akses',
            $this->policy->update($otherSeller, $product)->message()
        );
    }

    public function test_only_product_owner_can_update_stock(): void
    {
        $product = new Product();
        $product->seller_id = 1;

        $owner = new User();
        $owner->id = 1;
        $owner->role = UserRole::Penjual;

        $otherSeller = new User();
        $otherSeller->id = 2;
        $otherSeller->role = UserRole::Penjual;

        $this->assertTrue($this->policy->updateStock($owner, $product)->allowed());
        $this->assertFalse($this->policy->updateStock($otherSeller, $product)->allowed());

        $this->assertEquals(
            'Anda tidak memiliki akses',
            $this->policy->updateStock($otherSeller, $product)->message()
        );
    }

    public function test_only_admin_can_deactivate_product(): void
    {
        $admin = new User();
        $admin->role = UserRole::Admin;

        $seller = new User();
        $seller->role = UserRole::Penjual;

        $buyer = new User();
        $buyer->role = UserRole::Pembeli;

        $this->assertTrue($this->policy->deactivate($admin)->allowed());
        $this->assertFalse($this->policy->deactivate($seller)->allowed());
        $this->assertFalse($this->policy->deactivate($buyer)->allowed());

        $this->assertEquals(
            'Anda tidak memiliki akses',
            $this->policy->deactivate($seller)->message()
        );
    }
}
