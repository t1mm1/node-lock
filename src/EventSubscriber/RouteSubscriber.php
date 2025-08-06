<?php

namespace Drupal\node_lock\EventSubscriber;

use Symfony\Component\Routing\RouteCollection;
use Drupal\Core\Routing\RouteSubscriberBase;

/**
 * RouteSubscriber class for entity routes.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    if ($route = $collection->get('entity.node_lock.collection')) {
      $route->setDefault(
        '_title_callback',
        '\Drupal\node_lock\Controller\EntityTitleController::getTitleNodeLockList'
      );
    }
  }

}
