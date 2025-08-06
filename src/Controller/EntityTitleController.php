<?php

namespace Drupal\node_lock\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Controller class for product attribute titles.
 */
class EntityTitleController extends ControllerBase {

  use StringTranslationTrait;

  /**
   * The collection page title.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   *   The collection page title.
   */
  public function getTitleNodeLockList(): TranslatableMarkup {
    return $this->t('Lock list');
  }

}
