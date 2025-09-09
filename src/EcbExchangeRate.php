<?php

namespace EcbExchange;

/**
 * Represents a single exchange rate data point
 */
class EcbExchangeRate
{
    private string $fromCurrency;
    private string $toCurrency;
    private float $rate;
    private string $date;
    private ?string $updatedAfter;

    public function __construct(
        string $fromCurrency,
        string $toCurrency,
        float $rate,
        string $date,
        ?string $updatedAfter = null
    ) {
        $this->fromCurrency = $fromCurrency;
        $this->toCurrency = $toCurrency;
        $this->rate = $rate;
        $this->date = $date;
        $this->updatedAfter = $updatedAfter;
    }

    public function getFromCurrency(): string
    {
        return $this->fromCurrency;
    }

    public function getToCurrency(): string
    {
        return $this->toCurrency;
    }

    public function getRate(): float
    {
        return $this->rate;
    }

    public function getDate(): string
    {
        return $this->date;
    }

    public function getUpdatedAfter(): ?string
    {
        return $this->updatedAfter;
    }

    public function convert(float $amount): float
    {
        return $amount * $this->rate;
    }

    public function toArray(): array
    {
        return [
            'from_currency' => $this->fromCurrency,
            'to_currency' => $this->toCurrency,
            'rate' => $this->rate,
            'date' => $this->date,
            'updated_after' => $this->updatedAfter,
        ];
    }

    public function __toString(): string
    {
        return "1 {$this->fromCurrency} = {$this->rate} {$this->toCurrency} (on {$this->date})";
    }
}
