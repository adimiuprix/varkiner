<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class BitgetService
{
    protected string $baseUrl = 'https://api.bitget.com';
    protected string $apiKey;
    protected string $secretKey;
    protected string $passphrase;

    public function __construct()
    {
        $this->apiKey = "bg_523cc6034001f552e851cfb7986a0937";
        $this->secretKey = "e25da0e7123f6430a4402e43bd541b26674e3f951a4053707dee85305279acbe";
        $this->passphrase = "MakanBiawak";
    }

    protected function signature(string $method, string $path, string $query = '', array $body = []): array
    {
        $timestamp = (int) (microtime(true) * 1000);
        $preHash = $timestamp . strtoupper($method) . $path . ($query ? "?$query" : '') . ($body ? json_encode($body, JSON_UNESCAPED_SLASHES) : '');

        return [$timestamp, base64_encode(hash_hmac('sha256', $preHash, $this->secretKey, true))];
    }

    public function request(string $method, string $path, array $params = [])
    {
        $method = strtoupper($method);
        $query  = $method === 'GET' ? http_build_query($params) : '';
        $body   = $method === 'GET' ? [] : $params;

        [$timestamp, $sign] = $this->signature($method, $path, $query, $body);

        return Http::withHeaders([
            'ACCESS-KEY'        => $this->apiKey,
            'ACCESS-SIGN'       => $sign,
            'ACCESS-TIMESTAMP'  => $timestamp,
            'ACCESS-PASSPHRASE' => $this->passphrase,
            'locale'            => 'en-US',
            'Content-Type'      => 'application/json',
        ])->send($method, $this->baseUrl . $path . ($query ? "?$query" : ''), [
            'json' => $body
        ])->json();
    }

    // Spot API
    public function getBalance()
    {
        return $this->request('GET', '/api/v2/spot/account/assets');
    }

    public function getTicker(string $symbol)
    {
        return $this->request('GET', '/api/v2/spot/market/ticker', ['symbol' => $symbol]);
    }

    public function getOrderBook(string $symbol)
    {
        return $this->request('GET', '/api/v2/spot/market/orderbook', ['symbol' => $symbol]);
    }

    public function createOrder(array $params)
    {
        // Required: symbol, side, orderType, force
        return $this->request('POST', '/api/v2/spot/trade/place-order', $params);
    }

    public function cancelOrder(string $symbol, string $orderId)
    {
        return $this->request('POST', '/api/v2/spot/trade/cancel-order', [
            'symbol' => $symbol,
            'orderId' => $orderId,
        ]);
    }

    public function getOrder(string $symbol, string $orderId)
    {
        return $this->request('GET', '/api/v2/spot/trade/orderInfo', [
            'symbol' => $symbol,
            'orderId' => $orderId,
        ]);
    }

    public function getOrders(string $symbol, array $params = [])
    {
        return $this->request('GET', '/api/v2/spot/trade/history-orders', array_merge([
            'symbol' => $symbol,
        ], $params));
    }

    // Futures API
    public function createFuturesOrder(array $params)
    {
        // Required: symbol, productType, marginMode, side, orderType, size
        return $this->request('POST', '/api/v2/mix/order/place-order', $params);
    }

    public function cancelFuturesOrder(string $symbol, string $productType, string $orderId)
    {
        return $this->request('POST', '/api/v2/mix/order/cancel-order', [
            'symbol' => $symbol,
            'productType' => $productType,
            'orderId' => $orderId,
        ]);
    }

    public function getFuturesOrder(string $symbol, string $productType, string $orderId)
    {
        return $this->request('GET', '/api/v2/mix/order/detail', [
            'symbol' => $symbol,
            'productType' => $productType,
            'orderId' => $orderId,
        ]);
    }

    public function getFuturesOrders(string $symbol, string $productType, array $params = [])
    {
        return $this->request('GET', '/api/v2/mix/order/history', array_merge([
            'symbol' => $symbol,
            'productType' => $productType,
        ], $params));
    }

    public function flashCloseOrder(string $symbol, string $productType)
    {
        return $this->request('POST', '/api/v2/mix/order/close-positions', [
            'symbol' => $symbol,
            'productType' => $productType,
        ]);
    }
}