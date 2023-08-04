<?php

declare(strict_types = 1);

namespace Drupal\cmis\Service;

use Drupal\cmis\CmisConnectionApi;
use Drupal\cmis\Controller\CmisRepositoryController;

/**
 * Provides a lazy builder for field browser formatter.
 */
class CmisFieldBrowserBuilder {

  /**
   * Lazy builder callback for displaying a browser formatter.
   *
   * @param string $path
   *   The CMIS path.
   * @param string $show_breadcrumb
   *   Check if show breadcrumb.
   *
   * @return array
   *   A render array for the browser formatter.
   */
  public function build($path, $show_breadcrumb) {
    // Get config_id.
    $config_path = explode('/', $path, -1);
    // Get folder_id.
    $folder_path = explode('/', $path);
    $config_id = end($config_path);
    $folder_id = end($folder_path);
    $folder_id_parent = \Drupal::request()->query->get('folder_id');
    $config = \Drupal::request()->query->get('config_id');
    if (!is_null($folder_id_parent) && !is_null($config)) {
      $cmis_connection_api = new CmisConnectionApi($config);
      $cmis_connection_api->setDefaultParameters();
      if (!is_null($cmis_connection_api->getObjectById($folder_id_parent))) {
        $folder_id = $folder_id_parent;
        $config_id = $config;
      }
    }
    $browser = new CmisRepositoryController($config_id, $folder_id, ['show_breadcrumb' => $show_breadcrumb]);

    return $browser->browse($config_id, $folder_id);
  }

}
