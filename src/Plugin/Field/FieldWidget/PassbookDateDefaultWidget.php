<?php

namespace Drupal\passbook\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'passbook_date_default' widget.
 *
 * @FieldWidget(
 *   id = "passbook_date_default",
 *   label = @Translation("Passbook Date"),
 *   field_types = {
 *     "passbook_date"
 *   }
 * )
 */
class PassbookDateDefaultWidget extends PassbookDefaultWidget {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {

    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    $element['value']['#type'] = 'date';

    return $element;
  }

}
