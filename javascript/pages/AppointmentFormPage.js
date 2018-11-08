(function($){
	"use strict";
	window._apptPage = window._apptPage || {};
	window._apptPage.blockWeekends = window._apptPage.blockWeekends || false;
	window._apptPage.disabledDays = window._apptPage.disabledDays || [];
	window._apptPage.disabledWeekdays = window._apptPage.disabledWeekdays || [];
	$(document).ready(function(){
		$('input.date').datepicker({
			minDate: 0,
			beforeShowDay: function(date){
				if(window._apptPage.blockWeekends){
					return window._apptPage.noWeekendsOrBlocked(date);
				}
				return window._apptPage.blockedDays(date);
			}
		});
	});
	window._apptPage.blockedDays = function(date) {
		var m = date.getMonth(), d = date.getDate(), y = date.getFullYear(),wkdy = date.getDay();
		// check blocked dates
		for (var i = 0; i < window._apptPage.disabledDays.length; i++) {
			if($.inArray((m+1) + '-' + d + '-' + y,window._apptPage.disabledDays) !== -1 || $.inArray((wkdy+1),window._apptPage.disabledWeekdays) !== -1 || new Date() > date) {
				return [false];
			}
		}
		return [true];
	};
	window._apptPage.noWeekendsOrBlocked = function(date) {
		var noWeekend = jQuery.datepicker.noWeekends(date);
		return noWeekend[0] ? window._apptPage.blockedDays(date) : noWeekend;
	};
}(jQuery));