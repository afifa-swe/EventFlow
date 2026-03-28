<?php

namespace Drupal\event_flow\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\event_flow\Entity\Event;
use Drupal\event_flow\Service\WeatherService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
class EventWeatherController extends ControllerBase {

  /**
   * The weather service.
   */
  protected WeatherService $weatherService;

  /**
   * Constructs the controller.
   */
  public function __construct(WeatherService $weather_service) {
    $this->weatherService = $weather_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('event_flow.weather_service')
    );
  }

  /**
   * Returns weather data for an event location as JSON.
   */
  public function getWeather(Event $event) {
    $lat = (float) $event->get('latitude')->value;
    $lon = (float) $event->get('longitude')->value;

    $weather = $this->weatherService->getWeather($lat, $lon);

    if ($weather === NULL) {
      return new JsonResponse(['error' => 'Unable to fetch weather data.'], 503);
    }

    return new JsonResponse($weather);
  }

}