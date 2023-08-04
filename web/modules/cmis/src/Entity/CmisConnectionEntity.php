<?php

declare(strict_types = 1);

namespace Drupal\cmis\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the CMIS connection entity.
 *
 * @ConfigEntityType(
 *   id = "cmis_connection_entity",
 *   label = @Translation("CMIS connection"),
 *   handlers = {
 *     "list_builder" = "Drupal\cmis\CmisConnectionEntityListBuilder",
 *     "form" = {
 *       "add" = "Drupal\cmis\Form\CmisConnectionEntityForm",
 *       "edit" = "Drupal\cmis\Form\CmisConnectionEntityForm",
 *       "delete" = "Drupal\cmis\Form\CmisConnectionEntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "cmis_connection_entity",
 *   config_export = {
 *     "id",
 *     "label",
 *     "uuid",
 *     "cmis_url",
 *     "cmis_user",
 *     "cmis_password",
 *     "cmis_repository",
 *     "cmis_root_folder",
 *     "cmis_cacheable"
 *   },
 *   admin_permission = "administer cmis connection entity",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *   },
 *   links = {
 *     "canonical" = "/admin/config/cmis/connection/cmis_connection_entity/{cmis_connection_entity}",
 *     "add-form" = "/admin/config/cmis/connection/cmis_connection_entity/add",
 *     "edit-form" = "/admin/config/cmis/connection/cmis_connection_entity/{cmis_connection_entity}/edit",
 *     "delete-form" = "/admin/config/cmis/connection/cmis_connection_entity/{cmis_connection_entity}/delete",
 *     "collection" = "/admin/config/cmis/connection/cmis_connection_entity"
 *   }
 * )
 */
class CmisConnectionEntity extends ConfigEntityBase implements CmisConnectionEntityInterface {

  /**
   * The CMIS connection ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The CMIS connection label.
   *
   * @var string
   */
  protected $label;

  /**
   * The CMIS connection url.
   *
   * @var string
   */
  protected $cmis_url;

  /**
   * The CMIS connection user.
   *
   * @var string
   */
  protected $cmis_user;

  /**
   * The CMIS connection password.
   *
   * @var string
   */
  protected $cmis_password;

  /**
   * The CMIS connection repository id.
   *
   * @var string
   */
  protected $cmis_repository;

  /**
   * The CMIS connection root folder id.
   *
   * @var string
   */
  protected $cmis_root_folder;

  /**
   * The CMIS repository cacheable flag.
   *
   * @var bool
   */
  protected $cmis_cacheable;

  /**
   * {@inheritdoc}
   */
  public function getCmisUrl() {
    return $this->cmis_url;
  }

  /**
   * {@inheritdoc}
   */
  public function getCmisUser() {
    return $this->cmis_user;
  }

  /**
   * {@inheritdoc}
   */
  public function getCmisPassword() {
    return $this->cmis_password;
  }

  /**
   * {@inheritdoc}
   */
  public function getCmisRepository() {
    return $this->cmis_repository;
  }

  /**
   * {@inheritdoc}
   */
  public function getCmisRootFolder() {
    return $this->cmis_root_folder;
  }

  /**
   * {@inheritdoc}
   */
  public function getCmisCacheable() {
    return $this->cmis_cacheable;
  }

}
