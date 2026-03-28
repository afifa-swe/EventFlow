<?php

namespace Drupal\event_flow;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;

/**
 * Provides a list builder for the Event entity.
 */
class EventListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('ID');
    $header['title'] = $this->t('Title');
    $header['start_date'] = $this->t('Start date');
    $header['status'] = $this->t('Status');
    $header['participants'] = $this->t('Participants');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\event_flow\Entity\Event $entity */
    $row['id'] = $entity->id();
    $row['title'] = $entity->toLink();
    $row['start_date'] = $entity->get('start_date')->value;
    $row['status'] = $entity->get('status')->value;
    $row['participants'] = $entity->getRegistrationCount() . ' / ' . $entity->get('max_participants')->value;
    return $row + parent::buildRow($entity);
  }

}