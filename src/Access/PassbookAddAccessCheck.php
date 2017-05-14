<?php

namespace Drupal\passbook\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\passbook\Entity\PassbookTypeInterface;

/**
 * Determines access to for add pages.
 *
 * @ingroup passbook_access
 */
class PassbookAddAccessCheck implements AccessInterface {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityManager;

  /**
   * Constructs a EntityCreateAccessCheck object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   The entity manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_manager) {
    $this->entityManager = $entity_manager;
  }

  /**
   * Checks access to the add page for the passbook type.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The currently logged in account.
   * @param \Drupal\passbook\Entity\PassbookTypeInterface $passbook_type
   *   (optional) Passbook type. If not specified, access is allowed if there
   *   exists at least one passbook type for which the user may create a entity.
   *
   * @return string
   *   A \Drupal\Core\Access\AccessInterface constant value.
   */
  public function access(AccountInterface $account, PassbookTypeInterface $passbook_type = NULL) {
    $access_control_handler = $this->entityManager->getAccessControlHandler('passbook');
    // If checking whether a entity of a particular type may be created.
    if ($account->hasPermission('administer passbook types')) {
      return AccessResult::allowed()->cachePerPermissions();
    }
    if ($passbook_type) {
      return $access_control_handler->createAccess($passbook_type->id(), $account, [], TRUE);
    }
    // If checking whether a entity of any type may be created.
    foreach ($this->entityManager->getStorage('passbook_type')->loadMultiple() as $passbook_type) {
      if (($access = $access_control_handler->createAccess($passbook_type->id(), $account, [], TRUE)) && $access->isAllowed()) {
        return $access;
      }
    }

    // No opinion.
    return AccessResult::neutral();
  }

}
