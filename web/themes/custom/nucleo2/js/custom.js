/**
 * @file
 * Global utilities.
 *
 */
(function ($, Drupal) {

  'use strict';

  Drupal.behaviors.nucleo2 = {
    attach: function (context, settings) {

      // Custom code here

      $("span, p").each(function () {
        var text = $(this).text();
        text = text.replace(new RegExp('pré-vizualizar', 'ig'), 'pré-visualizar');
        $(this).text(text);
      });

      $(".tablesort--desc").html('<i class="fa fa-arrow-down" aria-hidden="true"></i>');
      $(".tablesort--asc").html('<i class="fa fa-arrow-up" aria-hidden="true"></i>');

      $(".views_slideshow_cycle_teaser_section_slide_inicio-block_1").css("width", "100%");

      $("#block-nucleo2-search-form-narrow #edit-submit")
        .html('<i class="fa fa-search" aria-hidden="true"></i>')
        .removeClass("btn-outline-primary")
        .addClass("button-outline-cinza");

      // let usuario = $('#block-views-block-logado-block-1-2 > div.content > div > div > div.view-content.row > div > div > div > a');

/*       if ($('#esconde-user').text() != 1060) {
        $('#toolbar-administration').css("display", "none");
        $('.toolbar-fixed.toolbar-tray-open:not(.toolbar-vertical), .gin--vertical-toolbar, .gin--horizontal-toolbar, .gin--classic-toolbar').attr('padding-top','0 !important');
        $("body").css("margin-top","-78px");
      } */

      // $("div[id^='reservas_de_salas-'] .contents a").append('<br>');
      $(".page-view-reservas-de-salas .calendar.monthview .contents *").css("display","block");

    }



  };

})(jQuery, Drupal);