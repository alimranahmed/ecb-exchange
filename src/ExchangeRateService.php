<?php

namespace EcbExchange;

use EcbExchange\EcbApiRepository;

/**
 * Service for handling exchange rate operations
 */
class ExchangeRateService
{
    private $repository;

    public function __construct(EcbApiRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getExchangeRate(
        $fromCurrency,
        $toCurrency,
        $date,
        $updatedAfter = null
    ) {

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

    public function getSupportedCurrencies()
    {
        return $this->repository->getSupportedCurrencies();
    }

    public function getTimeSeries(
        $startDate,
        $endDate,
        $currencies = []
    ) {
        return $this->repository->getTimeSeries($startDate, $endDate, $currencies);
    }
}
