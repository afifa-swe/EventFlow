<?php

namespace Drupal\event_flow\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the Event entity add/edit forms.
 */
class EventForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    $start = $form_state->getValue('start_date');
    $end = $form_state->getValue('end_date');

    if (!empty($start[0]['value']) && !empty($end[0]['value'])) {
      $start_date = $start[0]['value'];
      $end_date = $end[0]['value'];
      if ($start_date instanceof \Drupal\Core\Datetime\DrupalDateTime && $end_date instanceof \Drupal\Core\Datetime\DrupalDateTime) {
        if ($end_date < $start_date) {
          $form_state->setErrorByName('end_date', $this->t('End date must be after the start date.'));
        }
      }
    }

    $lat = $form_state->getValue('latitude');
    if (!empty($lat[0]['value'])) {
      $lat_val = (float) $lat[0]['value'];
      if ($lat_val < -90 || $lat_val > 90) {
        $form_state->setErrorByName('latitude', $this->t('Latitude must be between -90 and 90.'));
      }
    }

    $lon = $form_state->getValue('longitude');
    if (!empty($lon[0]['value'])) {
      $lon_val = (float) $lon[0]['value'];
      if ($lon_val < -180 || $lon_val > 180) {
        $form_state->setErrorByName('longitude', $this->t('Longitude must be between -180 and 180.'));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->getEntity();
    $status = parent::save($form, $form_state);

    if ($status === SAVED_NEW) {
      $this->messenger()->addStatus($this->t('Event %label has been created.', [
        '%label' => $entity->label(),
      ]));
    }
    else {
      $this->messenger()->addStatus($this->t('Event %label has been updated.', [
        '%label' => $entity->label(),
      ]));
    }

    $form_state->setRedirectUrl($entity->toUrl('canonical'));
    return $status;
  }

}