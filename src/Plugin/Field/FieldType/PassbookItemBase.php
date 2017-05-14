<?php

namespace Drupal\passbook\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Base class for 'text' configurable field types.
 */
class PassbookItemBase extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultFieldSettings() {
    return [
      'callback' => NULL,
    ] + parent::defaultFieldSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {
    $element = [];
    $settings = $this->getSettings();

    $element['callback'] = [
      '#title' => $this->t('Passbook field type'),
      '#type' => 'select',
      '#options' => [
        '' => $this->t('- None -'),
        'addHeaderField' => $this->t('Header'),
        'addPrimaryField' => $this->t('Primary'),
        'addSecondaryField' => $this->t('Secondary'),
        'addAuxiliaryField' => $this->t('Auxiliary'),
        'addBackField' => $this->t('Back'),
      ],
      '#default_value' => $settings['callback'],
      '#description' => $this->t('Select passbook field type.'),
      '#required' => TRUE,
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field) {

    return [
      'columns' => [
        'value' => [
          'type' => 'varchar',
          'length' => 700,
          'not null' => FALSE,
        ],
      ],
    ];

  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $value = $this->get('value')->getValue();
    return $value == NULL;
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {

    $properties['value'] = DataDefinition::create('string');

    return $properties;
  }

}
