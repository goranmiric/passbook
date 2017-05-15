<?php

namespace Drupal\passbook\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\RevisionableEntityBundleInterface;

/**
 * Provides an interface.
 */
interface PassbookTypeInterface extends ConfigEntityInterface, RevisionableEntityBundleInterface {

  /**
   * Gets the pass type.
   *
   * @return string
   *   The pass type.
   */
  public function passType();

  /**
   * Gets the background color.
   *
   * @return string
   *   Color code.
   */
  public function backgroundColor();

  /**
   * Gets the foreground color.
   *
   * @return string
   *   Color code.
   */
  public function foregroundColor();

  /**
   * Gets the label color.
   *
   * @return string
   *   Color code.
   */
  public function labelColor();

  /**
   * Determines whether the bundle is locked.
   *
   * @return string|false
   *   The module name that locks the type or FALSE.
   */
  public function isLocked();

  /**
   * Gets whether a new revision should be created by default.
   *
   * @return bool
   *   TRUE if a new revision should be created by default.
   */
  public function isNewRevision();

  /**
   * Sets whether a new revision should be created by default.
   *
   * @param bool $new_revision
   *   TRUE if a new revision should be created by default.
   */
  public function setNewRevision($new_revision);

  /**
   * Gets whether 'Submitted by' information should be shown.
   *
   * @return bool
   *   TRUE if the submitted by information should be shown.
   */
  public function displaySubmitted();

  /**
   * Sets whether 'Submitted by' information should be shown.
   *
   * @param bool $display_submitted
   *   TRUE if the submitted by information should be shown.
   */
  public function setDisplaySubmitted($display_submitted);

  /**
   * Gets the preview mode.
   *
   * @return int
   *   DRUPAL_DISABLED, DRUPAL_OPTIONAL or DRUPAL_REQUIRED.
   */
  public function getPreviewMode();

  /**
   * Sets the preview mode.
   *
   * @param int $preview_mode
   *   DRUPAL_DISABLED, DRUPAL_OPTIONAL or DRUPAL_REQUIRED.
   */
  public function setPreviewMode($preview_mode);

  /**
   * Gets the description.
   *
   * @return string
   *   The description of this type.
   */
  public function getDescription();

}
