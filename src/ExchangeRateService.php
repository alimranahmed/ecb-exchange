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

        if ($fromCurrency === $toCurrency) {
            return 1.0;
        }

        if ($fromCurrency === 'EUR') {
            return $this->repository->getRateToEur($toCurrency, $date, $updatedAfter);
        }


        if ($toCurrency === 'EUR') {
            return 1.0 / $this->repository->getRateToEur($fromCurrency, $date, $updatedAfter);
        }

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
