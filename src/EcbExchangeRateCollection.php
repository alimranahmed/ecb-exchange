<?php

namespace EcbExchange;

/**
 * Collection of exchange rates
 */
class EcbExchangeRateCollection
{
    private array $rates = [];

    public function add(EcbExchangeRate $rate): void
    {
        $this->rates[] = $rate;
    }

    public function addAll(array $rates): void
    {
        foreach ($rates as $rate) {
            if ($rate instanceof EcbExchangeRate) {
                $this->add($rate);
            }
        }
    }

    public function getRates(): array
    {
        return $this->rates;
    }

    public function count(): int
    {
        return count($this->rates);
    }

    public function isEmpty(): bool
    {
        return empty($this->rates);
    }

    public function filterByCurrency(string $currency): EcbExchangeRateCollection
    {
        $filtered = new self();
        foreach ($this->rates as $rate) {
            if ($rate->getFromCurrency() === $currency || $rate->getToCurrency() === $currency) {
                $filtered->add($rate);
            }
        }
        return $filtered;
    }

    public function filterByDate(string $date): EcbExchangeRateCollection
    {
        $filtered = new self();
        foreach ($this->rates as $rate) {
            if ($rate->getDate() === $date) {
                $filtered->add($rate);
            }
        }
        return $filtered;
    }

    public function toArray(): array
    {
        return array_map(fn($rate) => $rate->toArray(), $this->rates);
    }

    public function getFirst(): ?EcbExchangeRate
    {
        return $this->rates[0] ?? null;
    }

    public function getLast(): ?EcbExchangeRate
    {
        return end($this->rates) ?: null;
    }

    public function __toString(): string
    {
        return implode("\n", array_map(fn($rate) => (string) $rate, $this->rates));
    }
}
