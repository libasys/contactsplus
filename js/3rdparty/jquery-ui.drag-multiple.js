/*! Drag Multiple Plugin - v0.1.1 - 2014-05-14
* https://github.com/javadoug/jquery.drag-multiple
* Copyright (c) 2014 Doug Ross; Licensed MIT */
/*globals jQuery */
(function ($) {

    "use strict";

    var options = {

        // allow consumer to specify the selection
        items: function getSelectedItems() {
            return $(".ui-draggable.ui-selected");
        },

        // allow consumer to cancel drag multiple
        beforeStart: function beforeDragMultipleStart() {
            // make sure target is selected, otherwise deselect others
            if (!(this.is('.ui-draggable') && this.is('.ui-selected'))) {
                $(".ui-draggable").removeClass('ui-selected');
                return false;
            }
        },

        // notify consumer of drag multiple
        beforeDrag: $.noop,

        // notify consumer of drag multiple stop
        beforeStop: $.noop

    };

    function preventDraggableRevert() {
        return false;
    }

    /** given an instance return the options hash */
    function initOptions(instance) {
        return $.extend({}, options, instance.options.multiple);
    }

    function callback(handler, element, jqEvent, ui) {
        if ($.isFunction(handler)) {
            return handler.call(element, jqEvent, ui);
        }
    }

    function notifyBeforeStart(element, options, jqEvent, ui) {
        return callback(options.beforeStart, element, jqEvent, ui);
    }

    function notifyBeforeDrag(element, options, jqEvent, ui) {
        return callback(options.beforeDrag, element, jqEvent, ui);
    }

    function notifyBeforeStop(element, options, jqEvent, ui) {
        return callback(options.beforeStop, element, jqEvent, ui);
    }

    $.ui.plugin.add("draggable", "multiple", {

        /** initialize the selected elements for dragging as a group */
        start: function (ev, ui) {

            var element, instance, selected, options;

            // the draggable element under the mouse
            element = this;

            // the draggable instance
            instance = element.data('draggable') || element.data('ui-draggable');

            // initialize state
            instance.multiple = {};

            // the consumer provided option overrides
            options = instance.multiple.options = initOptions(instance);

            // the consumer provided selection
            selected = options.items();

            // notify consumer before starting
            if (false === notifyBeforeStart(element, options, ev, ui)) {
                options.dragCanceled = true;
                return false;
            }

            // cache respective origins
            selected.each(function () {
                var position = $(this).position();
                $(this).data('dragmultiple:originalPosition', $.extend({}, position));
            });

            // TODO: support the 'valid, invalid and function' values
            //  currently only supports true
            // disable draggable revert, we will handle the revert
            instance.originalRevert = options.revert = instance.options.revert;
            instance.options.revert = preventDraggableRevert;
        },

        // move the selected draggables
        drag: function (ev, ui) {

            var element, instance, options;

            element = this;
            instance = element.data('draggable') || element.data('ui-draggable');
            options = instance.multiple.options;

            if (options.dragCanceled) {
                return false;
            }

            notifyBeforeDrag(element, options, ev, ui);

            // check to see if consumer updated the revert option
            if (preventDraggableRevert !== instance.options.revert) {
                options.revert = instance.options.revert;
                instance.options.revert = preventDraggableRevert;
            }

            // TODO: make this as robust as draggable's positioning
            options.items().each(function () {
                var origPosition = $(this).data('dragmultiple:originalPosition');
                // NOTE: this only works on elements that are already positionable
                $(this).css({
                    top: origPosition.top + (ui.position.top - ui.originalPosition.top),
                    left: origPosition.left + (ui.position.left - ui.originalPosition.left)
                });
            });

        },

        stop: function (ev, ui) {

            var element, instance, options;

            element = this;
            instance = element.data('draggable') || element.data('ui-draggable');
            options = instance.multiple.options;

            if (options.dragCanceled) {
                return false;
            }

            notifyBeforeStop(element, options, ev, ui);

            // TODO: mimic the revert logic from draggable
            if (options.revert === true) {
                options.items().each(function () {
                    var position = $(this).data('dragmultiple:originalPosition');
                    $(this).css(position);
                });
            }

            // clean up
            options.items().each(function () {
                $(this).removeData('dragmultiple:originalPosition');
            });

            // restore orignal revert setting
            instance.options.revert = instance.originalRevert;

        }
    });

}(jQuery));