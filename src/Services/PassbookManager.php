<?php

namespace Drupal\passbook\Services;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystem;
use Drupal\Core\Entity\EntityInterface;
use Drupal\image\Entity\ImageStyle;
use Drupal\passbook\Entity\PassbookType;
use Passbook\Pass\Image;
use Passbook\PassFactory;
use Passbook\Pass\Field;
use Passbook\Pass\Barcode;
use Passbook\Pass\Structure;
use Passbook\Pass\Location;
/**
 * Service to interact with the passbook library.
 */
class PassbookManager {

  /**
   * A configuration object.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   */
  protected $entityTypeManager;

  /**
   * File system service.
   *
   * @var \Drupal\Core\File\FileSystem $fileSystem
   */
  protected $fileSystem;

  /**
   * {@inheritdoc}
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The configuration factory.
   */
  public function __construct(ConfigFactoryInterface $configFactory, EntityTypeManagerInterface $entityTypeManager, FileSystem $fileSystem) {
    $this->config = $configFactory->get('passbook.settings')->get();
    $this->entityTypeManager = $entityTypeManager;
    $this->fileSystem = $fileSystem;
  }

  /**
   * Build pass object from entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Entity object.
   *
   * @return string
   *   File path.
   */
  public function buildPassFromEntity(EntityInterface $entity) {
    $fields = $entity->getPassbookFields();
    $bundle = PassbookType::load($entity->bundle());

    // Get passbook entity view settings.
    $viewStorage = $this->entityTypeManager->getStorage('entity_view_display');
    $viewSettings = $viewStorage->load('passbook.' . $entity->bundle() . '.default')->get('content');

    $passTypeClass = '\Passbook\Type\\' . ucfirst($entity->getPassType());
    $pass = new $passTypeClass($entity->uuid(), $entity->label());

    $pass->setLogoText($bundle->label());
    $pass->setBackgroundColor($bundle->backgroundColor());
    $pass->setForegroundColor($bundle->foregroundColor());
    $pass->setLabelColor($bundle->labelColor());

    // Create pass structure.
    $structure = new Structure();

    foreach ($fields as $field) {
      $value = $entity->$field->getValue();
      $value = reset($value);
      // Skip if field is not added to default view mode
      // or if there is no value for this field.
      if (!isset($viewSettings[$field]) || !$value) {
        continue;
      }

      $definition = $entity->$field->getFieldDefinition();
      switch ($definition->getType()) {
        case 'passbook_text':
          $data = new Field($definition->getName(), $value['value']);
          if ($viewSettings[$field]['label'] != 'hidden') {
            $data->setLabel($definition->getLabel());
          }
          $callback = $definition->getSetting('callback');
          $structure->$callback($data);
          break;

        case 'passbook_date':
          $date = new \DateTime($value['value']);
          $format = $viewSettings[$field]['settings']['date_format'];

          $data = new Field($definition->getName(), $date->format($format));
          if ($viewSettings[$field]['label'] != 'hidden') {
            $data->setLabel($definition->getLabel());
          }
          $callback = $definition->getSetting('callback');
          $structure->$callback($data);

          if ($definition->getName() == 'expiration_date') {
            // TODO: Move this to the field config.
            $pass->setExpirationDate($date);
          }
          break;

        case 'passbook_barcode':
          $const = $definition->getSetting('callback');
          $barcode = new Barcode(constant("\Passbook\Pass\Barcode::$const"), $value['value']);
          $pass->setBarcode($barcode);
          break;

        case 'image':
          $file = $this->entityTypeManager->getStorage('file')->load($value['target_id']);

          // Add images.
          foreach (self::getImageContext() as $key => $context) {
            $imageStyle = 'passbook_' . $definition->getName() . $key;
            $imageContext = $definition->getName() . $context;

            // Get image style uri.
            $imageStyle = ImageStyle::load($imageStyle);
            $styleUri = $imageStyle->buildUri($file->getFileUri());

            // Create the image.
            $imageStyle->createDerivative($file->getFileUri(), $styleUri);
            $realPath = $this->fileSystem->realpath($styleUri);

            $image = new Image($realPath, $imageContext);
            $pass->addImage($image);
          }
          break;
      }
    }

    // Set pass structure.
    $pass->setStructure($structure);
    $path = $this->create($pass);

    return $path;
  }

  /**
   * Create pass factory instance.
   *
   * @param object $pass
   *   A passbook object.
   *
   * @return string
   *   Path to the file.
   */
  public function create($pass) {
    // Set api version.
    $pass->setFormatVersion(1);

    // Add icon image to the pass object.
    $this->setIcon($pass);

    // Set webservice.
    if (isset($this->config['web_service_url'])) {
      $pass->setAuthenticationToken(bin2hex(random_bytes(16)));
      $pass->setWebServiceURL($this->config['web_service_url']);
    }

    $factory = new PassFactory(
      $this->config['pass_type_identifier'],
      $this->config['team_identifier'],
      $this->config['organization_name'],
      $this->config['p12_file_path'],
      $this->config['p12_password'],
      $this->config['wwdr_file_path']
    );

    $factory->setOutputPath($this->fileSystem->realpath($this->config['output_path']));
    $out = $factory->package($pass);

    return $this->config['output_path'] . $out->getFilename();
  }

  /**
   * Image context definition for passbook.
   *
   * @return array
   *   Image style suffix : Image context suffix.
   */
  public static function getImageContext() {
    return [
      '' => '',
      '_2x' => '@2x'
    ];
  }

  /**
   * Add icon file to the passbook object.
   *
   * @param object $pass
   *   Passbook object.
   */
  public function setIcon(&$pass) {
    // Add icon image.
    $icon = new Image($this->config['icon_file'], 'icon');
    $pass->addImage($icon);

    $icon2x = new Image($this->config['icon2x_file'], 'icon@2x');
    $pass->addImage($icon2x);
  }

}
