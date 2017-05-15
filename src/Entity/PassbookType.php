<?php

namespace Drupal\passbook\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\Core\Entity\EntityStorageInterface;

/**
 * Defines the passbook type configuration entity.
 *
 * @ConfigEntityType(
 *   id = "passbook_type",
 *   label = @Translation("Passbook type"),
 *   handlers = {
 *     "access" = "Drupal\passbook\PassbookTypeAccessControlHandler",
 *     "form" = {
 *       "add" = "Drupal\passbook\Form\PassbookTypeForm",
 *       "edit" = "Drupal\passbook\Form\PassbookTypeForm",
 *       "delete" = "Drupal\passbook\Form\PassbookTypeDeleteConfirm"
 *     },
 *     "list_builder" = "Drupal\passbook\PassbookTypeListBuilder",
 *   },
 *   admin_permission = "administer passbook types",
 *   config_prefix = "type",
 *   bundle_of = "passbook",
 *   entity_keys = {
 *     "id" = "id",
 *     "pass_type" = "pass_type",
 *     "label" = "label"
 *   },
 *   links = {
 *     "edit-form" = "/admin/structure/passbook/manage/{passbook_type}",
 *     "delete-form" = "/admin/structure/passbook/manage/{passbook_type}/delete",
 *     "collection" = "/admin/structure/passbook",
 *   },
 *   config_export = {
 *     "label",
 *     "id",
 *     "pass_type",
 *     "background_color",
 *     "foreground_color",
 *     "label_color",
 *     "description",
 *     "new_revision",
 *     "preview_mode",
 *     "display_submitted",
 *   }
 * )
 */
class PassbookType extends ConfigEntityBundleBase implements PassbookTypeInterface {

  /**
   * The machine name of this passbook type.
   *
   * @var string
   */
  protected $id;

  /**
   * The human-readable name of the passbook type.
   *
   * @var string
   */
  protected $label;

  /**
   * The apple passbook type.
   *
   * @var string
   */
  protected $pass_type;

  /**
   * The background color.
   *
   * @var string
   */
  protected $background_color;

  /**
   * The foreground color.
   *
   * @var string
   */
  protected $foreground_color;

  /**
   * The label color.
   *
   * @var string
   */
  protected $label_color;

  /**
   * A brief description of this passbook type.
   *
   * @var string
   */
  protected $description;

  /**
   * Default value of the 'Create new revision' checkbox of this passbook type.
   *
   * @var bool
   */
  protected $new_revision = TRUE;

  /**
   * The preview mode.
   *
   * @var int
   */
  protected $preview_mode = DRUPAL_OPTIONAL;

  /**
   * Display setting for author and date Submitted by post information.
   *
   * @var bool
   */
  protected $display_submitted = TRUE;

  /**
   * {@inheritdoc}
   */
  public function id() {
    return $this->id;
  }

  /**
   * {@inheritdoc}
   */
  public function passType() {
    return $this->pass_type;
  }

  /**
   * {@inheritdoc}
   */
  public function backgroundColor() {
    return $this->background_color;
  }

  /**
   * {@inheritdoc}
   */
  public function foregroundColor() {
    return $this->foreground_color;
  }

  /**
   * {@inheritdoc}
   */
  public function labelColor() {
    return $this->label_color;
  }

  /**
   * {@inheritdoc}
   */
  public function isLocked() {
    $locked = \Drupal::state()->get('passbook.type.locked');
    return isset($locked[$this->id()]) ? $locked[$this->id()] : FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function isNewRevision() {
    return $this->new_revision;
  }

  /**
   * {@inheritdoc}
   */
  public function shouldCreateNewRevision() {
    return $this->isNewRevision();
  }

  /**
   * {@inheritdoc}
   */
  public function setNewRevision($new_revision) {
    $this->new_revision = $new_revision;
  }

  /**
   * {@inheritdoc}
   */
  public function displaySubmitted() {
    return $this->display_submitted;
  }

  /**
   * {@inheritdoc}
   */
  public function setDisplaySubmitted($display_submitted) {
    $this->display_submitted = $display_submitted;
  }

  /**
   * {@inheritdoc}
   */
  public function getPreviewMode() {
    return $this->preview_mode;
  }

  /**
   * {@inheritdoc}
   */
  public function setPreviewMode($preview_mode) {
    $this->preview_mode = $preview_mode;
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->description;
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);

    if ($update && $this->getOriginalId() != $this->id()) {
      $update_count = \Drupal::entityTypeManager()->getStorage('passbook')->updateType($this->getOriginalId(), $this->id());
      if ($update_count) {
        drupal_set_message(\Drupal::translation()->formatPlural($update_count,
          'Changed the passbook type of 1 post from %old-type to %type.',
          'Changed the passbook type of @count posts from %old-type to %type.',
          [
            '%old-type' => $this->getOriginalId(),
            '%type' => $this->id(),
          ]));
      }
    }
    if ($update) {
      // Clear the cached field definitions as some settings affect the field
      // definitions.
      $this->entityManager()->clearCachedFieldDefinitions();
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function postDelete(EntityStorageInterface $storage, array $entities) {
    parent::postDelete($storage, $entities);

    // Clear the passbook type cache to reflect the removal.
    $storage->resetCache(array_keys($entities));
  }

}
