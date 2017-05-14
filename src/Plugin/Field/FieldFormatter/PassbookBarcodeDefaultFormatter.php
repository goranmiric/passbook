<?php

namespace Drupal\passbook\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'passbook_barcode_default' formatter.
 *
 * @FieldFormatter(
 *   id = "passbook_barcode_default",
 *   label = @Translation("Passbook barcode default"),
 *   field_types = {
 *     "passbook_barcode"
 *   }
 * )
 */
class PassbookBarcodeDefaultFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      // Render each element as link.
      $elements[$delta] = [
        '#markup' => $item->value,
      ];
    }

    return $elements;
  }

}
