<?php

namespace Tests\Feature;

use App\Models\Trade;
use App\Models\TradeHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TradeOrderTest extends TestCase
{
    use RefreshDatabase;

    private function authHeaders(): array
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $token = $user->createToken('test-token')->plainTextToken;

        return ['Authorization' => "Bearer {$token}"];
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'symbol' => 'BTCUSDT',
            'type' => 'BULLISH OB',
            'current_price' => '65432.12345678',
            'zone' => [
                'bottom' => '64000.00000000',
                'top' => '66000.00000000',
            ],
        ], $overrides);
    }

    public function test_unauthenticated_request_returns_401(): void
    {
        $response = $this->postJson('/api/trade-order', $this->validPayload());

        $response->assertStatus(401);
    }

    public function test_opens_new_trade_successfully(): void
    {
        $response = $this->postJson(
            '/api/trade-order',
            $this->validPayload(),
            $this->authHeaders(),
        );

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => ['trade_id', 'txid', 'closed_trades'],
            ]);

        $this->assertDatabaseHas('trades', [
            'symbol' => 'BTCUSDT',
            'type' => 'BULLISH OB',
            'status' => 'open',
        ]);

        $this->assertDatabaseHas('trade_histories', [
            'symbol' => 'BTCUSDT',
            'type' => 'BULLISH OB',
        ]);
    }

    public function test_duplicate_signal_does_nothing(): void
    {
        // Seed an existing history entry with same type
        TradeHistory::create([
            'symbol' => 'BTCUSDT',
            'type' => 'BULLISH OB',
            'current_price' => '65000.00000000',
            'zone_bottom' => '64000.00000000',
            'zone_top' => '66000.00000000',
            'order_id' => 'existing-order',
        ]);

        $response = $this->postJson(
            '/api/trade-order',
            $this->validPayload(),
            $this->authHeaders(),
        );

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Trade type is same as last history, nothing executed',
            ]);

        // No new trade should be created
        $this->assertDatabaseCount('trades', 0);
    }

    public function test_opposite_signal_closes_open_trades_and_opens_new(): void
    {
        // Seed an existing open trade and history
        Trade::create([
            'symbol' => 'BTCUSDT',
            'type' => 'BULLISH OB',
            'price' => '64000.00000000',
            'status' => 'open',
            'txid' => 'old-txid',
        ]);

        TradeHistory::create([
            'symbol' => 'BTCUSDT',
            'type' => 'BULLISH OB',
            'current_price' => '64000.00000000',
            'zone_bottom' => '63000.00000000',
            'zone_top' => '65000.00000000',
            'order_id' => 'old-txid',
        ]);

        // Send opposite signal
        $response = $this->postJson(
            '/api/trade-order',
            $this->validPayload(['type' => 'BEARISH OB']),
            $this->authHeaders(),
        );

        $response->assertStatus(201)
            ->assertJsonPath('data.closed_trades', 1);

        // Old trade should be closed
        $this->assertDatabaseHas('trades', [
            'txid' => 'old-txid',
            'status' => 'closed',
        ]);

        // New trade should be open
        $this->assertDatabaseHas('trades', [
            'type' => 'BEARISH OB',
            'status' => 'open',
        ]);
    }

    public function test_validation_rejects_invalid_type(): void
    {
        $response = $this->postJson(
            '/api/trade-order',
            $this->validPayload(['type' => 'INVALID']),
            $this->authHeaders(),
        );

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['type']);
    }

    public function test_validation_rejects_missing_fields(): void
    {
        $response = $this->postJson(
            '/api/trade-order',
            [],
            $this->authHeaders(),
        );

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['symbol', 'type', 'current_price', 'zone.bottom', 'zone.top']);
    }
}
