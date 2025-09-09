<?php

namespace EcbExchange;

/**
 * Collection of exchange rates
 */
class EcbExchangeRateCollection
{
    private $rates = [];

    public function add(EcbExchangeRate $rate)
    {
        $this->rates[] = $rate;
    }

    public function addAll($rates)
    {
        foreach ($rates as $rate) {
            if ($rate instanceof EcbExchangeRate) {
                $this->add($rate);
            }
        }
    }

    public function getRates()
    {
        return $this->rates;
    }

    public function count()
    {
        return count($this->rates);
    }

    public function isEmpty()
    {
        return empty($this->rates);
    }

    public function filterByCurrency($currency)
    {
        $filtered = new self();
        foreach ($this->rates as $rate) {
            if ($rate->getFromCurrency() === $currency || $rate->getToCurrency() === $currency) {
                $filtered->add($rate);
            }
        }
        return $filtered;
    }

    public function filterByDate($date)
    {
        $filtered = new self();
        foreach ($this->rates as $rate) {
            if ($rate->getDate() === $date) {
                $filtered->add($rate);
            }
        }
        return $filtered;
    }

    public function toArray()
    {
        return array_map(function($rate) { return $rate->toArray(); }, $this->rates);
    }

    public function getFirst()
    {
        return $this->rates[0] ?? null;
    }

    public function getLast()
    {
        return end($this->rates) ?: null;
    }

    public function __toString()
    {
        return implode("\n", array_map(function($rate) { return (string) $rate; }, $this->rates));
    }
}
