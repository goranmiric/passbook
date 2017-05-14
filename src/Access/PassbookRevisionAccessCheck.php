<?php

namespace Drupal\passbook\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\passbook\Entity\PassbookInterface;
use Symfony\Component\Routing\Route;

/**
 * Provides an access checker for revisions.
 *
 * @ingroup passbook_access
 */
class PassbookRevisionAccessCheck implements AccessInterface {

  /**
   * The passbook storage.
   *
   * @var \Drupal\passbook\PassbookStorageInterface
   */
  protected $entityStorage;

  /**
   * The entity access control handler.
   *
   * @var \Drupal\Core\Entity\EntityAccessControlHandlerInterface
   */
  protected $entityAccess;

  /**
   * A static cache of access checks.
   *
   * @var array
   */
  protected $access = [];

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   */
  public function __construct(EntityManagerInterface $entity_manager) {
    $this->entityStorage = $entity_manager->getStorage('passbook');
    $this->entityAccess = $entity_manager->getAccessControlHandler('passbook');
  }

  /**
   * Checks routing access for the revision.
   *
   * @param \Symfony\Component\Routing\Route $route
   *   The route to check against.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The currently logged in account.
   * @param int $passbook_revision
   *   (optional) The revision ID. If not specified.
   * @param \Drupal\passbook\Entity\PassbookInterface $passbook
   *   (optional) A entity object.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function access(Route $route, AccountInterface $account, $passbook_revision = NULL, PassbookInterface $passbook = NULL) {
    if ($passbook_revision) {
      $passbook = $this->entityStorage->loadRevision($passbook_revision);
    }
    $operation = $route->getRequirement('_access_passbook_revision');

    return AccessResult::allowedIf($passbook && $this->checkAccess($passbook, $account, $operation))->cachePerPermissions()->addCacheableDependency($passbook);
  }

  /**
   * Checks revision access.
   *
   * @param \Drupal\passbook\Entity\PassbookInterface $entity
   *   The entity to check.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   A user object representing the user for whom the operation is to be
   *   performed.
   * @param string $op
   *   (optional) The specific operation being checked. Defaults to 'view.'.
   *
   * @return bool
   *   TRUE if the operation may be performed, FALSE otherwise.
   */
  public function checkAccess(PassbookInterface $entity, AccountInterface $account, $op = 'view') {
    $map = [
      'view' => 'view all revisions',
      'update' => 'revert all revisions',
      'delete' => 'delete all revisions',
    ];
    $bundle = $entity->bundle();
    $type_map = [
      'view' => "view $bundle revisions",
      'update' => "revert $bundle revisions",
      'delete' => "delete $bundle revisions",
    ];

    if (!$entity || !isset($map[$op]) || !isset($type_map[$op])) {
      return FALSE;
    }

    // Statically cache access by revision ID, language code, user account ID,
    // and operation.
    $langcode = $entity->language()->getId();
    $cid = $entity->getRevisionId() . ':' . $langcode . ':' . $account->id() . ':' . $op;

    if (!isset($this->access[$cid])) {
      // Perform basic permission checks first.
      if (!$account->hasPermission($map[$op]) && !$account->hasPermission($type_map[$op]) && !$account->hasPermission('administer passbooks')) {
        $this->access[$cid] = FALSE;
        return FALSE;
      }

      // There should be at least two revisions. If the vid of the given entity
      // and the vid of the default revision differ, then we already have two
      // different revisions so there is no need for a separate database check.
      // Also, if you try to revert to or delete the default revision, that's
      // not good.
      if ($entity->isDefaultRevision() && ($this->entityStorage->countDefaultLanguageRevisions($entity) == 1 || $op == 'update' || $op == 'delete')) {
        $this->access[$cid] = FALSE;
      }
      elseif ($account->hasPermission('administer passbooks')) {
        $this->access[$cid] = TRUE;
      }
      else {
        // First check the access to the default revision and finally, if the
        // entity passed in is not the default revision then access to that too.
        $this->access[$cid] = $this->entityAccess->access($this->entityStorage->load($entity->id()), $op, $account) && ($entity->isDefaultRevision() || $this->entityAccess->access($entity, $op, $account));
      }
    }

    return $this->access[$cid];
  }

}
