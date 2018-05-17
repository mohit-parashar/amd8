/**
 * @file
 * Front-end Administration handling of blocks.
 *
 * Sponsored by: www.freelance-drupal.com
 */

(function ($, Drupal) {

  /**
   * Start the block handling behavior:
   *  - move block around, from one region to another.
   */
  Drupal.behaviors.feadmin_block = {
    attach: function (context, settings) {

      if (!this.isInitialized) {
        this.isInitialized = true;

        // Init body view.
        Drupal.feaAdmin.block.views.bodyVisualView = new Drupal.feaAdmin.block.BodyVisualView({
          model: Drupal.feaAdmin.toolbar.models.toolbarModel
        });
      }
    }
  };

  /**
   * feaAdmin block Backbone objects.
   */
  Drupal.feaAdmin.block = {
    // A hash of View instances.
    views: {},
    // A hash of Model instances.
    models: {}
  };

})(jQuery, Drupal);