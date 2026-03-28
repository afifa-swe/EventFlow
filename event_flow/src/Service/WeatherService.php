<?php

namespace Drupal\event_flow\Service;

use Drupal\Core\Cache\CacheBackendInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;

class WeatherService {

  /**
   * The HTTP client.
   */
  protected ClientInterface $httpClient;

  /**
   * The cache backend.
   */
  protected CacheBackendInterface $cache;

  /**
   * Constructs a WeatherService.
   */
  public function __construct(ClientInterface $http_client, CacheBackendInterface $cache) {
    $this->httpClient = $http_client;
    $this->cache = $cache;
  }

  /**
   * Gets current weather data for given coordinates.
   */
  public function getWeather(float $lat, float $lon): ?array {
    $cid = 'event_flow:weather:' . round($lat, 4) . ':' . round($lon, 4);

    if ($cached = $this->cache->get($cid)) {
      return $cached->data;
    }

    try {
      $url = sprintf(
        'https://api.open-meteo.com/v1/forecast?latitude=%s&longitude=%s&current_weather=true',
        $lat,
        $lon
      );

      $response = $this->httpClient->request('GET', $url, [
        'timeout' => 5,
      ]);

      $data = json_decode((string) $response->getBody(), TRUE);

      if (empty($data['current_weather'])) {
        return NULL;
      }

      $current = $data['current_weather'];
      $weather = [
        'temperature' => $current['temperature'],
        'windspeed' => $current['windspeed'],
        'winddirection' => $current['winddirection'] ?? NULL,
        'weathercode' => $current['weathercode'],
        'description' => $this->mapWeatherCode((int) $current['weathercode']),
        'is_day' => $current['is_day'] ?? 1,
      ];

      // Cache for 10 minutes.
      $this->cache->set($cid, $weather, time() + 600);

      return $weather;
    }
    catch (GuzzleException $e) {
      \Drupal::logger('event_flow')->error('Weather API error: @message', [
        '@message' => $e->getMessage(),
      ]);
      return NULL;
    }
  }

  /**
   * Maps WMO weather codes to human-readable descriptions.
   */
  protected function mapWeatherCode(int $code): string {
    $codes = [
      0 => 'Clear sky',
      1 => 'Mainly clear',
      2 => 'Partly cloudy',
      3 => 'Overcast',
      45 => 'Fog',
      48 => 'Depositing rime fog',
      51 => 'Light drizzle',
      53 => 'Moderate drizzle',
      55 => 'Dense drizzle',
      56 => 'Light freezing drizzle',
      57 => 'Dense freezing drizzle',
      61 => 'Slight rain',
      63 => 'Moderate rain',
      65 => 'Heavy rain',
      66 => 'Light freezing rain',
      67 => 'Heavy freezing rain',
      71 => 'Slight snowfall',
      73 => 'Moderate snowfall',
      75 => 'Heavy snowfall',
      77 => 'Snow grains',
      80 => 'Slight rain showers',
      81 => 'Moderate rain showers',
      82 => 'Violent rain showers',
      85 => 'Slight snow showers',
      86 => 'Heavy snow showers',
      95 => 'Thunderstorm',
      96 => 'Thunderstorm with slight hail',
      99 => 'Thunderstorm with heavy hail',
    ];

    return $codes[$code] ?? 'Unknown';
  }

}