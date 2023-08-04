<?php
/**
 * elFinder Integration
 *
 * Copyright (c) 2010-2020, Alexey Sukhotin. All rights reserved.
 */

/**
 * file manager admin settings page
 */

namespace Drupal\elfinder\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\Form\ConfigFormBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Component\Utility\Environment;

class AdminForm extends ConfigFormBase {

  protected function getEditableConfigNames() {
    return array('elfinder.settings');
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'elfinder_admin_settings';
  }

  public function elfinder_admin_profile_links($profile_name) {
    $links = l($this->t('Edit'), 'admin/config/media/elfinder/profile/' . $profile_name . '/edit') . ' ' . l($this->t('Delete'), 'admin/config/media/elfinder/profile/' . $profile_name . '/delete');
    return $links;
  }


  /**
   * Settings form definition
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('elfinder.settings');
    global $language;
    $user = \Drupal::currentUser();
    $path = drupal_get_path('module', 'elfinder');

    $langCode = isset($language->language) ? $language->language : 'en';

    $roles = user_roles();

    $form['filesystem_settings'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('File system settings'),   //return parent::buildForm($form, $form_state);
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
    );


    $form['filesystem_settings']['filesystem_public_root_label'] = array(
      '#prefix' => '<div class="custom-container">',
      '#suffix' => '</div>',
      '#type' => 'textfield',
      '#title' => $this->t('Public files root directory label'),
      '#default_value' => $config->get('filesystem.public_root_label'),
      '#description' => $this->t('Root directory label in directory tree'),
    );

    $form['filesystem_settings']['filesystem_private_root_label'] = array(
      '#prefix' => '<div class="custom-container">',
      '#suffix' => '</div>',
      '#type' => 'textfield',
      '#title' => $this->t('Private files root directory label'),
      '#default_value' => $config->get('filesystem.private_root_label'),
      '#description' => $this->t('Root directory label in directory tree'),
    );

    $form['filesystem_settings']['filesystem_unmanaged_root_label'] = array(
      '#prefix' => '<div class="custom-container">',
      '#suffix' => '</div>',
      '#type' => 'textfield',
      '#title' => $this->t('Unmanaged files root directory label'),
      '#default_value' => $config->get('filesystem.unmanaged_root_label'),
      '#description' => $this->t('Root directory label in directory tree'),
    );

    $form['filesystem_settings']['filesystem_root_custom'] = array(
      '#prefix' => '<div class="custom-container">',
      '#suffix' => '</div>',
      '#type' => 'textfield',
      '#title' => $this->t('Custom unmanaged files path'),
      '#default_value' => $config->get('filesystem.root_custom'),
      '#description' => $this->t('Custom filesystem root path.') . '<br/>' . $this->t('Available tokens: <code>%files</code> (base path, eg: <code>/</code>), <code>%name</code> (current username, eg: <code>@u</code>, <b>NOTE:</b> it is not unique - users can have same username, so better to combine it with user id value), <code>%uid</code> (current user id, eg: <code>@uid</code>), <code>%lang</code> (current language code, eg: <code>@lang</code>), plus all tokens provided by token module', array('@u' => $user->getDisplayName(), '@uid' => $user->id(), '@lang' => $langCode)),
    );

    $form['filesystem_settings']['filesystem_url_custom'] = array(
      '#prefix' => '<div class="custom-container">',
      '#suffix' => '</div>',
      '#type' => 'textfield',
      '#title' => $this->t('Custom unmanaged files URL'),
      '#default_value' => $config->get('filesystem.url_custom'),
      '#description' => $this->t('Custom filesystem url.') . '<br/>' . $this->t('Available tokens: <code>%files</code> (base path, eg: <code>/</code>), <code>%name</code> (current username, eg: <code>@u</code>, <b>NOTE:</b> it is not unique - users can have same username, so better to combine it with user id value), <code>%uid</code> (current user id, eg: <code>@uid</code>), <code>%lang</code> (current language code, eg: <code>@lang</code>), plus all tokens provided by token module', array('@u' => $user->getDisplayName(), '@uid' => $user->id(), '@lang' => $langCode)),
    );

    $form['filesystem_settings']['mime_detect'] = array(
      '#type' => 'radios',
      '#title' => $this->t('File type detection'),
      '#default_value' => $config->get('filesystem.mimedetect'),
      '#options' => array(
        'auto' => $this->t('Automatical detection'),
      ),
    );

    $form['filesystem_settings']['filesystem_allowed_extensions'] = array(
      '#prefix' => '<div class="custom-container">',
      '#suffix' => '</div>',
      '#type' => 'textfield',
      '#title' => $this->t('Allowed file extensions'),
      '#default_value' => $config->get('filesystem.allowed_extensions'),
      '#description' => $this->t('Separate extensions with a space or comma and do not include the leading dot.'),
    );

    if (function_exists('finfo_open')) {
      $form['filesystem_settings']['mime_detect']['#options']['finfo'] = $this->t('php finfo');
    }

    if (function_exists('mime_content_type')) {
      $form['filesystem_settings']['mime_detect']['#options']['php'] = $this->t('php mime_content_type()');
    }

    $form['filesystem_settings']['mime_detect']['#options']['linux'] = $this->t('file -ib (linux)');
    $form['filesystem_settings']['mime_detect']['#options']['bsd'] = $this->t('file -Ib (bsd)');
    $form['filesystem_settings']['mime_detect']['#options']['internal'] = $this->t('By file extension (built-in)');
    $form['filesystem_settings']['mime_detect']['#options']['drupal'] = $this->t('Drupal API');

    $form['filesystem_settings']['filesystem_inline_preview'] = array(
      '#type' => 'radios',
      '#title' => $this->t('Preview'),
      '#default_value' => $config->get('filesystem.inline_preview'),
      '#description' => $this->t('File types allowed to display in preview'),
      '#options' => array(
        'default' => $this->t('Default - images, video, audio, pdf, text'),
        'all' => $this->t('All supported for preview files - additional: md, psd, html, archives, swf, sharecad.org, MS Office Online, Google Docs - be careful'),
        'custom' => $this->t('Custom regex'),
        'disabled' => $this->t('Disabled'),
      ),
    );

    $form['filesystem_settings']['filesystem_inline_preview_custom'] = array(
      '#prefix' => '<div class="custom-container">',
      '#suffix' => '</div>',
      '#type' => 'textfield',
      '#title' => $this->t('Custom preview match regex'),
      '#default_value' => $config->get('filesystem.inlinepreviewcustom'),
      '#description' => $this->t('Custom mime type match regex for preview'),
    );

    $form['filesystem_settings']['filesystem_external_preview'] = array(
      '#type' => 'radios',
      '#title' => $this->t('External Service Preview'),
      '#default_value' => $config->get('filesystem.external_preview', 'disabled'),
      '#description' => t('Use Microsoft, Google and other online services to preview some office documents. <b>Warning!</b> By previewing document with external services <b>YOU ARE ULOADING</b> the document to them. Google, Microsoft and other service owners usually <b>TRACK</b> your activity and <b>share it with Sales, CIA, FSB (KGB), FBI, governors, etc.</b>'),
      '#options' => array(
        'default' => $this->t('Use Microsoft Office and Google Docs for preview'),
        'disabled' => $this->t('Disabled'),
      ),
    );

    $form['filesystem_settings']['file_url_type'] = array(
      '#type' => 'radios',
      '#title' => $this->t('Selected file url type'),
      '#default_value' => $config->get('filesystem.fileurl'),
      '#options' => array(
        'true' => $this->t('Absolute'),
        'false' => $this->t('Relative'),
      ),
    );

    $form['filesystem_settings']['file_perm'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Created file permissions'),
      '#default_value' => $config->get('filesystem.fileperm'),
      '#size' => 4,
    );

    $form['filesystem_settings']['dir_perm'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Created directory permissions'),
      '#default_value' => $config->get('filesystem.dirperm'),
      '#size' => 4,
    );


    $form['filesystem_settings']['max_filesize'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Maximum upload size'),
      '#default_value' => $config->get('filesystem.maxfilesize'),
      '#description' => $this->t('Enter a value like "512" (bytes), "80 KB" (kilobytes) or "50 MB" (megabytes) in order to restrict the allowed file size. If left empty the file sizes will be limited only by PHP\'s maximum post and file upload sizes (current limit <strong>%limit</strong>).', array('%limit' => format_size(Environment::getUploadMaxSize()))),
      '#size' => 10,
      '#weight' => 5,
    );

    $form['filesystem_settings']['max_archivesize'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Maximum archive file size'),
      '#default_value' => $config->get('filesystem.maxarchivesize'),
      '#description' => $this->t('Enter a value like "512" (bytes), "80 KB" (kilobytes) or "50 MB" (megabytes) in order to restrict the allowed file size. If left empty the archive file file sizes will not be checked during extraction.'),
      '#size' => 10,
      '#weight' => 5,
    );

    $form['filesystem_settings']['max_filecount'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Maximum folder size'),
      '#default_value' => $config->get('filesystem.maxfilecount'),
      '#description' => $this->t('The maximum number of files allowed in a directory. 0 for unlimited.'),
      '#size' => 4,
      '#weight' => 5,
    );


    $form['filesystem_settings']['handleprivate'] = array(
      '#type' => 'radios',
      '#title' => $this->t('Handle private downloads'),
      '#default_value' => $config->get('filesystem.handleprivate', false),
      '#options' => array(
        'true' => $this->t('Yes'),
        'false' => $this->t('No'),
      ),
      '#description' => $this->t('Use elFinder to handle private file downloads'),
    );

    $form['thumbnail_settings'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Image settings'),
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
    );

    $form['thumbnail_settings']['tmbsize'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Thumbnail size'),
      '#default_value' => $config->get('thumbnail.size'),
      '#size' => 4,
    );

    $form['thumbnail_settings']['tmbdirname'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Thumbnail directory name'),
      '#default_value' => $config->get('thumbnail.dirname'),
      '#size' => 10,
    );

    $form['thumbnail_settings']['imglib'] = array(
      '#type' => 'radios',
      '#title' => $this->t('Image manipulation library'),
      '#default_value' => $config->get('thumbnail.imglib'),
      '#options' => array(
        'auto' => $this->t('Automatical detection'),
        'imagick' => $this->t('Image Magick'),
        'gd' => $this->t('GD'),
      ),
    );

    $form['thumbnail_settings']['tmbcrop'] = array(
      '#type' => 'radios',
      '#title' => $this->t('Image crop'),
      '#default_value' => $config->get('thumbnail.tmbcrop', false),
      '#options' => array(
        'true' => $this->t('Yes'),
        'false' => $this->t('No'),
      ),
      '#description' => $this->t('Crop image to fit thumbnail size. Yes - crop, No - scale image to fit thumbnail size.'),
    );

    $form['misc_settings'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Miscellaneous settings'),
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
    );

    $form['misc_settings']['rememberlastdir'] = array(
      '#type' => 'radios',
      '#title' => $this->t('Remember last opened directory'),
      '#default_value' => $config->get('misc.rememberlastdir', false),
      '#options' => array(
        'true' => $this->t('Yes'),
        'false' => $this->t('No'),
      ),
      '#description' => $this->t('Creates a cookie. Disable if you have issues with caching.'),
    );

    $form['misc_settings']['usesystemjquery'] = array(
      '#type' => 'radios',
      '#title' => $this->t('Use system jQuery'),
      '#default_value' => $config->get('misc.usesystemjquery', false),
      '#options' => array(
        'true' => $this->t('Yes'),
        'false' => $this->t('No'),
      ),
      '#description' => $this->t('Use system jQuery and jQuery UI when possible. If set to \'No\' jQuery hosted at Google will be uses.'),
    );

    $form['misc_settings']['manager_width'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('File manager width'),
      '#default_value' => $config->get('misc.manager_width'),
      '#size' => 4,
    );

    $form['misc_settings']['manager_height'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('File manager height'),
      '#default_value' => $config->get('misc.manager_height'),
      '#size' => 4,
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * Save form data
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $config = \Drupal::configFactory()->getEditable('elfinder.settings');
    $config->set('thumbnail.size', $form_state->getValue('tmbsize'));
    $config->set('thumbnail.dirname', $form_state->getValue('tmbdirname'));

    if ($form_state->getValue('filesystem_root_custom') != '') {
      $config->set('filesystem.root_custom', $form_state->getValue('filesystem_root_custom'));
    }

    $config->set('filesystem.url_custom', $form_state->getValue('filesystem_url_custom'));
    $config->set('filesystem.mimedetect', $form_state->getValue('mime_detect'));
    $config->set('filesystem.fileurl', $form_state->getValue('file_url_type'));
    $config->set('thumbnail.imglib', $form_state->getValue('imglib'));
    $config->set('filesystem.fileperm', $form_state->getValue('file_perm'));
    $config->set('filesystem.dirperm', $form_state->getValue('dir_perm'));
    $config->set('misc.rememberlastdir', $form_state->getValue('rememberlastdir', 'false'));
    $config->set('misc.usesystemjquery', $form_state->getValue('usesystemjquery', 'false'));
    $config->set('thumbnail.tmbcrop', $form_state->getValue('tmbcrop', 'false'));
    $config->set('filesystem.maxfilesize', $form_state->getValue('max_filesize'));
    $config->set('filesystem.maxarchivesize', $form_state->getValue('max_archivesize'));
    $config->set('filesystem.maxfilecount', $form_state->getValue('max_filecount'));
    $config->set('filesystem.handleprivate', $form_state->getValue('handleprivate', 'false'));
    $config->set('filesystem.public_root_label', $form_state->getValue('filesystem_public_root_label'));
    $config->set('filesystem.private_root_label', $form_state->getValue('filesystem_private_root_label'));
    $config->set('filesystem.unmanaged_root_label', $form_state->getValue('filesystem_unmanaged_root_label'));
    $config->set('misc.manager_width', $form_state->getValue('manager_width'));
    $config->set('misc.manager_height', $form_state->getValue('manager_height'));
    $config->set('filesystem.allowed_extensions', $form_state->getValue('filesystem_allowed_extensions'));

    if ($form_state->getValue('filesystem_inline_preview') == 'default') {
      $config->set('filesystem.inlinepreviewregex', '^(?:(?:image|video|audio)|application/(?:x-mpegURL|dash\\+xml)|(?:text/plain|application/pdf)$)');
    } else if ($form_state->getValue('filesystem_inline_preview') == 'all') {
      $config->set('filesystem.inlinepreviewregex', '.');
    } else if ($form_state->getValue('filesystem_inline_preview') == 'custom') {
      $config->set('filesystem.inlinepreviewregex', $config->get('filesystem.inlinepreviewcustom'));
    } else {
      $config->set('filesystem.inlinepreviewregex', '^$');
    }

    $config->set('filesystem.inline_preview', $form_state->getValue('filesystem_inline_preview', 'default'));
    $config->set('filesystem.inlinepreviewcustom', $form_state->getValue('filesystem_inline_preview_custom'));
    $config->set('filesystem.external_preview', $form_state->getValue('filesystem_external_preview', 'disabled'));

    $config->save();

    parent::submitForm($form, $form_state);
  }

}
