<?php

declare(strict_types = 1);

namespace Drupal\cmis\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface for defining CMIS connection entities.
 */
interface CmisConnectionEntityInterface extends ConfigEntityInterface {

  /**
   * Get CMIS url.
   *
   * @return string
   *   Return CMIS Url.
   */
  public function getCmisUrl();

  /**
   * Get CMIS user name.
   *
   * @return string
   *   Return CMIS Username.
   */
  public function getCmisUser();

  /**
   * Get CMIS password.
   *
   * @return string
   *   return CMIS password.
   */
  public function getCmisPassword();

  /**
   * Get CMIS repository id.
   *
   * @return string
   *   Return CMIS repository id.
   */
  public function getCmisRepository();

  /**
   * Get CMIS root folder id.
   *
   * @return string
   *   Return CMIS root folder id.
   */
  public function getCmisRootFolder();

  /**
   * Get CMIS repository cacheable flag.
   *
   * @return bool
   *   Return CMIS cacheable flag.
   */
  public function getCmisCacheable();

}
