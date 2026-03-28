<?php

namespace Drupal\event_flow\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

class EventRegistration extends ContentEntityBase implements ContentEntityInterface {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['event_id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Event ID'))
      ->setDescription(t('The ID of the event this registration belongs to.'))
      ->setRequired(TRUE);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('User'))
      ->setDescription(t('The registered user.'))
      ->setSetting('target_type', 'user')
      ->setRequired(TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Registered on'))
      ->setDescription(t('The time the user registered for the event.'));

    return $fields;
  }

}