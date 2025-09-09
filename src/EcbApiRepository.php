<?php

namespace EcbExchange;

use Exception;

/**
 * ECB API repository implementation
 */
class EcbApiRepository
{
    private const ECB_API_URL = 'https://data-api.ecb.europa.eu/service/data/EXR';
    private const MAJOR_CURRENCIES = ['USD', 'GBP', 'JPY', 'CHF', 'CAD', 'AUD', 'NZD', 'SEK', 'NOK', 'DKK'];
    
    // ECB update schedule: rates are updated around 16:00 CET on working days
    private const ECB_UPDATE_HOUR = 16;
    private const ECB_UPDATE_TIMEZONE = 'Europe/Brussels';

    private $timeout;

    public function __construct($timeout = 30)
    {
        $this->timeout = $timeout;
    }

    public function getRateToEur($currency, $date, $updatedAfter = null)
    {
        if ($currency === 'EUR') {
            return 1.0;
        }

        // Check if we should use a fallback date due to ECB update schedule
        $effectiveDate = $this->getEffectiveDate($date, $updatedAfter);
        
        $url = $this->buildApiUrl($effectiveDate, [$currency]);
        $response = $this->httpGet($url);
        
        $rates = $this->parseExchangeRates($response);
        
        if (!isset($rates[$currency])) {
            throw new Exception("Exchange rate for {$currency} not found for date {$effectiveDate}");
        }

        return $rates[$currency];
    }

    public function getSupportedCurrencies()
    {
        // Use a recent date to get supported currencies
        $recentDate = $this->getRecentWorkingDay();
        $url = $this->buildApiUrl($recentDate, self::MAJOR_CURRENCIES);
        
        try {
            $response = $this->httpGet($url);
            $rates = $this->parseExchangeRates($response);
            $currencies = array_keys($rates);
            $currencies[] = 'EUR'; // EUR is the base currency
            sort($currencies);
            return $currencies;
        } catch (Exception $e) {
            // Fallback to major currencies if API fails
            return array_merge(self::MAJOR_CURRENCIES, ['EUR']);
        }
    }

    public function getTimeSeries($startDate, $endDate, $currencies = [])
    {
        $url = $this->buildTimeSeriesUrl($startDate, $endDate, $currencies);
        $response = $this->httpGet($url);
        
        return $this->parseTimeSeriesData($response, $currencies);
    }

    public function isDataAvailable($date)
    {
        try {
            $url = $this->buildApiUrl($date, ['USD']); // Test with USD
            $response = $this->httpGet($url);
            $rates = $this->parseExchangeRates($response);
            return !empty($rates);
        } catch (Exception $e) {
            return false;
        }
    }

    public function getLastUpdateTime($date)
    {
        // ECB rates are typically updated around 16:00 CET on working days
        $dateTime = new \DateTime($date, new \DateTimeZone(self::ECB_UPDATE_TIMEZONE));
        
        // If it's a weekend, get the last working day
        if ($this->isWeekend($dateTime)) {
            $dateTime = $this->getLastWorkingDay($dateTime);
        }
        
        // Set to 16:00 CET (ECB update time)
        $dateTime->setTime(self::ECB_UPDATE_HOUR, 0, 0);
        
        return $dateTime->format('c'); // ISO 8601 format
    }

    private function httpGet($url)
    {
        $context = stream_context_create([
            'http' => [
                'timeout' => $this->timeout,
                'user_agent' => 'ECB Exchange PHP Client/2.0',
                'method' => 'GET',
                'header' => [
                    'Accept: application/json',
                    'Content-Type: application/json',
                ],
            ],
        ]);

        $response = @file_get_contents($url, false, $context);

        if ($response === false) {
            $error = error_get_last();
            throw new Exception('Failed to fetch data from ECB API: ' . ($error['message'] ?? 'Unknown error'));
        }

        return $response;
    }

    private function parseExchangeRates($jsonResponse)
    {
        $data = json_decode($jsonResponse, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Failed to parse JSON response from ECB API: ' . json_last_error_msg());
        }

        if (!isset($data['dataSets'][0]['series'])) {
            throw new Exception('No exchange rates found in API response');
        }

        $rates = [];
        $series = $data['dataSets'][0]['series'];
        $structure = $data['structure']['dimensions']['series'];

        // Find currency dimension index
        $currencyIndex = $this->findDimensionIndex($structure, 'CURRENCY');

        // Parse each series
        foreach ($series as $seriesKey => $seriesData) {
            $keyParts = explode(':', $seriesKey);
            $currencyIndexInKey = $keyParts[$currencyIndex] ?? null;

            if ($currencyIndexInKey !== null && isset($seriesData['observations'])) {
                // Get the currency name from the structure
                $currency = $structure[$currencyIndex]['values'][$currencyIndexInKey]['id'] ?? null;
                
                if ($currency) {
                    // Get the first observation (most recent rate for the date)
                    $observations = $seriesData['observations'];
                    $firstObservation = reset($observations);

                    if (isset($firstObservation[0])) {
                        $rates[$currency] = (float) $firstObservation[0];
                    }
                }
            }
        }

        return $rates;
    }

    private function parseTimeSeriesData($jsonResponse, $currencies = [])
    {
        $data = json_decode($jsonResponse, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Failed to parse JSON response from ECB API: ' . json_last_error_msg());
        }

        $timeSeries = [];
        $series = $data['dataSets'][0]['series'];
        $structure = $data['structure']['dimensions']['series'];
        $observations = $data['structure']['dimensions']['observation'];

        // Find currency and time dimension indices
        $currencyIndex = $this->findDimensionIndex($structure, 'CURRENCY');
        $timeIndex = $this->findDimensionIndex($observations, 'TIME_PERIOD');

        // Get time periods
        $timePeriods = [];
        if (isset($data['structure']['dimensions']['observation'][$timeIndex]['values'])) {
            foreach ($data['structure']['dimensions']['observation'][$timeIndex]['values'] as $timeValue) {
                $timePeriods[] = $timeValue['id'];
            }
        }

        // Parse each series
        foreach ($series as $seriesKey => $seriesData) {
            $keyParts = explode(':', $seriesKey);
            $currencyIndexInKey = $keyParts[$currencyIndex] ?? null;

            if ($currencyIndexInKey !== null && isset($seriesData['observations'])) {
                // Get the currency name from the structure
                $currency = $structure[$currencyIndex]['values'][$currencyIndexInKey]['id'] ?? null;
                
                // Apply currency filter if specified
                if (!empty($currencies) && !in_array($currency, $currencies)) {
                    continue;
                }
                
                if ($currency) {
                    $observations = $seriesData['observations'];
                    $observationIndex = 0;
                    
                    foreach ($observations as $obsKey => $obsValue) {
                        if (isset($timePeriods[$observationIndex]) && isset($obsValue[0])) {
                            $date = $timePeriods[$observationIndex];
                            $rate = (float) $obsValue[0];
                            
                            if (!isset($timeSeries[$date])) {
                                $timeSeries[$date] = [];
                            }
                            
                            $timeSeries[$date][$currency] = $rate;
                        }
                        $observationIndex++;
                    }
                }
            }
        }

        return $timeSeries;
    }

    private function findDimensionIndex($dimensions, $dimensionId)
    {
        foreach ($dimensions as $index => $dimension) {
            if (isset($dimension['id']) && $dimension['id'] === $dimensionId) {
                return $index;
            }
        }
        
        throw new Exception("Dimension '{$dimensionId}' not found in API response");
    }

    private function buildApiUrl($date, $currencies)
    {
        $currencyList = implode('+', $currencies);
        $url = self::ECB_API_URL . '/D.' . $currencyList . '.EUR.SP00.A';
        $url .= "?startPeriod={$date}&endPeriod={$date}&format=jsondata";
        
        return $url;
    }

    private function buildTimeSeriesUrl($startDate, $endDate, $currencies = [])
    {
        $currencyList = empty($currencies) ? implode('+', self::MAJOR_CURRENCIES) : implode('+', $currencies);
        $url = self::ECB_API_URL . '/D.' . $currencyList . '.EUR.SP00.A';
        $url .= "?startPeriod={$startDate}&endPeriod={$endDate}&format=jsondata";
        
        return $url;
    }

    private function getEffectiveDate($date, $updatedAfter = null)
    {
        $requestDate = new \DateTime($date, new \DateTimeZone(self::ECB_UPDATE_TIMEZONE));
        
        // If updatedAfter is provided, check if we need to use a different date
        if ($updatedAfter) {
            $updatedTime = new \DateTime($updatedAfter);
            $requestDate->setTimezone($updatedTime->getTimezone());
            
            // If the request is before ECB update time, use previous working day
            if ($requestDate->format('H:i') < '16:00') {
                $requestDate = $this->getPreviousWorkingDay($requestDate);
            }
        }
        
        // Ensure we're using a working day
        if ($this->isWeekend($requestDate)) {
            $requestDate = $this->getLastWorkingDay($requestDate);
        }
        
        // Try to find a date with available data
        $originalDate = $requestDate->format('Y-m-d');
        $attempts = 0;
        $maxAttempts = 7; // Try up to a week back
        
        while ($attempts < $maxAttempts) {
            if ($this->isDataAvailable($requestDate->format('Y-m-d'))) {
                return $requestDate->format('Y-m-d');
            }
            
            $requestDate = $this->getPreviousWorkingDay($requestDate);
            $attempts++;
        }
        
        // If no data found, return the original date and let the error bubble up
        return $originalDate;
    }

    private function getRecentWorkingDay()
    {
        $date = new \DateTime('now', new \DateTimeZone(self::ECB_UPDATE_TIMEZONE));
        
        // If it's weekend, get last working day
        if ($this->isWeekend($date)) {
            $date = $this->getLastWorkingDay($date);
        }
        
        return $date->format('Y-m-d');
    }

    private function isWeekend(\DateTime $date)
    {
        $dayOfWeek = (int) $date->format('N');
        return $dayOfWeek >= 6; // Saturday = 6, Sunday = 7
    }

    private function getLastWorkingDay(\DateTime $date)
    {
        do {
            $date->modify('-1 day');
        } while ($this->isWeekend($date));
        
        return $date;
    }

    private function getPreviousWorkingDay(\DateTime $date)
    {
        do {
            $date->modify('-1 day');
        } while ($this->isWeekend($date));
        
        return $date;
    }
}
