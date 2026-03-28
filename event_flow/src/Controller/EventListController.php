<?php
namespace Drupal\event_flow\Controller;

use Drupal\Core\Controller\ControllerBase;

class EventListController extends ControllerBase {

  /**
   * Displays a list of all events as cards.
   */
  public function list() {
    $storage = $this->entityTypeManager()->getStorage('event');

    $ids = $storage->getQuery()
      ->accessCheck(TRUE)
      ->sort('start_date', 'ASC')
      ->execute();

    $events = $storage->loadMultiple($ids);

    $cards = [];
    foreach ($events as $event) {
      $count = $event->getRegistrationCount();
      $max = (int) $event->get('max_participants')->value;
      $percent = $max > 0 ? round(($count / $max) * 100) : 0;
      $status = $event->get('status')->value;

      $cards[] = [
        'title' => $event->toLink()->toString(),
        'start_date' => $event->get('start_date')->value,
        'end_date' => $event->get('end_date')->value,
        'status' => $status,
        'status_label' => $status === 'active' ? $this->t('Active') : $this->t('Completed'),
        'count' => $count,
        'max' => $max,
        'percent' => min($percent, 100),
      ];
    }

    $build = [
      '#theme' => 'event_list',
      '#events' => $cards,
      '#attached' => [
        'library' => ['event_flow/event_flow'],
      ],
    ];

    return $build;
  }

}