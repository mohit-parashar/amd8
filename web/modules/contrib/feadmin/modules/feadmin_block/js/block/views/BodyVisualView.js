/**
 * @file
 * A Backbone view for the body when feaadmin toobar is rendered.
 * 
 * Sponsored by: www.freelance-drupal.com
 */

(function (Drupal, Backbone, $) {
  "use strict";

  Drupal.feaAdmin = Drupal.feaAdmin || {};

  /**
   * Backbone view for the body when feadmin toolbar is rendered.
   */
  Drupal.feaAdmin.block.BodyVisualView = Backbone.View.extend({

    /**
     * Main element.
     */
    el: 'body',

    /**
     * Regions within this body
     */
    regions: null,

    /**
     * {@inheritdoc}
     */
    initialize: function (options) {
      this.regions = $("[data-region]");
      this.listenTo(this.model, 'change:activeTool', this.drag);
    },

    /**
     * {@inheritdoc}
     */
    drag: function () {
      var activeTool = this.model.get('activeTool')
      if (activeTool === 'feadmin_block') {
        this.startDrag();
      } else {
        this.stopDrag();
      }
    },

    /**
     * Start dragging blocks around !
     */
    startDrag: function () {
      // Regions are sortable: blocks can move around.
      $('[data-block]').addClass('draggable');
      this.regions.sortable({
        appendTo: document.body,
        connectWith: "[data-region]",
        items: "[data-block]",
        placeholder: "block-placeholder ui-state-highlight",
        
        // Display the region indicators.
        start: function (event, ui) {
          _startDraggingOperation();
        },
        /*helper: function(e,li) {
          copyHelper = li.clone().insertAfter(li);
          return li.clone();
        },*/
        // Hide the region indicators.
        stop: function (event, ui) {
          _stopDraggingOperation();
        },

        // Display some blocks placeholder indicators.
        sort: function (event, ui) {
          $(".block-placeholder").text(Drupal.t("Drop your block here"));
        },

        // Update the block orders after the dragging was completed.
        update: function (event, ui) {
          if (this === ui.item.parent()[0]) {
            // The destination region cannot be empty anymore.
            $(this).removeClass('empty-region');

            // The origin region may now be empty.
            if (ui.sender && $("[data-block]:not('.empty-block')", ui.sender).length == 0) {
              ui.sender.addClass('empty-region');
            }

            var destinationRegionName = $(this).data('region');
            var originRegionName = ui.sender ? ui.sender.data('region') : destinationRegionName;
            var request = {};

            // Build destination region
            request['region'] = destinationRegionName;
            request['blocks'] = [];
            $("[data-block]", $(this)).each(function () {
              request['blocks'].push($(this).data('block'));
            });

            // Send the changed data to our backend.
            var uniqId = _uniqId();
            $.ajax({
              type: "POST",
              url: Drupal.url('feadmin/callback/blocks'),
              contentType: "application/json",
              data: JSON.stringify(request),
              beforeSend: function (xhr) {
                var notif = $.notify(Drupal.t("Blocks position are saving..."), {
                  className: ['wait', uniqId],
                  position: 'left bottom'
                });
              },
              success: function (data) {
                $(".notifyjs-bootstrap-" + uniqId).trigger('notify-hide');
                $.notify(data, {
                  className: 'success',
                  position: 'left bottom'
                });
              },
              error: function (data) {
                $(".notifyjs-bootstrap-" + uniqId).trigger('notify-hide');
                $.notify(Drupal.t("An error has happened: blocks positionning is not saved."), {
                  className: 'error',
                  position: 'left bottom'
                });
              }
            });
          }
        }
      });
      $(".feadmin_block-block").draggable({
        appendTo: "body",
        helper: "clone",
        connectToSortable: this.regions,
        placeholder: "block-placeholder ui-state-highlight",
        // Display the region indicators.
        start: function (event, ui) {
          _startDraggingOperation();
        },
        // Display the region indicators.
        stop: function (event, ui) {
          _stopDraggingOperation();
        }
      });
    },

    stopDrag: function () {
      $('[data-block]').removeClass('draggable');
      if (this.regions.data( "ui-sortable" )) {
        this.regions.sortable("destroy");
      }
    }

  });

  // Should work for most cases
  var _uniqId = function() {
    return Math.round(new Date().getTime() + (Math.random() * 100));
  };

  var _startDraggingOperation = function() {
    // Remove all sidebars body configurations and add two sidebars.
    $('body').removeClass(function (index, css) {
      return (css.match(/(^|\s)layout-\S+/g) || []).join(' ');
    }).addClass('layout-two-sidebars');
    // Show empty blocks and regions.
    $(".empty-region, .empty-block").show();
  };

  var _stopDraggingOperation = function() {
    var body = $('body');
    // Remove all sidebars configurations on body.
    body.removeClass(function (index, css) {
      return (css.match(/(^|\s)layout-\S+/g) || []).join(' ');
    });
    // Count number of sidebar columns now filled.
    var sidebarsCount, sidebars = $('[data-region*="sidebar"]:not(".empty-region")');
    if (sidebars.length) {
      sidebarsCount = (sidebars.length == 1) ? 'one' : 'two';
    } else {
      sidebarsCount = 'no';
    }
    // Add body general sidebar configurations.
    body.addClass('layout-' + sidebarsCount + '-sidebar' + ((sidebars.length == 2) ? 's' : ''));
    // Add specific sidebars
    if (sidebars.length == 1) {
      // Find the sidebar name.
      body.addClass('layout-' + sidebars.data('region').replace('_', '-'));
    }

    // Hide empty blocks and regions.
    $(".empty-region, .empty-block").hide();
  }

  var _updateDraggingOperation = function(that, event, ui) {
    if (that === ui.item.parent()[0]) {
      // The destination region cannot be empty anymore.
      $(that).removeClass('empty-region');

      // The origin region may now be empty.
      if (ui.sender && $("[data-block]:not('.empty-block')", ui.sender).length == 0) {
        ui.sender.addClass('empty-region');
      }

      var destinationRegionName = $(that).data('region');
      var originRegionName = ui.sender ? ui.sender.data('region') : destinationRegionName;
      var request = {};

      // Build destination region
      request['region'] = destinationRegionName;
      request['blocks'] = [];
      $("[data-block]", $(that)).each(function () {
        request['blocks'].push($(that).data('block'));
      });

      // Send the changed data to our backend.
      var uniqId = _uniqId();
      $.ajax({
        type: "POST",
        url: Drupal.url('feadmin/callback/blocks'),
        contentType: "application/json",
        data: JSON.stringify(request),
        beforeSend: function (xhr) {
          var notif = $.notify(Drupal.t("Blocks position are saving..."), {
            className: ['wait', uniqId],
            position: 'left bottom'
          });
        },
        success: function (data) {
          $(".notifyjs-bootstrap-" + uniqId).trigger('notify-hide');
          $.notify(data, {
            className: 'success',
            position: 'left bottom'
          });
        },
        error: function (data) {
          $(".notifyjs-bootstrap-" + uniqId).trigger('notify-hide');
          $.notify(Drupal.t("An error has happened: blocks positionning is not saved."), {
            className: 'error',
            position: 'left bottom'
          });
        }
      });
    }
  }

}(Drupal, Backbone, jQuery));
