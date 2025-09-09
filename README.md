[![Test Coverage](https://raw.githubusercontent.com/alimranahmed/ecb-exchange/main/tests/coverage/badge-coverage.svg)](https://packagist.org/packages/alimranahmed/ecb-exchange)
[![MIT Licence](https://badges.frapsoft.com/os/mit/mit.svg?v=103)](https://opensource.org/licenses/mit-license.php)

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

- PHP 7.3 or higher
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

### 1.0.0
- Initial release with basic functionality

## Support

For issues and questions, please use the GitHub issue tracker.

## Disclaimer

This package is for informational purposes only. The ECB reference rates are published for information purposes only, and using the rates for transaction purposes is strongly discouraged.
