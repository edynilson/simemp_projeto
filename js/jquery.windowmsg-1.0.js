/*
* @Author: Ricardo Órfão
* @Date:   2014-05-04 13:22:10
* @Last Modified by:   Ricardo Órfão
* @Last Modified time: 2014-07-24 14:54:01
*/
/*
 * WindowMsg jquery plugin
 *
 * Copyright (c) 2008 Peter Hulst (sfpeter.com / hollice.com)
 * Dual licensed under the MIT (MIT-LICENSE.txt)
 * and GPL (GPL-LICENSE.txt) licenses.
 *
 * Version 1.0
 */
(function($) {
    $.windowMsgHandlers = [];
    $.initWindowMsg = function() {
        $('body').append($('<form name="windowComm"></form>')
                .append($('<input type="hidden" name="windowCommEvent">'))
                .append($('<input type="hidden" name="windowCommData">'))
                .append($('<input id="myinput" type="button" name="windowCommButton" value="" style="display:none">')));
        $('#myinput').click(function() {
            eventType = $('[name=windowCommEvent]').val();
            data = $('[name=windowCommData]').val();
            for (var i = 0; i < $.windowMsgHandlers.length; i++) {
                h = $.windowMsgHandlers[i];
                if (h.event == eventType) {
                    h.callback.call(null, data);
                    break;
                }
            }
        });
    };
    $.triggerParentEvent = function(event, msg) {
        $.triggerWindowEvent(window.opener, event, msg);
    };
    $.triggerWindowEvent = function(otherWindow, event, msg) {
        if (typeof otherWindow == "object") {
            form = otherWindow.document.forms["windowComm"];
            if (form) {
                form.windowCommEvent.value = event;
                form.windowCommData.value = msg;
                form.windowCommButton.click();
                return true;
            }
        }
        return false;
    }
    $.windowMsg = function(event, callback) {
        $.windowMsgHandlers.push({event: event, callback: callback});
    }
})(jQuery);
