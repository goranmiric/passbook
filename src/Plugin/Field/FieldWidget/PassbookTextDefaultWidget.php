<?php

namespace Drupal\passbook\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'passbook_text_default' widget.
 *
 * @FieldWidget(
 *   id = "passbook_text_default",
 *   label = @Translation("Passbook Text default"),
 *   field_types = {
 *     "passbook_text"
 *   }
 * )
 */
class PassbookTextDefaultWidget extends PassbookDefaultWidget {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {

    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    $element['value']['#type'] = 'textfield';

    return $element;
  }

}
