<?php

declare (strict_types = 1);

namespace Drupal\cmis;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;

/**
 * Cmis Permissions.
 *
 * @package Drupal\cmis
 */
class CmisPermissions implements ContainerInjectionInterface {
  use StringTranslationTrait;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a CmisPermissions instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * Returns an array of permissions.
   *
   * @return array
   *   The permissions.
   */
  public function accessCmisBrowserPermissions() {
    $permissions = [];
    $entity = $this->entityTypeManager->getStorage('cmis_connection_entity')->loadMultiple();
    foreach ($entity as $config_id => $value) {
      $type_params = ['%config_id' => $config_id];
      $permissions += [
        "access cmis browser $config_id" => [
          'title' => $this->t('Access cmis browser %config_id', $type_params),
        ],
      ];
    }
    return $permissions;
  }

}
