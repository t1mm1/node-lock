<?php

namespace Drupal\node_lock\Access;

use Drupal\Core\Access\AccessResultInterface;
use Drupal\node\NodeInterface;

/**
 * Interface of Access service.
 */
interface AccessInterface {

  /**
   * Get lock access.
   *
   * @param NodeInterface $node
   *   The entity that has lock or not.
   *
   * @return AccessResultInterface
   *   The lock access.
   */
  public function lock(NodeInterface $node): AccessResultInterface;

  /**
   * Get unlock access.
   *
   * @param NodeInterface $node
   *   The entity that has lock or not.
   *
   * @return AccessResultInterface
   *   The lock access.
   */
  public function unlock(NodeInterface $node): AccessResultInterface;

}
