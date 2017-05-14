<?php

namespace Drupal\passbook\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'passbook_barcode_default' widget.
 *
 * @FieldWidget(
 *   id = "passbook_barcode_default",
 *   label = @Translation("Passbook Barcode"),
 *   field_types = {
 *     "passbook_barcode"
 *   }
 * )
 */
class PassbookBarcodeDefaultWidget extends PassbookDefaultWidget {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {

    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    $element['value']['#type'] = 'textfield';

    return $element;
  }

}
