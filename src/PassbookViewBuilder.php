<?php

namespace Drupal\passbook;

use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityViewBuilder;

/**
 * View builder handler.
 */
class PassbookViewBuilder extends EntityViewBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildComponents(array &$build, array $entities, array $displays, $view_mode) {
    /** @var \Drupal\passbook\Entity\PassbookInterface[] $entities */
    if (empty($entities)) {
      return;
    }

    parent::buildComponents($build, $entities, $displays, $view_mode);

    foreach ($entities as $id => $entity) {
      $bundle = $entity->bundle();
      $display = $displays[$bundle];

      // Add Language field text element to entity render array.
      if ($display->getComponent('langcode')) {
        $build[$id]['langcode'] = [
          '#type' => 'item',
          '#title' => t('Language'),
          '#markup' => $entity->language()->getName(),
          '#prefix' => '<div id="field-language-display">',
          '#suffix' => '</div>',
        ];
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function getBuildDefaults(EntityInterface $entity, $view_mode) {
    $defaults = parent::getBuildDefaults($entity, $view_mode);

    // Don't cache entities that are in 'preview' mode.
    if (isset($defaults['#cache']) && isset($entity->inPreview)) {
      unset($defaults['#cache']);
    }

    return $defaults;
  }

  /**
   * {@inheritdoc}
   */
  protected function alterBuild(array &$build, EntityInterface $entity, EntityViewDisplayInterface $display, $view_mode) {
    /** @var \Drupal\passbook\Entity\PassbookInterface $entity */
    parent::alterBuild($build, $entity, $display, $view_mode);
    if ($entity->id()) {
      if ($entity->isDefaultRevision()) {
        $build['#contextual_links']['passbook'] = [
          'route_parameters' => ['passbook' => $entity->id()],
          'metadata' => ['changed' => $entity->getChangedTime()],
        ];
      }
      else {
        $build['#contextual_links']['passbook_revision'] = [
          'route_parameters' => [
            'passbook' => $entity->id(),
            'passbook_revision' => $entity->getRevisionId(),
          ],
          'metadata' => ['changed' => $entity->getChangedTime()],
        ];
      }
    }
  }

}
