/* Check if jQuery is loaded and make alert if not  */
document.addEventListener('DOMContentLoaded',function(){
	if (typeof jQuery == 'undefined') {  
		alert("WP Sticker plugins requires jQuery to load");
	}
});


( function( $ ) {

/* Main Class */
function WPST() {
	this.phpDATA = wpst_data;
	/* Define icon button's html */
	this.add_button_html = "<i class='wpst-button icon-plus-squared'>";
	this.cancel_button_html = "<i class='wpst-button icon-cancel-squared'>";
	this.edit_button_html = "<i class='wpst-button icon-plus-squared'>";
	this.ok_button_html = "<i class='wpst-button icon-ok-squared'>";
	this.list_button_html = "<i class='wpst-button icon-th-list'>";
	this.block_button_html = "<i class='wpst-button icon-block'>";
	
	this.stickerHTML = "<div class='resize'><header></header><textarea name='sticker_text'></textarea><div class='ajax-loading'><img src='"+wpst_data.plugin_dir+"/scripts/images/ajax-loader.gif'></div></div><i class='icon-cancel'></i><i class='icon-ok'></i>";
	this.screenWidth = getViewport("width");
	this.allStickers = new Array();
	
	/* Functions */
	this.createMenuContainer = function() {
		 this.menuContainer = jQuery('<div/>', {
				id: 'wpst-note-container-main',
				class: 'wpst-note-container'
			}).appendTo('body');
		return this.menuContainer;
	}
	
	this.createNewSticker = function() {
		this.currentSticker = jQuery('<div/>', {
				id: 'note-' + Math.random().toString(36).substr(2),
				class: 'wpst-sticker-note',
				css: {top: this.menuContainer.offset().top + $("html").position().top + "px" }
			}).appendTo('body');
		this.allStickers.push( this.currentSticker );
		return this.currentSticker;
	}
	
	
	
	this.saveSticker = function( sticker ) {
		properties = JSON.stringify( this.getProperties( sticker ) );
		url = $(location).attr('href');
		note = sticker.find("textarea").val();
		sticker_id = sticker.attr("id");
		//console.log( properties );
		
		sticker.addClass("loading");
		jQuery.ajax({
			type: "POST",
			url: wpst_data.home_url+'/wp-admin/admin-ajax.php',
			data: { sticker_id: sticker_id, properties: properties, url: url, note: note, action: "wpst_save_sticker" },
			dataType: "text",
			success: function (dataBack) {
				if(dataBack == "OK")
					sticker.removeClass("loading").addClass("sticked saved");
				else
					alert("Something wrong with ajax :(");
			},
			async:true
		});
	}
	
	this.deleteSticker = function( sticker ) {
		if( !sticker.hasClass("saved") )
		{
			sticker.remove();
			return true;
		}
		sticker_id = sticker.attr("id");
		sticker.addClass("loading");
		jQuery.ajax({
			type: "POST",
			url: wpst_data.home_url+'/wp-admin/admin-ajax.php',
			data: { sticker_id: sticker_id, action: "wpst_delete_sticker" },
			dataType: "text",
			success: function (dataBack) {
				if(dataBack == "OK")
					sticker.remove();
			},
			async:true
		});
	}
	
	this.getProperties = function( sticker ) {
		var properties = {};
		properties['top'] = sticker.position().top;
		properties['from_center'] = this.calcFromCenter( sticker.position().left );
		properties['width'] = sticker.find(".resize").width();
		properties['height'] = sticker.find(".resize").height();
		return properties;
	}
	
	this.getLeftPercentValue = function( left ) {
		return ( left / this.screenWidth ) * 100;
	}
	
	this.createSavedStickers = function( stickersJSON ) {
		stickers = JSON.parse( stickersJSON );
		for (var i in stickers) {
			sticker_properties = JSON.parse( stickers[i].properties );
			var from_left = this.calcLeft( sticker_properties.from_center );
			sticker = jQuery('<div/>', {
				id: stickers[i].sticker_id,
				class: 'wpst-sticker-note sticked saved',
				"data-from-center": sticker_properties.from_center,
				css: { "top" : sticker_properties.top+"px", "left" : from_left+"px", "position" : "absolute" }
			}).appendTo('body');
		  	sticker.html( this.stickerHTML ).find("textarea").val( stickers[i].note );
			sticker.find(".resize").css({ width: sticker_properties.width+"px", height: sticker_properties.height+"px" });
			this.bindEvents( sticker );
			this.allStickers.push( sticker );
		}
	}
	
	this.bindEvents = function( sticker ) {
		sticker.find(".icon-ok").click(function(){
			WPST.saveSticker($(this).closest(".wpst-sticker-note"));
		});
		
		sticker.find(".icon-cancel").click(function(){
			WPST.deleteSticker($(this).closest(".wpst-sticker-note"));
		});
		
		sticker.draggable({
			handle: "header",
			start: function(event, ui) {
				ui.position.top -= $(document).scrollTop() - $("html").position().top + parseInt( $("html").css("paddingTop") );
			},
			drag: function(event, ui) {
				ui.position.top -= $(document).scrollTop() - $("html").position().top + parseInt( $("html").css("paddingTop") );
				sticker.removeClass("sticked");
				sticker.attr( "data-from-center", WPST.calcFromCenter( sticker.position().left ) );
			}
		});
		
		sticker.find(".resize").resizable({
			start: function() { $(this).closest(".wpst-sticker-note").removeClass("sticked"); }
		});
		
		sticker.find("textarea").keyup(function(){
			$(this).closest(".wpst-sticker-note").removeClass("sticked");
		});
	}
	
	this.calcLeft = function( from_center ) {
		return (this.screenWidth / 2) - from_center;
	}
	
	this.calcFromCenter = function( from_left ) {
		return (this.screenWidth / 2) - from_left;
	}

}
var WPST = new WPST();

	jQuery(document).ready(function(e) {
		var wpstMainContainer = WPST.createMenuContainer();
		wpstMainContainer.append( WPST.add_button_html, WPST.block_button_html, WPST.list_button_html );
		
		$("i.icon-plus-squared").click(function(e) {
			var sticker = WPST.createNewSticker();
			sticker.append( WPST.stickerHTML );
			WPST.bindEvents( sticker );
		});
		
		$("i.icon-block").click(function(e) {
			$(".wpst-sticker-note").hide();
		});
		
		$("i.icon-th-list").click(function(e) {
			$(".wpst-sticker-note").show();
		});

		/* Create saved stickers from DB */
		WPST.createSavedStickers( WPST.phpDATA.stickers );
	});

jQuery(window).resize(function(e) {
    WPST.screenWidth = getViewport("width");
	for (var i in WPST.allStickers) {
		WPST.allStickers[i].css({left: WPST.calcLeft( WPST.allStickers[i].attr("data-from-center") )+"px" });
	}
	//console.log(WPST.allStickers);
});


/* Function to get actuall viewpor width and height. Cross different browsers */
function getViewport(what)
{
	 var viewportwidth;
	 var viewportheight;
	 // the more standards compliant browsers (mozilla/netscape/opera/IE7) use window.innerWidth and window.innerHeight
	 if (typeof window.innerWidth != 'undefined') {
		  viewportwidth = window.innerWidth,
		  viewportheight = window.innerHeight
	 }
	// IE6 in standards compliant mode (i.e. with a valid doctype as the first line in the document)
	 else if (typeof document.documentElement != 'undefined'
		 && typeof document.documentElement.clientWidth !=
		 'undefined' && document.documentElement.clientWidth != 0) {
		   viewportwidth = document.documentElement.clientWidth,
		   viewportheight = document.documentElement.clientHeight
	 }
	 // older versions of IE 
	 else {
		   viewportwidth = document.getElementsByTagName('body')[0].clientWidth,
		   viewportheight = document.getElementsByTagName('body')[0].clientHeight
	 }
	 if(what == "width")
	 	return (viewportwidth);
	 else if(what == "height")
	 	return (viewportheight);
	 else if(!what)
	 	return(viewportwidth+"x"+viewportheight);
	//-->
}

} )( jQuery );