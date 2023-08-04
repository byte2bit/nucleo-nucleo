<?php
/**
 * elFinder Integration
 *
 * Copyright (c) 2010-2020, Alexey Sukhotin. All rights reserved.
 */

namespace Drupal\elfinder\Plugin\CKEditorPlugin;

use Drupal\editor\Entity\Editor;
use Drupal\ckeditor\CKEditorPluginBase;
use Drupal\Core\Url;

/**
 * Defines elFinder plugin for CKEditor.
 *
 * @CKEditorPlugin(
 *   id = "elfinder",
 *   label = "elFinder"
 * )
 */
class elFinder extends CKEditorPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getFile() {
    return drupal_get_path('module', 'elfinder') . '/editors/ckeditor/ckeditor.callback.js';
  }

  public function getLibraries(Editor $editor) {
    return [
      'elfinder/drupal.elfinder.jqueryui',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getConfig(Editor $editor) {
    return array(
      'filebrowserBrowseUrl' => Url::fromRoute('elfinder')->toString(),
      'elFinderImageIcon' => file_create_url(drupal_get_path('module', 'ckeditor') . '/js/plugins/drupalimage/icons/drupalimage.png'),
      'elFinderLinkIcon' => file_create_url(drupal_get_path('module', 'ckeditor') . '/js/plugins/drupallink/icons/drupallink.png'),
    );
  }

  /**
   * {@inheritdoc}filebrowserBrowseUrl
   */
  public function getButtons() {
    return array(
      'elFinderImage' => array(
        'label' => t('Insert image with elFinder'),
        'image' => drupal_get_path('module', 'ckeditor') . '/js/plugins/drupalimage/icons/drupalimage.png',
      ),
     'elFinderLink' => array(
        'label' => t('Insert anchor with elFinder'),
        'image' => drupal_get_path('module', 'ckeditor') . '/js/plugins/drupallink/icons/drupallink.png',
      ),

    );
  }

  /**
   * {@inheritdoc}
   */
  function getDependencies(Editor $editor) {
    return array('drupalimage', 'drupallink', 'elfinder');
  }


}
