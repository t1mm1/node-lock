<?php

namespace Drupal\node_lock\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Render\Markup;
use Drupal\node\NodeInterface;
use Drupal\node_lock\Lock\LockInterface;

/**
 * Hook implementations for the Node Lock module.
 */
class Views {

  public function __construct(
    private LockInterface $lock,
  ) {
  }

  /**
   * Implements hook_preprocess_views_view_field().
   */
  #[Hook('preprocess_views_view_field')]
  public function entityDelete(&$variables): void {
    if ($variables['field']->field == 'title') {
      if (!empty($variables['row']->_entity) && $variables['row']->_entity instanceof NodeInterface) {
        $entity = $variables['row']->_entity;

        if ($this->lock->getLock($entity)) {
          $variables['#attached']['library'][] = 'node_lock/icon';
          $variables['output'] = Markup::create($variables['output'] . '<i class="icon-lock"></i>');
        }
      }

    }
  }

}
