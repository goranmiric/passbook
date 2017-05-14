<?php

namespace Drupal\passbook\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'passbook_date_default' widget.
 */
class PassbookDefaultWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {

    $element['value'] = $element + [
      '#default_value' => isset($items[$delta]->value) ? $items[$delta]->value : NULL,
      '#weight' => 20,
    ];

    $element['#attached'] = [
      'library' => [
        'passbook/passbook.fields',
      ],
    ];

    $element['#prefix'] = '<div class="passbook-form-element">';
    $element['#suffix'] = '</div>';

    return $element;
  }

}
