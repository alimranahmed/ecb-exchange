<?php

namespace EcbExchange;

use EcbExchange\EcbExchangeBuilder;
use EcbExchange\ExchangeRateService;
use EcbExchange\EcbApiRepository;


class Ecb
{
    private static ?ExchangeRateService $service = null;

    /**
     * Get a new exchange rate builder instance
     * 
     * @return EcbExchangeBuilder
     */
    public static function exchange(): EcbExchangeBuilder
    {
        if (self::$service === null) {
            self::$service = self::createService();
        }

        return new EcbExchangeBuilder(self::$service);
    }

    /**
     * Create the exchange rate service with dependencies
     * 
     * @return ExchangeRateService
     */
    private static function createService(): ExchangeRateService
    {
        $repository = new EcbApiRepository();
        return new ExchangeRateService($repository);
    }

    /**
     * Get supported currencies
     * 
     * @return array Array of supported currency codes
     */
    public static function getSupportedCurrencies(): array
    {
        if (self::$service === null) {
            self::$service = self::createService();
        }

        return self::$service->getSupportedCurrencies();
    }

    /**
     * Get time series data
     * 
     * @param string $startDate Start date in YYYY-MM-DD format
     * @param string $endDate End date in YYYY-MM-DD format
     * @param array $currencies Optional array of currencies to filter
     * @return array Time series data
     */
    public static function getTimeSeries(string $startDate, string $endDate, array $currencies = []): array
    {
        if (self::$service === null) {
            self::$service = self::createService();
        }

        return self::$service->getTimeSeries($startDate, $endDate, $currencies);
    }
}
