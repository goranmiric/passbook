<?php

namespace Drupal\passbook\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityPublishedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\user\UserInterface;

/**
 * Defines the entity class.
 *
 * @ContentEntityType(
 *   id = "passbook",
 *   label = @Translation("Passbook"),
 *   label_collection = @Translation("Passbook"),
 *   label_singular = @Translation("passbook item"),
 *   label_plural = @Translation("passbook items"),
 *   label_count = @PluralTranslation(
 *     singular = "@count passbook item",
 *     plural = "@count passbook items"
 *   ),
 *   bundle_label = @Translation("Passbook type"),
 *   handlers = {
 *     "storage" = "Drupal\passbook\PassbookStorage",
 *     "view_builder" = "Drupal\passbook\PassbookViewBuilder",
 *     "access" = "Drupal\passbook\PassbookAccessControlHandler",
 *     "views_data" = "Drupal\passbook\PassbookViewsData",
 *     "form" = {
 *       "default" = "Drupal\passbook\Form\PassbookForm",
 *       "delete" = "Drupal\passbook\Form\PassbookDeleteForm",
 *       "edit" = "Drupal\passbook\Form\PassbookForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\passbook\Entity\PassbookRouteProvider",
 *     },
 *     "list_builder" = "Drupal\passbook\PassbookListBuilder",
 *   },
 *   base_table = "passbook",
 *   data_table = "passbook_field_data",
 *   revision_table = "passbook_revision",
 *   revision_data_table = "passbook_field_revision",
 *   show_revision_ui = TRUE,
 *   translatable = TRUE,
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "vid",
 *     "bundle" = "type",
 *     "pass_type" = "pass_type",
 *     "label" = "title",
 *     "langcode" = "langcode",
 *     "uuid" = "uuid",
 *     "status" = "status",
 *     "published" = "status",
 *     "uid" = "uid",
 *   },
 *   bundle_entity_type = "passbook_type",
 *   field_ui_base_route = "entity.passbook_type.edit_form",
 *   common_reference_target = TRUE,
 *   permission_granularity = "bundle",
 *   links = {
 *     "canonical" = "/passbook/{passbook}",
 *     "delete-form" = "/passbook/{passbook}/delete",
 *     "edit-form" = "/passbook/{passbook}/edit",
 *     "collection" = "/admin/content/passbook",
 *     "version-history" = "/passbook/{passbook}/revisions",
 *     "revision" = "/passbook/{passbook}/revisions/{passbook_revision}/view",
 *   }
 * )
 */
class Passbook extends ContentEntityBase implements PassbookInterface {

  use EntityChangedTrait;
  use EntityPublishedTrait;

  /**
   * Whether the entity is being previewed or not.
   *
   * @var true|null
   *   TRUE if the entity is being previewed and NULL if it is not.
   */
  public $inPreview = NULL;

  /**
   * {@inheritdoc}
   */
  public function getType() {
    return $this->bundle();
  }

  /**
   * {@inheritdoc}
   */
  public function getPassType() {
    return $this->getEntityKey('pass_type');
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    return $this->get('title')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setTitle($title) {
    $this->set('title', $title);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('uid')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->getEntityKey('uid');
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('uid', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('uid', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getRevisionCreationTime() {
    return $this->get('revision_timestamp')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setRevisionCreationTime($timestamp) {
    $this->set('revision_timestamp', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getRevisionAuthor() {
    return $this->getRevisionUser();
  }

  /**
   * {@inheritdoc}
   */
  public function getRevisionUser() {
    return $this->get('revision_uid')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function setRevisionAuthorId($uid) {
    $this->setRevisionUserId($uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setRevisionUser(UserInterface $user) {
    $this->set('revision_uid', $user);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getRevisionUserId() {
    return $this->get('revision_uid')->entity->id();
  }

  /**
   * {@inheritdoc}
   */
  public function setRevisionUserId($user_id) {
    $this->set('revision_uid', $user_id);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getRevisionLogMessage() {
    return $this->get('revision_log')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setRevisionLogMessage($revision_log_message) {
    $this->set('revision_log', $revision_log_message);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields += static::publishedBaseFieldDefinitions($entity_type);

    // Pass type field.
    $fields['pass_type'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Pass type'))
      ->setRequired(TRUE)
      ->setReadOnly(TRUE);

    // Pass type field.
    $fields['pass_file_path'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Pass file path'))
      ->setRequired(TRUE)
      ->setReadOnly(TRUE);

    $fields['title'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Title'))
      ->setRequired(TRUE)
      ->setTranslatable(TRUE)
      ->setRevisionable(TRUE)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The username of the content author.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setDefaultValueCallback('Drupal\passbook\Entity\Passbook::getCurrentUserId')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Authored on'))
      ->setDescription(t('The time that the entity was created.'))
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'timestamp',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'datetime_timestamp',
        'weight' => 10,
      ])
      ->setDisplayConfigurable('form', TRUE);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'))
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE);

    $fields['revision_timestamp'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Revision timestamp'))
      ->setDescription(t('The time that the current revision was created.'))
      ->setQueryable(FALSE)
      ->setRevisionable(TRUE);

    $fields['revision_uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Revision user ID'))
      ->setDescription(t('The user ID of the author of the current revision.'))
      ->setSetting('target_type', 'user')
      ->setQueryable(FALSE)
      ->setRevisionable(TRUE);

    $fields['revision_log'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Revision log message'))
      ->setDescription(t('Briefly describe the changes you have made.'))
      ->setRevisionable(TRUE)
      ->setDefaultValue('')
      ->setDisplayOptions('form', [
        'type' => 'string_textarea',
        'weight' => 25,
        'settings' => [
          'rows' => 4,
        ],
      ]);

    $fields['revision_translation_affected'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Revision translation affected'))
      ->setDescription(t('Indicates if the last edit of a translation belongs to current revision.'))
      ->setReadOnly(TRUE)
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE);

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);

    // TODO: add file property and create field.
    // Initialize service.
    $passbookManager = \Drupal::service('passbook.manager');
    $path = $passbookManager->buildPassFromEntity($this);
    $this->set('pass_file_path', $path);
  }

  /**
   * Get passbook fields.
   *
   * @return array
   *   List of pass fields.
   */
  public function getPassbookFields() {
    $fields = array_keys($this->getFields());
    $baseFields = array_keys($this->baseFieldDefinitions($this->getEntityType()));
    $bundleFields = array_diff($fields, $baseFields);

    // Unset unneeded fields from result.
    return array_diff($bundleFields, ['default_langcode']);
  }

  /**
   * Default value callback for 'uid' base field definition.
   *
   * @see ::baseFieldDefinitions()
   *
   * @return array
   *   An array of default values.
   */
  public static function getCurrentUserId() {
    return [\Drupal::currentUser()->id()];
  }

}
