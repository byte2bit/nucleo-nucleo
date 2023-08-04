/*
 * elFinder Integration
 *
 * Copyright (c) 2010-2020, Alexey Sukhotin. All rights reserved.
 */

Drupal.elfinder.editor.bueditor = {
  fn: {
    attach: Drupal.wysiwyg.editor.attach.bueditor
  }
}

Drupal.wysiwyg.editor.attach.bueditor = function(context, params, settings) {
  Drupal.settings.BUE.imceURL = Drupal.settings.elfinder.moduleUrl;
  Drupal.elfinder.editor.bueditor.fn.attach.apply(this, arguments);
}
