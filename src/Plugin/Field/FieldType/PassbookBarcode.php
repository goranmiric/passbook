<?php

namespace Drupal\passbook\Plugin\Field\FieldType;

use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'passbook_text' field.
 *
 * @FieldType(
 *   id = "passbook_barcode",
 *   label = @Translation("Passbook barcode"),
 *   description = @Translation("Passbook barcode field."),
 *   category = @Translation("Passbook"),
 *   default_widget = "passbook_barcode_default",
 *   default_formatter = "passbook_barcode_default"
 * )
 */
class PassbookBarcode extends PassbookItemBase {
  /**
   * {@inheritdoc}
   */
  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {
    $element = [];
    $settings = $this->getSettings();

    $element['callback'] = [
      '#title' => $this->t('Barcode type'),
      '#type' => 'select',
      '#options' => [
        '' => $this->t('- None -'),
        'TYPE_QR' => $this->t('PKBarcodeFormatQR'),
        'TYPE_PDF_417' => $this->t('PKBarcodeFormatPDF417'),
        'TYPE_AZTEC' => $this->t('PKBarcodeFormatAztec'),
        'TYPE_CODE_128' => $this->t('PKBarcodeFormatCode128'),
      ],
      '#default_value' => $settings['callback'],
      '#description' => $this->t('Select barcode type.'),
      '#required' => TRUE,
    ];

    return $element;
  }
}
