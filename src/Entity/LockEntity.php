<?php

namespace Drupal\node_lock\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\user\EntityOwnerInterface;
use Drupal\user\EntityOwnerTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * The property entity.
 *
 * @ContentEntityType(
 *   id = "node_lock",
 *   label = @Translation("Node lock"),
 *   label_collection = @Translation("Locks list"),
 *   base_table = "node_lock",
 *   data_table = "node_lock_field_data",
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid",
 *     "langcode" = "langcode",
 *   },
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\node_lock\ListBuilder\NodeLockListBuilder",
 *     "form" = {
 *       "delete" = "Drupal\node_lock\Form\LockDeleteForm",
 *     },
 *     "access" = "Drupal\Core\Entity\EntityAccessControlHandler",
 *     "route_provider" = {
 *        "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *      },
 *   },
 *   links = {
 *     "delete-form" = "/admin/structure/node-lock/{node_lock}/delete",
 *     "collection" = "/admin/structure/node-lock",
 *   },
 *   admin_permission = "administer content",
 *   translatable = TRUE,
 * )
 */
class LockEntity extends ContentEntityBase implements EntityOwnerInterface {

  use EntityOwnerTrait;

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = [];

    $fields[$entity_type->getKey('id')] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('Entity ID.'));

    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The UUID.'))
      ->setReadOnly(TRUE);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Author'))
      ->setDescription(t('The author of the entity.'))
      ->setSetting('target_type', 'user')
      ->setDefaultValueCallback(static::class . '::getCurrentUserId');

    $fields['langcode'] = BaseFieldDefinition::create('language')
      ->setLabel(t('Language code'))
      ->setDescription(t('The language code of the entity.'))
      ->setDisplayOptions('form', [
        'type' => 'language_select',
        'weight' => 100,
      ]);

    $fields['parent'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Node'))
      ->setDescription(t('The parent node entity.'))
      ->setSetting('target_type', 'node')
      ->setSetting('handler', 'default')
      ->setRequired(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'hidden',
        'weight' => -5,
      ]);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The created datetime.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The changed datetime.'));

    return $fields;
  }

  /**
   * Returns the current user id for default value callbacks.
   */
  public static function getCurrentUserId(): array {
    return [\Drupal::currentUser()->id()];
  }

  /**
   * Return user entity.
   *
   * @return UserInterface|null
   *   User entity.
   */
  public function getUser(): ?UserInterface {
    $user = $this->get('uid')->entity;
    return ($user instanceof UserInterface) ? $user : NULL;
  }

  /**
   * Gets the creation timestamp.
   *
   * @return int
   *   Creation timestamp of the entity.
   */
  public function getCreatedTime(): int {
    return (int) $this->get('created')->value;
  }

}
