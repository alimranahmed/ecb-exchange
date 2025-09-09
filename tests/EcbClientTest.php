<?php

namespace EcbExchange\Tests;

use PHPUnit\Framework\TestCase;
use EcbExchange\Ecb;

class EcbClientTest extends TestCase
{
    public function testGetSupportedCurrencies()
    {
        $currencies = Ecb::getSupportedCurrencies();
        
        $this->assertIsArray($currencies);
        $this->assertContains('EUR', $currencies);
        $this->assertContains('USD', $currencies);
        $this->assertContains('GBP', $currencies);
    }

    public function testGetExchangeRateForSpecificDate()
    {
        $rate = Ecb::exchange()
            ->fromCurrency('USD')
            ->toCurrency('EUR')
            ->date('2024-12-27')
            ->get();
        
        $this->assertIsFloat($rate->getRate());
        $this->assertGreaterThan(0, $rate->getRate());
        $this->assertEquals('USD', $rate->getFromCurrency());
        $this->assertEquals('EUR', $rate->getToCurrency());
        $this->assertEquals('2024-12-27', $rate->getDate());
    }

    public function testGetMultipleExchangeRates()
    {
        $rates = Ecb::exchange()
            ->fromCurrency('EUR')
            ->toCurrencies(['USD', 'GBP'])
            ->date('2024-12-27')
            ->get();
        
        $this->assertEquals(2, $rates->count());
        $this->assertFalse($rates->isEmpty());
    }

    public function testConvertAmount()
    {
        $rate = Ecb::exchange()
            ->fromCurrency('USD')
            ->toCurrency('EUR')
            ->date('2024-12-27')
            ->get();
        
        $converted = $rate->convert(100);
        $this->assertIsFloat($converted);
        $this->assertGreaterThan(0, $converted);
    }

    public function testGetTimeSeries()
    {
        $timeSeries = Ecb::getTimeSeries('2024-12-25', '2024-12-27', ['USD', 'GBP']);
        
        $this->assertIsArray($timeSeries);
        $this->assertNotEmpty($timeSeries);
    }
}