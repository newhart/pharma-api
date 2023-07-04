<?php

namespace Tests\Feature;

use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_new_order(): void
    {
        $response = $this->post('api/order/2', [
            'numberPlaquette' => 5,
            'numberGellule' => 10,
            'quantityBoite' => 5,
            'id' => 2
        ]);
        $this->assertDatabaseHas('orders', [
            'dateCommande' => now()
        ]);

        $response->assertStatus(200);
    }
}
