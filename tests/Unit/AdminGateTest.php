<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class AdminGateTest extends TestCase
{
    public function test_admin_can_pass_admin_gate(): void
    {
        $user = new User(['role' => 'admin']);

        $this->assertTrue(Gate::forUser($user)->allows('admin'));
    }

    public function test_buyer_cannot_pass_admin_gate(): void
    {
        $user = new User(['role' => 'pembeli']);

        $this->assertFalse(Gate::forUser($user)->allows('admin'));
    }

    public function test_seller_cannot_pass_admin_gate(): void
    {
        $user = new User(['role' => 'penjual']);

        $this->assertFalse(Gate::forUser($user)->allows('admin'));
    }
}
