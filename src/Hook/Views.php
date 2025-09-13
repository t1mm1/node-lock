<?php

namespace Drupal\node_lock\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Render\Markup;
use Drupal\Core\Render\RendererInterface;
use Drupal\node\NodeInterface;
use Drupal\node_lock\Lock\LockInterface;
use Drupal\node_lock\NodeLockHelper;

/**
 * Hook implementations for the Node Lock module.
 */
class Views {

  public function __construct(
    private RendererInterface $renderer,
    private NodeLockHelper $nodeLockHelper,
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
        $node = $variables['row']->_entity;
        $lock = $this->lock->getLock($node);
        if ($lock) {
          $is_owner = $this->lock->isOwner($node);
          $content = [
            '#theme' => 'node_lock_icon',
            '#output' => $variables['output'],
            '#title' => Markup::create($is_owner ?
              $this->nodeLockHelper->getMessageAsOwner($lock, TRUE) :
              $this->nodeLockHelper->getMessageAsUser($lock, TRUE)
            ),
            '#cache' => [
              'tags' => $node->getCacheTags(),
              'max-age' => $node->getCacheMaxAge(),
            ],
          ];

          $variables['#attached']['library'][] = 'node_lock/icon';
          $variables['output'] = $this->renderer->render($content);
        }
      }

    }
  }

}
