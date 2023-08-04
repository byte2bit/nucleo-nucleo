<?php

namespace Drupal\elfinder_ffsel\Plugin\FilefieldSource;

use Drupal\Core\File\FileSystem;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\filefield_sources\FilefieldSourceInterface;
use Drupal\Core\Site\Settings;

/**
 * A FileField source plugin to allow insert files using elFinder.
 *
 * @FilefieldSource(
 *   id = "elfinder",
 *   name = @Translation("Insert file with elFinder"),
 *   label = @Translation("elFinder"),
 *   description = @Translation("Insert file with elFinder."),
 *   weight = 1
 * )
 */
class elFinder implements FilefieldSourceInterface {

  /**
   * {@inheritdoc}
   */
  public static function value(array &$element, &$input, FormStateInterface $form_state) {
    if (isset($input['filefield_elfinder']['file_path']) && $input['filefield_elfinder']['file_path'] != '') {
      if ((int)$input['filefield_elfinder']['file_path'] > 0) {
         $input['fids'][] = (int)$input['filefield_elfinder']['file_path'];
      } else {
         $form_state->setError($element, t('The selected file could not be used because the file does not exist in the database.'));
      }
      $input['filefield_elfinder']['file_path'] = '';
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function process(array &$element, FormStateInterface $form_state, array &$complete_form) {
    $element['filefield_elfinder'] = [
      '#weight' => 100.5,
      '#theme' => 'filefield_sources_element',
      '#source_id' => 'elfinder',
      '#filefield_source' => true,
      '#filefield_sources_hint_text' => t('Pick file using elFinder'),
    ];

    $filepath_id = $element['#id'] . '-elfinder-path';
    $display_id = $element['#id'] . '-elfinder-display';
    $select_id = $element['#id'] . '-elfinder-select';

    $element['#attached']['library'][] = 'elfinder_ffsel/drupal.elfinder.filefield';

    $elfinder_function = "return elfinderFileField.modal()";

    $element['filefield_elfinder']['display_path'] = array(
      '#type' => 'markup',
      '#markup' => '<span id="' . $display_id . '" class="filefield-sources-elfinder-display">' . t('No file selected') . '</span>',
    );

    $element['filefield_elfinder']['browse_button'] = [
      '#type' => 'button',
      '#name' => 'browse',
      '#value' => t('Browse'),
      '#attributes' => [
         'onclick' => "elfinderFileField.modal({'filepath_id': '" . $filepath_id. "'});return false;",
      ],
    ];

    $element['filefield_elfinder']['file_path'] = [
      '#type' => 'hidden',
      '#value' => '',
      '#attributes' => array(
        'id' => $filepath_id,
        'onchange' => "if (!jQuery('#". $select_id . "').attr('disabled')) { jQuery('#" . $select_id . "').click(); jQuery('#" . $select_id . "').attr('disabled', true); jQuery('#$display_id').html(this.value); }",
      ),

    ];

    $class = '\Drupal\file\Element\ManagedFile';
    $ajax_settings = [
      'callback' => [$class, 'uploadAjaxCallback'],
      'options' => [
        'query' => [
          'element_parents' => implode('/', $element['#array_parents']),
        ],
      ],
      'wrapper' => $element['upload_button']['#ajax']['wrapper'],
      'effect' => 'fade',
    ];

    $element['filefield_elfinder']['elfinder_button'] = [
      '#name' => implode('_', $element['#parents']) . '_elfinder_select',
      '#type' => 'submit',
      '#value' => t('Select'),
      '#attributes' => array(
        'class' => array('js-hide'),
        'id' => $select_id,
      ),
      '#validate' => [],
      '#submit' => ['filefield_sources_field_submit'],
      '#limit_validation_errors' => [$element['#parents']],
      '#ajax' => $ajax_settings,
    ];


    return $element;
  }

  public static function element($variables) {
    $element = $variables['element'];
    $output = '';
    foreach (Element::children($element) as $key) {
      if (!empty($element[$key])) {
        $output .= \Drupal::service('renderer')->render($element[$key]);
      }
    }
    return '<div class="filefield-source filefield-source-elfinder clear-block">' . $output . '</div>';
  }

}
