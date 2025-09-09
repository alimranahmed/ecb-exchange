<?php

namespace EcbExchange;

use EcbExchange\EcbApiRepository;

/**
 * Service for handling exchange rate operations
 */
class ExchangeRateService
{
    public function __construct(
        private EcbApiRepository $repository
    ) {}

    public function getExchangeRate(
        string $fromCurrency,
        string $toCurrency,
        string $date,
        ?string $updatedAfter = null
    ): float {
        // If same currency, return 1.0
        if ($fromCurrency === $toCurrency) {
            return 1.0;
        }

        // If converting from EUR to another currency
        if ($fromCurrency === 'EUR') {
            return $this->repository->getRateToEur($toCurrency, $date, $updatedAfter);
        }

        // If converting to EUR from another currency
        if ($toCurrency === 'EUR') {
            return 1.0 / $this->repository->getRateToEur($fromCurrency, $date, $updatedAfter);
        }

        // Convert between two non-EUR currencies via EUR
        $fromToEur = $this->repository->getRateToEur($fromCurrency, $date, $updatedAfter);
        $toToEur = $this->repository->getRateToEur($toCurrency, $date, $updatedAfter);

        return $toToEur / $fromToEur;
    }

    public function getSupportedCurrencies(): array
    {
        return $this->repository->getSupportedCurrencies();
    }

    public function getTimeSeries(
        string $startDate,
        string $endDate,
        array $currencies = []
    ): array {
        return $this->repository->getTimeSeries($startDate, $endDate, $currencies);
    }
}
