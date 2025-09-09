<?php

namespace EcbExchange;

/**
 * Represents a single exchange rate data point
 */
class EcbExchangeRate
{
    private $fromCurrency;
    private $toCurrency;
    private $rate;
    private $date;
    private $updatedAfter;

    public function __construct(
        $fromCurrency,
        $toCurrency,
        $rate,
        $date,
        $updatedAfter = null
    ) {
        $this->fromCurrency = $fromCurrency;
        $this->toCurrency = $toCurrency;
        $this->rate = $rate;
        $this->date = $date;
        $this->updatedAfter = $updatedAfter;
    }

    public function getFromCurrency()
    {
        return $this->fromCurrency;
    }

    public function getToCurrency()
    {
        return $this->toCurrency;
    }

    public function getRate()
    {
        return $this->rate;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function getUpdatedAfter()
    {
        return $this->updatedAfter;
    }

    public function convert($amount)
    {
        return $amount * $this->rate;
    }

    public function toArray()
    {
        return [
            'from_currency' => $this->fromCurrency,
            'to_currency' => $this->toCurrency,
            'rate' => $this->rate,
            'date' => $this->date,
            'updated_after' => $this->updatedAfter,
        ];
    }

    public function __toString()
    {
        return "1 {$this->fromCurrency} = {$this->rate} {$this->toCurrency} (on {$this->date})";
    }
}
