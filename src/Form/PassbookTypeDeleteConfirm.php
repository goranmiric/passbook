<?php

namespace Drupal\passbook\Form;

use Drupal\Core\Entity\EntityDeleteForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a form for passbook type deletion.
 */
class PassbookTypeDeleteConfirm extends EntityDeleteForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $count = $this->entityTypeManager->getStorage('passbook')->getQuery()
      ->condition('type', $this->entity->id())
      ->count()
      ->execute();
    if ($count) {
      $caption = '<p>' . $this->formatPlural($count, '%type is used by 1 piece of content on your site. You can not remove this passbook type until you have removed all of the %type content.', '%type is used by @count pieces of content on your site. You may not remove %type until you have removed all of the %type content.', ['%type' => $this->entity->label()]) . '</p>';
      $form['#title'] = $this->getQuestion();
      $form['description'] = ['#markup' => $caption];
      return $form;
    }

    return parent::buildForm($form, $form_state);
  }

}
