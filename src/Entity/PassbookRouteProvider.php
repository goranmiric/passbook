<?php

namespace Drupal\passbook\Entity;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Routing\EntityRouteProviderInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Provides routes.
 */
class PassbookRouteProvider implements EntityRouteProviderInterface {

  /**
   * {@inheritdoc}
   */
  public function getRoutes(EntityTypeInterface $entity_type) {
    $route_collection = new RouteCollection();

    $route = (new Route('/admin/content/passbook'))
      ->setDefaults([
        '_entity_list' => 'passbook',
        '_title' => 'Passbook',
      ])
      ->setRequirement('_permission', 'access passbook overview')
      ->setOption('_admin_route', TRUE);
    $route_collection->add('entity.passbook.collection', $route);

    $route = (new Route('/passbook/{passbook}'))
      ->addDefaults([
        '_controller' => '\Drupal\passbook\Controller\PassbookViewController::view',
        '_title_callback' => '\Drupal\passbook\Controller\PassbookViewController::title',
      ])
      ->setRequirement('passbook', '\d+')
      ->setRequirement('_entity_access', 'passbook.view');
    $route_collection->add('entity.passbook.canonical', $route);

    $route = (new Route('/passbook/{passbook}/delete'))
      ->addDefaults([
        '_entity_form' => 'passbook.delete',
        '_title' => 'Delete',
      ])
      ->setRequirement('passbook', '\d+')
      ->setRequirement('_entity_access', 'passbook.delete')
      ->setOption('_passbook_operation_route', TRUE);
    $route_collection->add('entity.passbook.delete_form', $route);

    $route = (new Route('/passbook/{passbook}/edit'))
      ->setDefault('_entity_form', 'passbook.edit')
      ->setRequirement('_entity_access', 'passbook.update')
      ->setRequirement('passbook', '\d+')
      ->setOption('_passbook_operation_route', TRUE);
    $route_collection->add('entity.passbook.edit_form', $route);

    return $route_collection;
  }

}
