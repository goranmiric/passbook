<?php

namespace Drupal\passbook\Form;

use Drupal\Core\Entity\ContentEntityDeleteForm;

/**
 * Provides a form for deleting an entity.
 */
class PassbookDeleteForm extends ContentEntityDeleteForm {

  /**
   * {@inheritdoc}
   */
  protected function getDeletionMessage() {
    /** @var \Drupal\passbook\Entity\PassbookInterface $entity */
    $entity = $this->getEntity();

    $passbookTypeStorage = $this->entityManager->getStorage('passbook_type');
    $passbookType = $passbookTypeStorage->load($entity->bundle())->label();

    if (!$entity->isDefaultTranslation()) {
      return $this->t('@language translation of the @type %label has been deleted.', [
        '@language' => $entity->language()->getName(),
        '@type' => $passbookType,
        '%label' => $entity->label(),
      ]);
    }

    return $this->t('The @type %title has been deleted.', [
      '@type' => $passbookType,
      '%title' => $this->getEntity()->label(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  protected function logDeletionMessage() {
    /** @var \Drupal\passbook\Entity\PassbookInterface $entity */
    $entity = $this->getEntity();
    $this->logger('passbook')->notice('@type: deleted %title.', ['@type' => $entity->getType(), '%title' => $entity->label()]);
  }

}
