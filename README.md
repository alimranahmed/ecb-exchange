# ECB Exchange Rate PHP Package

A modern, fluent PHP package for accessing European Central Bank (ECB) exchange rate data [API](https://data.ecb.europa.eu/help/api/data)

## Features

- **ECB Schedule Aware** - Handles ECB's 16:00 CET update schedule
- **Multiple Data Formats** - Single rates, collections, and time series
- **Automatic Fallbacks** - Smart date handling for weekends and holidays

## Installation

```bash
composer require alimranahmed/ecb-exchange
```

## Quick Start

### Basic Usage

```php
use EcbExchange\Ecb;

// Get a single exchange rate
$rate = Ecb::exchange()
    ->fromCurrency('USD')
    ->toCurrency('EUR')
    ->date('2025-09-01')
    ->updatedAfter('2009-05-15T14:15:00+01:00')
    ->get();

echo $rate; // "1 USD = 0.85 EUR (on 2025-09-01)"

// Convert amount
$amount = $rate->convert(100); // 85.0
```

### Multiple Currencies

```php
// Get multiple exchange rates at once
$rates = Ecb::exchange()
    ->fromCurrency('EUR')
    ->toCurrencies(['USD', 'GBP', 'JPY', 'CHF'])
    ->date('2025-09-01')
    ->get();

foreach ($rates as $rate) {
    echo $rate . "\n";
}
```

### Using EUR as Base (Default)

```php
// When no fromCurrency is specified, EUR is used as base
$rate = Ecb::exchange()
    ->toCurrency('USD')
    ->date('2025-09-01')
    ->get();

// Same as above
$rate = Ecb::exchange()
    ->fromCurrency('EUR')
    ->toCurrency('USD')
    ->date('2025-09-01')
    ->get();
```

### Time Series Data

```php
// Get historical data
$timeSeries = Ecb::getTimeSeries('2025-01-01', '2025-01-31', ['USD', 'GBP']);

foreach ($timeSeries as $date => $rates) {
    echo "Date: $date\n";
    foreach ($rates as $currency => $rate) {
        echo "  1 EUR = $rate $currency\n";
    }
}
```

### Get Supported Currencies

```php
$currencies = Ecb::getSupportedCurrencies();
// Returns: ['AUD', 'CAD', 'CHF', 'DKK', 'EUR', 'GBP', 'JPY', 'NOK', 'NZD', 'SEK', 'USD']
```

## API Reference

### Ecb::exchange()

Returns a new `EcbExchangeBuilder` instance for fluent API usage.

### EcbExchangeBuilder Methods

#### fromCurrency(string $currency)
Set the source currency. If not specified, EUR is used as default.

#### toCurrency(string $currency)
Set the target currency for single rate queries.

#### toCurrencies(array $currencies)
Set multiple target currencies for batch queries.

#### date(string $date)
Set the date for exchange rates in YYYY-MM-DD format.

#### updatedAfter(string $timestamp)
Set a filter for data updated after the specified ISO 8601 timestamp.

#### get()
Execute the query and return:
- `EcbExchangeRate` for single currency queries
- `EcbExchangeRateCollection` for multiple currency queries

### EcbExchangeRate Methods

#### getFromCurrency(): string
Get the source currency code.

#### getToCurrency(): string
Get the target currency code.

#### getRate(): float
Get the exchange rate.

#### getDate(): string
Get the date of the exchange rate.

#### getUpdatedAfter(): ?string
Get the updated after timestamp.

#### convert(float $amount): float
Convert an amount using this exchange rate.

#### toArray(): array
Convert to array representation.

#### __toString(): string
String representation of the exchange rate.

### EcbExchangeRateCollection Methods

#### add(EcbExchangeRate $rate): void
Add an exchange rate to the collection.

#### getRates(): array
Get all exchange rates in the collection.

#### count(): int
Get the number of rates in the collection.

#### isEmpty(): bool
Check if the collection is empty.

#### filterByCurrency(string $currency): EcbExchangeRateCollection
Filter rates by currency code.

#### filterByDate(string $date): EcbExchangeRateCollection
Filter rates by date.

#### toArray(): array
Convert to array representation.

#### getFirst(): ?EcbExchangeRate
Get the first rate in the collection.

#### getLast(): ?EcbExchangeRate
Get the last rate in the collection.

## ECB Schedule Awareness

This package is aware of the ECB's update schedule:

- **Update Time**: Exchange rates are updated around 16:00 CET every working day
- **Concentration Procedure**: Based on daily concertation between central banks around 14:10 CET
- **TARGET Days**: No updates on TARGET closing days
- **Weekend Handling**: Automatically uses the last working day's data for weekend requests

The package automatically handles:
- Weekend and holiday fallbacks
- Time zone considerations
- Effective date calculations based on update times

## Architecture

The package follows SOLID principles:

- **Single Responsibility**: Each class has one clear purpose
- **Open/Closed**: Easy to extend without modifying existing code
- **Liskov Substitution**: Interfaces can be substituted with implementations
- **Interface Segregation**: Small, focused interfaces
- **Dependency Inversion**: Depends on abstractions, not concretions

### Key Components

- `Ecb` - Main entry point
- `EcbExchangeBuilder` - Fluent API builder
- `ExchangeRateService` - Business logic service
- `EcbApiRepository` - Data access layer with integrated HTTP client and data parsing
- `EcbExchangeRate` - Single exchange rate data object
- `EcbExchangeRateCollection` - Collection of exchange rates

## Error Handling

The package throws `Exception` for various error conditions:

```php
try {
    $rate = Ecb::exchange()
        ->fromCurrency('USD')
        ->toCurrency('EUR')
        ->date('2025-09-01')
        ->get();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
```

Common error scenarios:
- Invalid currency codes
- Network connectivity issues
- API response parsing errors
- Date availability issues

## Testing

```bash
composer test
```

## Requirements

- PHP 7.4 or higher
- Internet connection for API access

## License

MIT License. See LICENSE file for details.

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests
5. Submit a pull request

## Changelog

### 2.0.0
- Complete rewrite with SOLID architecture
- Fluent API implementation
- ECB schedule awareness
- Type safety improvements
- Better error handling

### 1.0.0
- Initial release with basic functionality

## Support

For issues and questions, please use the GitHub issue tracker.

## Disclaimer

This package is for informational purposes only. The ECB reference rates are published for information purposes only, and using the rates for transaction purposes is strongly discouraged.
