<?php

namespace Drupal\event_flow\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\event_flow\Entity\Event;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class EventRegistrationController extends ControllerBase {

  /**
   * Registers or unregisters the current user for an event.
   */
  public function register(Event $event) {
    $user = $this->currentUser();

    if ($event->get('status')->value !== 'active') {
      $this->messenger()->addError($this->t('This event is no longer accepting registrations.'));
      return $this->redirect('entity.event.canonical', ['event' => $event->id()]);
    }

    $storage = $this->entityTypeManager()->getStorage('event_registration');

    $existing = $storage->loadByProperties([
      'event_id' => $event->id(),
      'uid' => $user->id(),
    ]);

    if ($existing) {
      $storage->delete($existing);
      $this->messenger()->addStatus($this->t('You have been unregistered from %event.', [
        '%event' => $event->label(),
      ]));
    }
    else {
      $count = $event->getRegistrationCount();
      $max = (int) $event->get('max_participants')->value;

      if ($count >= $max) {
        $this->messenger()->addError($this->t('Registration for %event is full.', [
          '%event' => $event->label(),
        ]));
        return $this->redirect('entity.event.canonical', ['event' => $event->id()]);
      }

      $registration = $storage->create([
        'event_id' => $event->id(),
        'uid' => $user->id(),
      ]);
      $registration->save();

      $this->messenger()->addStatus($this->t('You have been registered for %event.', [
        '%event' => $event->label(),
      ]));

      $new_count = $event->getRegistrationCount();
      if ($new_count >= $max) {
        $event->set('status', 'completed');
        $event->save();
        $this->messenger()->addStatus($this->t('Registration for %event is now closed (maximum participants reached).', [
          '%event' => $event->label(),
        ]));
      }
    }

    return $this->redirect('entity.event.canonical', ['event' => $event->id()]);
  }

}