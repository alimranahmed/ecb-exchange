<?php

namespace EcbExchange;

use EcbExchange\EcbExchangeRate;
use EcbExchange\EcbExchangeRateCollection;
use EcbExchange\ExchangeRateService;

/**
 * Fluent API builder for ECB exchange rates
 */
class EcbExchangeBuilder
{
    private $fromCurrency = null;
    private $toCurrency = null;
    private $fromDate = null;
    private $updatedAfter = null;
    private $toCurrencies = [];

    private $exchangeRateService;

    public function __construct(ExchangeRateService $exchangeRateService)
    {
        $this->exchangeRateService = $exchangeRateService;
    }

    public function fromCurrency($currency)
    {
        $this->fromCurrency = $currency;
        return $this;
    }

    public function toCurrency($currency)
    {
        $this->toCurrency = $currency;
        return $this;
    }

    public function toCurrencies($currencies)
    {
        $this->toCurrencies = $currencies;
        return $this;
    }

    public function date($date)
    {
        $this->fromDate = $date;
        return $this;
    }

    public function updatedAfter($timestamp)
    {
        $this->updatedAfter = $timestamp;
        return $this;
    }

    public function get()
    {
        if (!empty($this->toCurrencies)) {
            return $this->getMultipleRates();
        }

        return $this->getSingleRate();
    }

    private function getSingleRate()
    {
        $fromCurrency = $this->fromCurrency ?? 'EUR';
        $toCurrency = $this->toCurrency ?? 'EUR';
        $date = $this->fromDate ?? date('Y-m-d');

        $rate = $this->exchangeRateService->getExchangeRate(
            $fromCurrency,
            $toCurrency,
            $date,
            $this->updatedAfter
        );

        return new EcbExchangeRate(
            $fromCurrency,
            $toCurrency,
            $rate,
            $date,
            $this->updatedAfter
        );
    }

    private function getMultipleRates()
    {
        $fromCurrency = $this->fromCurrency ?? 'EUR';
        $date = $this->fromDate ?? date('Y-m-d');
        $currencies = $this->toCurrencies;

        $collection = new EcbExchangeRateCollection();

        foreach ($currencies as $toCurrency) {
            $rate = $this->exchangeRateService->getExchangeRate(
                $fromCurrency,
                $toCurrency,
                $date,
                $this->updatedAfter
            );

            $collection->add(new EcbExchangeRate(
                $fromCurrency,
                $toCurrency,
                $rate,
                $date,
                $this->updatedAfter
            ));
        }

        return $collection;
    }
}
