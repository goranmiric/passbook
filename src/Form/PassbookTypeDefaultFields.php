<?php

namespace Drupal\passbook\Form;

use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\passbook\Entity\PassbookTypeInterface;

/**
 * Provide defaults fields for passbook types.
 */
class PassbookTypeDefaultFields {
  use StringTranslationTrait;

  /**
   * Define default fields widget.
   */
  public function getDefaultFieldWidget($fieldName) {
    $widgets = [
      'image' => 'image_image',
      'passbook_date' => 'passbook_date_default',
      'passbook_text' => 'passbook_text_default',
      'passbook_barcode' => 'passbook_barcode_default',
    ];

    return $widgets[$fieldName];
  }

  /**
   * Define default fields widget.
   */
  public function getDefaultViewType($fieldName) {
    $widgets = [
      'thumbnail' => 'image_url',
      'thumbnail_2x' => 'image_url',
      'expiration_date' => 'passbook_date_default',
      'member_name' => 'passbook_text_default',
      'barcode' => 'passbook_barcode_default',
    ];

    return $widgets[$fieldName];
  }

  /**
   * Define default fields per pass type.
   */
  public function getDefaultFields($passType) {
    $fields = [];

    switch ($passType) {
      case 'generic':
        $fields += [
          'thumbnail' => [
            'label' => $this->t('Thumbnail'),
            'description' => $this->t('The image will be converted to 90x90 and 180x180.'),
            'settings' => [
              'file_directory' => 'passbook-thumbnail',
              'file_extensions' => 'png',
              'alt_field' => FALSE,
              'alt_field_required' => FALSE,
              'title_field' => FALSE,
              'title_field_required' => FALSE,
            ],
          ],
          'member_name' => [
            'label' => $this->t('Member name'),
            'settings' => [
              'callback' => 'addPrimaryField'
            ],
          ],
          'expiration_date' => [
            'label' => $this->t('Expiration date'),
            'settings' => [
              'callback' => 'addAuxiliaryField'
            ],
          ],
          'barcode' => [
            'label' => $this->t('Barcode'),
            'settings' => [
              'callback' => 'TYPE_AZTEC'
            ],
          ],
        ];
        break;
    }

    return $fields;
  }

  /**
   * Adds the default fields.
   *
   * @param \Drupal\passbook\Entity\PassbookTypeInterface $type
   *   A type object.
   */
  public function addDefaultFields(PassbookTypeInterface $type) {
    $fields = $this->getDefaultFields($type->passType());

    foreach ($fields as $fieldName => $fieldData) {
      $storage = FieldStorageConfig::loadByName('passbook', $fieldName);
      $field = FieldConfig::loadByName('passbook', $type->id(), $fieldName);

      if (empty($field)) {
        $fieldData += [
          'field_storage' => $storage,
          'bundle' => $type->id(),
        ];

        $field = FieldConfig::create($fieldData);
        $field->save();

        // Assign widget settings for the 'default' form mode.
        $entityFormDisplay = EntityFormDisplay::load('passbook.' . $type->id() . '.default');
        if (!$entityFormDisplay) {
          $entityFormDisplay = EntityFormDisplay::create([
            'targetEntityType' => 'passbook',
            'bundle' => $type->id(),
            'mode' => 'default',
            'status' => TRUE,
          ]);
        }

        $entityFormDisplay
          ->setComponent($fieldName, ['type' => $this->getDefaultFieldWidget($storage->getType())])
          ->save();

        // Assign display settings for the 'default' view mode.
        $display = EntityViewDisplay::load('passbook.' . $type->id() . '.default');
        if (!$display) {
          $display = EntityViewDisplay::create([
            'targetEntityType' => 'passbook',
            'bundle' => $type->id(),
            'mode' => 'default',
            'status' => TRUE,
          ]);
        }

        $display->setComponent($fieldName, [
          'label' => 'above',
          'type' => $this->getDefaultViewType($fieldName),
        ])->save();

      }
    }
  }

}
