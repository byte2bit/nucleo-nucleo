/**
 * @file
 * Contains the definition of the behavior cmisBrowser.
 */

(function ($, Drupal) {

  'use strict';

  /**
   * Remove item-list class.
   *
   * Twig template suggestion is not taken in the module in module.
   */
  Drupal.behaviors.cmisBrowser = {
    attach: function (context, settings) {
      $(context).find('.js-cmis-breadcrumb').once('js--cmis-breadcrumb').each(function () {
        $(this).removeClass('item-list');
      });
    }
  };

})(jQuery, Drupal);
