<?php

namespace Drupal\passbook\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'passbook_text_default' formatter.
 *
 * @FieldFormatter(
 *   id = "passbook_text_default",
 *   label = @Translation("Passbook text default"),
 *   field_types = {
 *     "passbook_text"
 *   }
 * )
 */
class PassbookTextDefaultFormatter extends FormatterBase {

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
