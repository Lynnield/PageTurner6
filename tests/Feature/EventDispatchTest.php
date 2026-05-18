<?php

namespace Tests\Feature;

use App\Events\OrderPlaced;
use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class EventDispatchTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_placed_event_is_dispatched()
    {
        Event::fake();

        $user = User::factory()->create();
        $book = Book::factory()->create(['stock_quantity' => 10]);

        $this->actingAs($user)->post('/checkout', [
            'shipping_name' => 'John Doe',
            'shipping_province' => 'Province',
            'shipping_city' => 'City',
            'shipping_barangay' => 'Barangay',
            'shipping_postal_code' => '1234',
            'shipping_street' => 'Street',
            'shipping_building_number' => '1',
        ], [
            'session' => ['cart' => [$book->id => 1]]
        ]);

        Event::assertDispatched(OrderPlaced::class);
    }
}
