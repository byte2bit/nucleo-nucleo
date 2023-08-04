/*
 * elFinder Integration
 *
 * Copyright (c) 2010-2020, Alexey Sukhotin. All rights reserved.
 */

(function($, Drupal, BUE) {
  'use strict';

  /**
   * Defines elFinder plugin for BUEditor.
   */

  /**
   * File browser handler for image/link dialogs.
   */
  BUE.fileBrowsers.elfinder = function(field, type, E) {
    var width = Math.min(1000, parseInt(screen.availWidth * 0.8));
    var height = Math.min(800, parseInt(screen.availHeight * 0.8));
    var field_id = BUE.elfinder.fields.length;
    var url = drupalSettings.elfinder.moduleUrl;

    var elfinderOpts = {
      editorCallback: function() {
        // alert(123);
      },
      getFileCallback: function(file, arg2) {

        field.value = file['url'];

        var name;
        var input;
        var value;
        var values = {width: file.width, height: file.height, alt: file.name};
        for (name in values) {
          if ((value = values[name])) {
            if ((input = field.form.elements[name])) {
              input.value = value;
            }
          }
        }
        E.getPopup('elfinder').close();
      }
    }

    /* Pushing all settings to elFinder */
    $.extend(drupalSettings.elfinder, elfinderOpts);

    var elfinder = $('<div id="bu-elfinder"></div>').drupalelfinder();


    var pop;

    if (!E.getPopup('elfinder')) {
      pop = E.createPopup('elfinder', 'Choose File', elfinder, null);
    } else {
      pop = E.getPopup('elfinder');
    }

    pop.open();
  };

  /**
   * Global container for helper methods.
   */
  BUE.elfinder = BUE.elfinder || {

    /**
     * Active form fields currently using the file browser.
     */
    fields: [],

    /**
     * elFinder sendto handler for inserting a file url into a form field.
     */
    sendtoField: function(File, win) {
      var field;
      var id = win.elfinder.getQuery('field_id');
      if ((field = BUE.elfinder.fields[id])) {
        // Set field value
        field.value = File.getUrl();
        // Check other fields
        var name;
        var input;
        var value;
        var values = {width: File.width, height: File.height, alt: File.formatName()};
        for (name in values) {
          if ((value = values[name])) {
            if ((input = field.form.elements[name])) {
              input.value = value;
            }
          }
        }
        field.focus();
        BUE.elfinder.fields[id] = null;
      }
      win.close();
    },

    /**
     * Returns imce url.
     */
    url: function(query) {
      var url = Drupal.url('elfinder');
      if (query) {
        url += (url.indexOf('?') === -1 ? '?' : '&') + query;
      }
      return url;
    }

  };

})(jQuery, Drupal, BUE);