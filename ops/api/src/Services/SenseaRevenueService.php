<?php

declare(strict_types=1);

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class sensaeRevenueService
{
    private static ?Client $client = null;

    private static function getClient(): Client
    {
        if (self::$client === null) {
            $baseUrl = $_ENV['sensae_API_URL'] ?? getenv('sensae_API_URL') ?: 'http://localhost:8080';
            $apiKey = $_ENV['sensae_API_KEY'] ?? getenv('sensae_API_KEY') ?: '';

            self::$client = new Client([
                'base_uri' => rtrim($baseUrl, '/') . '/',
                'timeout' => 10,
                'headers' => [
                    'X-API-Key' => $apiKey,
                    'Accept' => 'application/json'
                ]
            ]);
        }

        return self::$client;
    }

    /**
     * Get revenue data for a specific month
     */
    public static function getMonthlyRevenue(int $year, int $month): array
    {
        try {
            $response = self::getClient()->get('ops/revenue', [
                'query' => ['year' => $year, 'month' => $month]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            return $data['data'] ?? [
                'total' => 0,
                'count' => 0,
                'paid_count' => 0,
                'paid_total' => 0
            ];
        } catch (GuzzleException $e) {
            error_log('sensaeRevenueService error: ' . $e->getMessage());
            return [
                'total' => 0,
                'count' => 0,
                'paid_count' => 0,
                'paid_total' => 0,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get revenue data for entire year
     */
    public static function getYearlyRevenue(int $year): array
    {
        try {
            $response = self::getClient()->get("ops/revenue/year/{$year}");

            $data = json_decode($response->getBody()->getContents(), true);
            return $data['data'] ?? [];
        } catch (GuzzleException $e) {
            error_log('sensaeRevenueService error: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Get session count for a month
     */
    public static function getSessionCount(int $year, int $month): array
    {
        try {
            $response = self::getClient()->get('ops/sessions/count', [
                'query' => ['year' => $year, 'month' => $month]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            return $data['data'] ?? ['count' => 0];
        } catch (GuzzleException $e) {
            error_log('sensaeRevenueService error: ' . $e->getMessage());
            return ['count' => 0, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get daily revenue for a month
     */
    public static function getDailyRevenue(int $year, int $month): array
    {
        try {
            $response = self::getClient()->get('ops/revenue/daily', [
                'query' => ['year' => $year, 'month' => $month]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            return $data['data'] ?? [];
        } catch (GuzzleException $e) {
            error_log('sensaeRevenueService getDailyRevenue error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get prepaid packs revenue for a specific month
     */
    public static function getPrepaidMonthlyRevenue(int $year, int $month): array
    {
        try {
            $response = self::getClient()->get('ops/prepaid-revenue', [
                'query' => ['year' => $year, 'month' => $month]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            return $data['data'] ?? [
                'total' => 0,
                'count' => 0,
                'pack_2' => ['total' => 0, 'count' => 0],
                'pack_4' => ['total' => 0, 'count' => 0]
            ];
        } catch (GuzzleException $e) {
            error_log('sensaeRevenueService prepaid error: ' . $e->getMessage());
            return [
                'total' => 0,
                'count' => 0,
                'pack_2' => ['total' => 0, 'count' => 0],
                'pack_4' => ['total' => 0, 'count' => 0],
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get prepaid packs revenue for entire year
     */
    public static function getPrepaidYearlyRevenue(int $year): array
    {
        try {
            $response = self::getClient()->get("ops/prepaid-revenue/year/{$year}");

            $data = json_decode($response->getBody()->getContents(), true);
            return $data['data'] ?? [];
        } catch (GuzzleException $e) {
            error_log('sensaeRevenueService prepaid yearly error: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Get daily prepaid revenue for a month
     */
    public static function getPrepaidDailyRevenue(int $year, int $month): array
    {
        try {
            $response = self::getClient()->get('ops/prepaid-revenue/daily', [
                'query' => ['year' => $year, 'month' => $month]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            return $data['data'] ?? [];
        } catch (GuzzleException $e) {
            error_log('sensaeRevenueService getPrepaidDailyRevenue error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Check if sensae API is available
     */
    public static function ping(): bool
    {
        try {
            $response = self::getClient()->get('health');
            return $response->getStatusCode() === 200;
        } catch (GuzzleException $e) {
            return false;
        }
    }
}
