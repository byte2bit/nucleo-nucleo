<?php
/**
 * elFinder Integration
 *
 * Copyright (c) 2010-2020, Alexey Sukhotin. All rights reserved.
 */

namespace Drupal\elfinder\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\SafeMarkup;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use \elFinder;

class elFinderPageController extends ControllerBase {

  public function getContent($scheme, Request $request) {
    $build = array(
      '#type' => 'markup',
      '#markup' => t('Hello World!'),
    );
    return $build;

  }

  public function getBrowser($scheme, Request $request, RouteMatchInterface $route_match) {


    $build = $this->buildBrowserPage(FALSE);

    // '#markup' => '<div id="finder">test</div>',
    $build['#theme'] = 'browser_page';
    /*
     setting alpha1 variable for browser-page template (browser-page.html.twig)
     !!! Clear cache in Admin backend to see template/theme changes (you MUST do it if caching disabled too) !!! 
    */
    /* template vaariable pass example */
    //$build['#alpha1'] = 'm,6m2mm';

    $regions = array(

      'sidebar_first' => array(
        //'#theme' => 'elfinder_page',
        '#markup' => ''
      )
    );

    return \Drupal::service('bare_html_page_renderer')->renderBarePage($build, t('File manager'), 'elfinder_page', $regions);

  }

  public function getBrowserPage($scheme, Request $request, RouteMatchInterface $route_match) {
    $build = array();

    $build['elfinder-admin-container'] = $this->buildBrowserPage(TRUE);
    // $build['elfinder-admin-container']['#markup'] = t('<div id="finder"></div>');
    $build['elfinder-admin-container']['#theme'] = 'browser_page';

    return $build;

  }


  public function checkAccess($scheme) {
    return AccessResult::allowedIf(\Drupal::currentUser()->hasPermission('use file manager'));
  }

  public static function getLibUrl() {
    global $base_url;

    $libpath = elfinder_lib_path() . '/';
    $relurl = str_replace(\Drupal::root(), '', $libpath);
    return $base_url . $relurl;
  }

  public static function buildBrowserPage($is_page_layout = FALSE) {

    global $language;

    $path = drupal_get_path('module', 'elfinder');
    $editorApp = '';
    $langCode = isset($language->language) ? $language->language : 'en';

    if (isset($_GET['app'])) {
      if (preg_match("/^[a-zA-Z]+$/", $_GET['app'])) {
        $editorApp = $_GET['app'];
      } elseif (preg_match("/^([a-zA-Z]+)|/", $_GET['app'], $m)) {
        $editorApp = $m[1];
      }
    }

    if (isset($_GET['langCode'])) {
      if (preg_match("/^[a-zA-z]{2}$/", $_GET['langCode'])) {
        $langCode = $_GET['langCode'];
      }
    }

    $token_generator = \Drupal::csrfToken();

    $token = $token_generator->get();

    $elfinder_js_settings = array(
      'editorApp' => $editorApp,
      'lang' => $langCode,
      'rememberLastDir' => \Drupal::config('elfinder.settings')->get('misc.rememberlastdir') == 'true' ? TRUE : FALSE, // remember last opened directory
      'disabledCommands' => elfinder_get_disabled_commands(),
      'requestType' => 'get',
      'closeOnEditorCallback' => false,
      'browserMode' => $is_page_layout ? 'backend' : 'default',
      'customData' => array('token' => $token),
      'baseUrl' => elFinderPageController::getLibUrl(),
      'moduleUrl' => ($is_page_layout ? Url::fromRoute('elfinder') : \Drupal::request()->getRequestUri()),
      'url' => ($is_page_layout ? Url::fromRoute('elfinder.connector')->toString() : \Drupal::request()->getRequestUri() . '/connector'),
      'commandsOptions' => array('dummy' => array('test'=>1)),
    );


    if (property_exists('elFinder', 'ApiVersion')) {
      $elfinder_js_settings['api21'] = true;

      if (\Drupal::config('elfinder.settings')->get('filesystem.external_preview') != 'disabled') {
        $elfinder_js_settings['commandsOptions']['quicklook'] = array(
          'sharecadMimes' => array('image/vnd.dwg', 'image/vnd.dxf', 'model/vnd.dwf', 'application/vnd.hp-hpgl', 'application/plt', 'application/step', 'model/iges', 'application/vnd.ms-pki.stl', 'application/sat', 'image/cgm', 'application/x-msmetafile'),
          'googleDocsMimes' => array('application/pdf', 'image/tiff', 'application/vnd.ms-office', 'application/msword', 'application/vnd.ms-word', 'application/vnd.ms-excel', 'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'application/postscript', 'application/rtf'),
          'officeOnlineMimes' => array('application/msword', 'application/vnd.ms-word', 'application/vnd.ms-excel', 'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'),
        );
      }


    } else {
      $elfinder_js_settings['api21'] = false;
    }


    $build = array();

    $build['#attached']['library'][] = 'elfinder/drupal.elfinder';
    $build['#attached']['library'][] = 'elfinder/drupal.elfinder.jqueryui';

    $build['#attached']['drupalSettings']['elfinder'] = $elfinder_js_settings;

    return $build;
  }


}
