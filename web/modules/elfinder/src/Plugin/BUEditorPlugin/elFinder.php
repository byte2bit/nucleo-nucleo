<?php
/**
 * elFinder Integration
 *
 * Copyright (c) 2010-2021, Alexey Sukhotin. All rights reserved.
 */

/**
 * Contains \Drupal\elfinder\Plugin\BUEditorPlugin\elFinder.
 */

namespace Drupal\elfinder\Plugin\BUEditorPlugin;

use Drupal\Core\Form\FormStateInterface;
use Drupal\editor\Entity\Editor;
use Drupal\bueditor\BUEditorPluginBase;
use Drupal\bueditor\Entity\BUEditorEditor;
use Drupal\elfinder\Controller\elFinderPageController as elFinderPageController;
use Drupal\Core\Url;

/**
 * Defines elFinder as a BUEditor plugin.
 *
 * @BUEditorPlugin(
 *   id = "elfinder",
 *   label = "elFinder File Manager"
 * )
 */
class elFinder extends BUEditorPluginBase {

  /**
   * {@inheritdoc}
   */
  public function alterEditorJS(array &$js, BUEditorEditor $bueditor_editor, Editor $editor = NULL) {
    if (isset($js['settings']['fileBrowser']) && $js['settings']['fileBrowser'] === 'elfinder') {
      $js['libraries'][] = 'elfinder/drupal.elfinder';
      $js['libraries'][] = 'elfinder/drupal.elfinder.bueditor';
      $browserpage = elFinderPageController::buildBrowserPage(TRUE);
      $js['settings']['elfinder'] = $browserpage['#attached']['drupalSettings']['elfinder'];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function alterEditorForm(array &$form, FormStateInterface $form_state, BUEditorEditor $bueditor_editor) {
    // Add elFinder option to file browser field.
    $fb = &$form['settings']['fileBrowser'];
    $fb['#options']['elfinder'] = $this->t('elFinder');
    // Add configuration link
    $form['settings']['elfinder'] = array(
      '#type' => 'container',
      '#states' => array(
        'visible' => array(':input[name="settings[fileBrowser]"]' => array('value' => 'elfinder')),
      ),
      '#attributes' => array(
        'class' => array('description'),
      ),
      'content' => array(
        '#markup' => $this->t('Configure <a href=":url">elFinder</a>.', array(':url' => Url::fromRoute('elfinder.admin')->toString()))
      ),
    );
    // Set weight
    if (isset($fb['#weight'])) {
      $form['settings']['elfinder']['#weight'] = $fb['#weight'] + 0.1;
    }

    //$browserpage = elFinderPageController::buildBrowserPage(FALSE);
    //\Drupal::messenger()->addMessage('99');
    //  $form['#attached']['drupalSettings']['elfinder'] = $browserpage['#attached']['drupalSettings']['elfinder'];
  }

}
