(function ($, Drupal) {
  "use strict";

  /**
   * @file
   * Integrates elFinder into file field widgets.
   */

  /**
   * Global container for helper methods.
   */
  var elfinderFileField = window.elfinderFileField = {};

  /**
   * Drupal behavior to handle elfinder file field integration.
   */
  Drupal.behaviors.elfinderFileField = {
    attach: function (context, settings) {
      var elfinderOpts = {
        editorCallback: function() {
          // alert(123);
        },
        getFileCallback: function(file, arg2) {
          console.log('picked file: ', file);
          console.log('target field: ', elfinderFileField.container.data('filepath_id'));
          $('#'+elfinderFileField.container.data('filepath_id')).val(file.fid).change();
          elfinderFileField.container.dialog('close');
        }
      }

      console.log('settings=', settings);

      /* Pushing all settings to elFinder */
      $.extend(settings.elfinder, elfinderOpts);

      elfinderFileField.container.drupalelfinder().dialog({
        autoOpen: false,
        title: Drupal.t('Select file'),
      });
    }
  };

  elfinderFileField.container = $('<div id="ffs-elfinder"></div>');

  elfinderFileField.modal = function(options) {
     elfinderFileField.container.data('filepath_id', options.filepath_id);
     elfinderFileField.container.dialog('open');
     return false;
  };

})(jQuery, Drupal);
