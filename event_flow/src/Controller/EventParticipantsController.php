<?php

namespace Drupal\event_flow\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\event_flow\Entity\Event;

class EventParticipantsController extends ControllerBase {

  /**
   * Displays a list of participants registered for an event.
   */
  public function list(Event $event) {
    $storage = $this->entityTypeManager()->getStorage('event_registration');
    $registrations = $storage->loadByProperties(['event_id' => $event->id()]);

    $rows = [];
    foreach ($registrations as $registration) {
      $user = $registration->get('uid')->entity;
      if ($user) {
        $rows[] = [
          $user->getDisplayName(),
          $user->getEmail(),
          \Drupal::service('date.formatter')->format(
            (int) $registration->get('created')->value,
            'medium'
          ),
        ];
      }
    }

    $build = [];

    $build['title'] = [
      '#markup' => '<h2>' . $this->t('Participants for: @event', ['@event' => $event->label()]) . '</h2>',
    ];

    $build['info'] = [
      '#markup' => '<p>' . $this->t('Registered: @count / @max', [
        '@count' => count($rows),
        '@max' => $event->get('max_participants')->value,
      ]) . '</p>',
    ];

    $build['table'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Name'),
        $this->t('Email'),
        $this->t('Registered on'),
      ],
      '#rows' => $rows,
      '#empty' => $this->t('No participants registered yet.'),
    ];

    return $build;
  }

}