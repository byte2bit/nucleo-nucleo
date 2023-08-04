<?php

declare(strict_types = 1);

namespace Drupal\cmis\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'cmis_field_link' formatter.
 *
 * @FieldFormatter(
 *   id = "cmis_field_browser",
 *   label = @Translation("Cmis Field Browser"),
 *   field_types = {
 *     "cmis_field"
 *   }
 * )
 */
class CmisFieldBrowser extends FormatterBase {

  /**
   * Defines the default settings for this plugin.
   *
   * @return array
   *   A list of default settings, keyed by the setting name.
   */
  public static function defaultSettings() {
    return [
      'show_breadcrumb' => TRUE,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);

    $form['show_breadcrumb'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show breadcrumb'),
      '#description' => $this->t('This option will affect only the first display. When navigating into folders the breadcrumb will be displayed.'),
      '#default_value' => $this->getSetting('show_breadcrumb'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $settings = $this->getSettings();
    $summary[] = ($settings['show_breadcrumb'] == 1) ? $this->t('Show breadcrumb: Yes') : $this->t('Show breadcrumb: No');
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $settings = $this->getSettings();
    $elements = [];

    foreach ($items as $delta => $item) {
      $elements[$delta]['#lazy_builder'] = ['cmis.field_browser_builder:build',
        [
          $item->get('path')->getValue(),
          $settings['show_breadcrumb'],
        ],
      ];
      $elements[$delta]['#create_placeholder'] = TRUE;
    }
    return $elements;
  }

}
