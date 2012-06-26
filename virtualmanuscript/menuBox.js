/*
 * menuBox plugin pour jQuery développé par Mandchou
 * http://www.mandchou.com/
 *
 * Copyright (c) 2009 Charly BELLE
 * Dual licensed under the MIT and GPL licenses.
 * http://docs.jquery.com/License
 *
 * Date: 2010-01-13 13:45:21 -0500 (Wed, 13 Jan 2010)
 * Revision: 1
 */

(function($) {  
$.fn.menuBox = function (options){ // réglages par défaut
	options = jQuery.extend({
		speedIn:200, 
		speedOut:100, 
		menuWi:200,
		align:'horizontal'
},options);	
$.fn.findPos = function() {
       obj = jQuery(this).get(0);
       var curleft = obj.offsetLeft || 0;
       var curtop = obj.offsetTop || 0;
       while (obj = obj.offsetParent) {
                curleft += obj.offsetLeft
                curtop += obj.offsetTop
       }
       return {x:curleft,y:curtop};
   }
this.each(function(){
var _self = $(this);
//var globalWi = parseInt($('html').width());
_self.find('ul').css({width:options.menuWi+'px',position:'absolute'});
_self.find('ul').addClass('ulFirstChild');
_self.find('ul').find('ul').css({marginLeft:options.menuWi+'px'});
_self.find('ul').find('ul').removeClass('ulFirstChild');

if(options.align=='vertical'){
	var firstAlign = parseInt(_self.width());
	var debugAlign = 10;
}

else

{
	var firstAlign = 0;
	var debugAlign = 5;
}

$(this).find('.ulFirstChild').css({marginLeft:firstAlign+'px'});

$(this).find('ul').hide();
$(this).find('li').bind('mouseenter',function(){
var curObj = $(this).find('ul:first');
var globalWi = parseInt($('html').width());
var pos = $(this).findPos();
if((globalWi - pos.x)-options.menuWi < options.menuWi){
	var curMargin = parseInt(curObj.css('marginLeft'));
	if(curMargin !=0){
	curObj.css({marginLeft:'-'+options.menuWi+'px'});	
	}
	
	var diffMargin = (globalWi - pos.x)-options.menuWi;
	
	if((globalWi - pos.x) < options.menuWi){
	$(this).find('.ulFirstChild').css({marginLeft:diffMargin-firstAlign-debugAlign+'px'});
	}	
}
else
{
	var curMargin = parseInt(curObj.css('marginLeft'));
	if(curMargin !=0){
	curObj.css({marginLeft:options.menuWi+'px'});
	$(this).find('.ulFirstChild').css({marginLeft:firstAlign+'px'});
	}
}
curObj.stop();
curObj.css({opacity:1});
curObj.fadeIn(options.speedIn);
});
$(this).find('li').bind('mouseleave',function(){
var curObj = $(this).find('ul:first');
curObj.stop();
curObj.fadeOut(options.speedOut);
});
});
}})(jQuery);

	

