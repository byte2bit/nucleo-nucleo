<?php
/**
 * elFinder Integration
 *
 * Copyright (c) 2010-2020, Alexey Sukhotin. All rights reserved.
 */

/**
 * Contains \Drupal\elfinder\Controller\elFinderAdminController.
 */

namespace Drupal\elfinder\Controller;

use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Access\AccessResult;

/**
 * Controller routines for elFinder Admin routes.
 */
class elFinderAdminController extends ControllerBase {

  /**
   * Returns an administrative settings
   */
  public function adminSettings(Request $request) {

    try {
      $profiles = $this->entityTypeManager()->getListBuilder('elfinder_profile')->render();
    } catch (Exception $e) {
      $profiles = array();
    }

    $output['profile_list'] = array(
      '#type' => 'container',
      '#attributes' => array('class' => array('elfinder-profile-list')),
      'title' => array('#markup' => '<h2>' . $this->t('Profiles') . '</h2>'),
      'list' => $profiles,
    );


    $output['settings_form'] = \Drupal::formBuilder()->getForm('Drupal\elfinder\Form\AdminForm') + array('#weight' => 10);

    return $output;
  }


  public function page($scheme, Request $request) {
    return array();
  }

  public function checkAccess($scheme) {
    return AccessResult::allowedIf(TRUE);
  }

}
