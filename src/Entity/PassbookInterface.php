<?php

namespace Drupal\passbook\Entity;

use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Provides an interface defining a entity.
 */
interface PassbookInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface, RevisionLogInterface, EntityPublishedInterface {

  /**
   * Denotes that the entity is not published.
   */
  const NOT_PUBLISHED = 0;

  /**
   * Denotes that the entity is published.
   */
  const PUBLISHED = 1;

  /**
   * Gets the entity type.
   *
   * @return string
   *   The entity type.
   */
  public function getType();

  /**
   * Gets the pass type (apple).
   *
   * @return string
   *   The pass type.
   */
  public function getPassType();

  /**
   * Gets the entity title.
   *
   * @return string
   *   Title of the entity.
   */
  public function getTitle();

  /**
   * Sets the entity title.
   *
   * @param string $title
   *   The entity title.
   *
   * @return \Drupal\passbook\Entity\PassbookInterface
   *   The called entity.
   */
  public function setTitle($title);

  /**
   * Gets the entity creation timestamp.
   *
   * @return int
   *   Creation timestamp of the entity.
   */
  public function getCreatedTime();

  /**
   * Sets the entity creation timestamp.
   *
   * @param int $timestamp
   *   The entity creation timestamp.
   *
   * @return \Drupal\passbook\Entity\PassbookInterface
   *   The called entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Gets the entity revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the entity revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\passbook\Entity\PassbookInterface
   *   The called entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the entity revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   *
   * @deprecated in Drupal 8.2.0, will be removed before Drupal 9.0.0. Use
   *   \Drupal\Core\Entity\RevisionLogInterface::getRevisionUser() instead.
   */
  public function getRevisionAuthor();

  /**
   * Sets the entity revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\passbook\Entity\PassbookInterface
   *   The called entity.
   *
   * @deprecated in Drupal 8.2.0, will be removed before Drupal 9.0.0. Use
   *   \Drupal\Core\Entity\RevisionLogInterface::setRevisionUserId() instead.
   */
  public function setRevisionAuthorId($uid);

}
