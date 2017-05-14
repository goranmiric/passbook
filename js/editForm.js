/**
 * @file
 * Defines Javascript behaviors for the passbook module.
 */

(function ($, Drupal, drupalSettings) {

  'use strict';

  /**
   * Behaviors for tabs in the edit form.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches summary behavior for tabs in the edit form.
   */
  Drupal.behaviors.passbookDetailsSummaries = {
    attach: function (context) {
      var $context = $(context);

      $context.find('.form-author-data').drupalSetSummary(function (context) {
        var $authorContext = $(context);
        var name = $authorContext.find('.field--name-uid input').val();
        var date = $authorContext.find('.field--name-created input').val();

        if (name && date) {
          return Drupal.t('By @name on @date', {'@name': name, '@date': date});
        }
        else if (name) {
          return Drupal.t('By @name', {'@name': name});
        }
        else if (date) {
          return Drupal.t('Authored on @date', {'@date': date});
        }
      });
    }
  };

})(jQuery, Drupal, drupalSettings);
