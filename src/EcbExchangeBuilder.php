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
    private ?string $fromCurrency = null;
    private ?string $toCurrency = null;
    private ?string $fromDate = null;
    private ?string $updatedAfter = null;
    private array $toCurrencies = [];

    public function __construct(
        private ExchangeRateService $exchangeRateService
    ) {}

    public function fromCurrency(string $currency): self
    {
        $this->fromCurrency = $currency;
        return $this;
    }

    public function toCurrency(string $currency): self
    {
        $this->toCurrency = $currency;
        return $this;
    }

    public function toCurrencies(array $currencies): self
    {
        $this->toCurrencies = $currencies;
        return $this;
    }

    public function date(string $date): self
    {
        $this->fromDate = $date;
        return $this;
    }

    public function updatedAfter(string $timestamp): self
    {
        $this->updatedAfter = $timestamp;
        return $this;
    }

    public function get(): EcbExchangeRate|EcbExchangeRateCollection
    {
        if (!empty($this->toCurrencies)) {
            return $this->getMultipleRates();
        }

        return $this->getSingleRate();
    }

    private function getSingleRate(): EcbExchangeRate
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

    private function getMultipleRates(): EcbExchangeRateCollection
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
