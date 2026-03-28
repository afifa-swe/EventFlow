<?php

namespace Drupal\event_flow;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Access control handler for the Event Registration entity.
 */
class EventRegistrationAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    switch ($operation) {
      case 'view':
        if ($account->hasPermission('administer events')) {
          return AccessResult::allowed()->cachePerPermissions();
        }
        return AccessResult::allowedIf((int) $entity->get('uid')->target_id === (int) $account->id())
          ->cachePerUser()
          ->addCacheableDependency($entity);

      case 'delete':
        if ($account->hasPermission('administer events')) {
          return AccessResult::allowed()->cachePerPermissions();
        }
        return AccessResult::allowedIf((int) $entity->get('uid')->target_id === (int) $account->id())
          ->cachePerUser()
          ->addCacheableDependency($entity);
    }

    return parent::checkAccess($entity, $operation, $account);
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'register for events');
  }

}