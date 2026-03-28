<?php
namespace Drupal\event_flow\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Field\BaseFieldDefinition;

class Event extends ContentEntityBase implements ContentEntityInterface, EntityChangedInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['title'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Title'))
      ->setDescription(t('The title of the event.'))
      ->setRequired(TRUE)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
        'weight' => -10,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -10,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['description'] = BaseFieldDefinition::create('text_long')
      ->setLabel(t('Description'))
      ->setDescription(t('A description of the event.'))
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'text_default',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'text_textarea',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['start_date'] = BaseFieldDefinition::create('datetime')
      ->setLabel(t('Start date'))
      ->setDescription(t('The start date of the event.'))
      ->setRequired(TRUE)
      ->setSetting('datetime_type', 'datetime')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'datetime_default',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'datetime_default',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['end_date'] = BaseFieldDefinition::create('datetime')
      ->setLabel(t('End date'))
      ->setDescription(t('The end date of the event.'))
      ->setRequired(TRUE)
      ->setSetting('datetime_type', 'datetime')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'datetime_default',
        'weight' => 1,
      ])
      ->setDisplayOptions('form', [
        'type' => 'datetime_default',
        'weight' => 1,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['latitude'] = BaseFieldDefinition::create('float')
      ->setLabel(t('Latitude'))
      ->setDescription(t('Latitude of the event location (-90 to 90).'))
      ->setRequired(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'number_decimal',
        'weight' => 2,
      ])
      ->setDisplayOptions('form', [
        'type' => 'number',
        'weight' => 2,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['longitude'] = BaseFieldDefinition::create('float')
      ->setLabel(t('Longitude'))
      ->setDescription(t('Longitude of the event location (-180 to 180).'))
      ->setRequired(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'number_decimal',
        'weight' => 3,
      ])
      ->setDisplayOptions('form', [
        'type' => 'number',
        'weight' => 3,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['max_participants'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Max participants'))
      ->setDescription(t('Maximum number of participants for this event.'))
      ->setRequired(TRUE)
      ->setSetting('min', 1)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'number_integer',
        'weight' => 4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'number',
        'weight' => 4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['status'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Status'))
      ->setDescription(t('The status of the event.'))
      ->setRequired(TRUE)
      ->setDefaultValue('active')
      ->setSetting('allowed_values', [
        'active' => 'Active',
        'completed' => 'Completed',
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'list_default',
        'weight' => 5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'options_select',
        'weight' => 5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Author'))
      ->setDescription(t('The user who created the event.'))
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setDefaultValueCallback(static::class . '::getDefaultEntityOwner')
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 10,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the event was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the event was last edited.'));

    return $fields;
  }

  /**
   * Default value callback for 'uid' base field.
   */
  public static function getDefaultEntityOwner() {
    return [\Drupal::currentUser()->id()];
  }

  /**
   * Gets the current registration count.
   */
  public function getRegistrationCount(): int {
    return (int) \Drupal::entityQuery('event_registration')
      ->condition('event_id', $this->id())
      ->accessCheck(FALSE)
      ->count()
      ->execute();
  }

  /**
   * Checks if registration is open.
   */
  public function isRegistrationOpen(): bool {
    if ($this->get('status')->value !== 'active') {
      return FALSE;
    }
    return $this->getRegistrationCount() < (int) $this->get('max_participants')->value;
  }

  /**
   * {@inheritdoc}
   */
  public static function preDelete($storage, array $entities) {
    parent::preDelete($storage, $entities);
    foreach ($entities as $event) {
      $registrations = \Drupal::entityTypeManager()
        ->getStorage('event_registration')
        ->loadByProperties(['event_id' => $event->id()]);
      if ($registrations) {
        \Drupal::entityTypeManager()
          ->getStorage('event_registration')
          ->delete($registrations);
      }
    }
  }

}