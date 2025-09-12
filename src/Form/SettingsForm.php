<?php

namespace Drupal\node_lock\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\TypedConfigManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Node lock settings form.
 *
 * @package Drupal\node_lock\Form
 */
class SettingsForm extends ConfigFormBase {

  /**
   * The entity type.
   *
   * @var string
   */
  protected string $type = 'node';

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    TypedConfigManagerInterface $typed_config_manager,
    protected EntityTypeManagerInterface $entity_type_manager,
  ) {
    parent::__construct($config_factory, $typed_config_manager);
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('config.factory'),
      $container->get('config.typed'),
      $container->get('entity_type.manager'),
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['node_lock.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'node_lock_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('node_lock.settings');

    $form['general'] = [
      '#type' => 'details',
      '#title' => t('General'),
      '#open' => TRUE,
      '#weight' => -1,
    ];

    $form['general']['enabled'] = [
      '#type' => 'checkbox',
      '#title' => t('Enable'),
      '#description' => t('Enable lock/unlock service.'),
      '#default_value' => $config->get('enabled'),
      '#return_value' => 1,
      '#empty' => 0,
    ];

    $form['bundles'] = [
      '#type' => 'details',
      '#title' => $this->t('Bundles'),
      '#open' => TRUE,
      '#tree' => TRUE,
      '#weight' => 1,
    ];

    $definition = $this->entityTypeManager->getDefinition($this->type);
    if ($definition->getBundleEntityType()) {
      $bundles = $this->entityTypeManager
        ->getStorage($definition->getBundleEntityType())
        ->loadMultiple();

      $options = [];
      foreach ($bundles as $bundle) {
        $options[$bundle->id()] = $bundle->label();
      }

      $form['bundles'][$definition->id()] = [
        '#type' => 'checkboxes',
        '#title' => $this->t('Bundles'),
        '#description' => $this->t('Select the bundles.'),
        '#options' => $options,
        '#default_value' => $config->get('bundles') ?: [],
      ];
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $settings = $this->config('node_lock.settings');
    $settings->set('enabled', $form_state->getValue('enabled'));
    $settings->set('bundles', array_keys(array_filter($form_state->getValue(['bundles', 'node']))));
    $settings->save();
  }

}
