<?php

namespace Drupal\node_lock\Hook;

use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hook implementations for the Node Lock module.
 */
class Theme {

  /**
   * Implements hook_theme().
   */
  #[Hook('theme')]
  public function theme($existing, $type, $theme, $path): array {
    return [
      'node_lock_icon' => [
        'variables' => [
          'output' => NULL,
          'title' => NULL,
        ],
      ],
    ];
  }

}
