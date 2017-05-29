<?php

namespace Drupal\passbook\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'passbook_date_default' formatter.
 *
 * @FieldFormatter(
 *   id = "passbook_date_default",
 *   label = @Translation("Passbook Date"),
 *   field_types = {
 *     "passbook_date"
 *   }
 * )
 */
class PassbookDateDefaultFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'date_format' => 'm/d/Y',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element['date_format'] = [
      '#title' => t('Date format'),
      '#type' => 'textfield',
      '#default_value' => $this->getSetting('date_format'),
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $format = $this->getSetting('date_format');

    return [$format];
  }

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
