<?php

namespace Drupal\passbook;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\passbook\Entity\PassbookInterface;

/**
 * Defines the storage handler class.
 */
class PassbookStorage extends SqlContentEntityStorage implements PassbookStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(PassbookInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {passbook_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {passbook_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(PassbookInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {passbook_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function updateType($old_type, $new_type) {
    return $this->database->update('passbook')
      ->fields(['type' => $new_type])
      ->condition('type', $old_type)
      ->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('passbook_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
