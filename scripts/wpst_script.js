/* Check if jQuery is loaded and make alert if not  */
document.addEventListener('DOMContentLoaded',function(){
	if (typeof jQuery == 'undefined') {  
		alert("WP Sticker plugins requires jQuery to load");
	}
});


( function( $ ) { 

/* Browser detection */
var ua = navigator.userAgent.toLowerCase();
var check = function(r) {
	return r.test(ua);
};
var DOC = document;
var isStrict = DOC.compatMode == "CSS1Compat";
var isOpera = check(/opr/);
var isChrome = check(/chrome/);
var isWebKit = check(/webkit/);
var isSafari = !isChrome && check(/safari/);
var isSafari2 = isSafari && check(/applewebkit\/4/); // unique to
// Safari 2
var isSafari3 = isSafari && check(/version\/3/);
var isSafari4 = isSafari && check(/version\/4/);
var isIE = !isOpera && check(/msie/);
var isIE7 = isIE && check(/msie 7/);
var isIE8 = isIE && check(/msie 8/);
var isIE6 = isIE && !isIE7 && !isIE8;
var isGecko = !isWebKit && check(/gecko/);
var isGecko2 = isGecko && check(/rv:1\.8/);
var isGecko3 = isGecko && check(/rv:1\.9/);
var isBorderBox = isIE && !isStrict;
var isWindows = check(/windows|win32/);
var isMac = check(/macintosh|mac os x/);
var isAir = check(/adobeair/);
var isLinux = check(/linux/);
var isSecure = /^https/i.test(window.location.protocol);
var isIE7InIE8 = isIE7 && DOC.documentMode == 7;
var isFirefox = navigator.userAgent.toLowerCase().indexOf('firefox') > -1;

var jsType = '', browserType = '', browserVersion = '', osName = '';
var ua = navigator.userAgent.toLowerCase();
var check = function(r) {
	return r.test(ua);
};

if(isWindows){
	osName = 'Windows';

	if(check(/windows nt/)){
		var start = ua.indexOf('windows nt');
		var end = ua.indexOf(';', start);
		osName = ua.substring(start, end);
	}
} else {
	osName = isMac ? 'Mac' : isLinux ? 'Linux' : 'Other';
} 

if(isIE){
	browserType = 'IE';
	jsType = 'IE';

	var versionStart = ua.indexOf('msie') + 5;
	var versionEnd = ua.indexOf(';', versionStart);
	browserVersion = ua.substring(versionStart, versionEnd);

	jsType = isIE6 ? 'IE6' : isIE7 ? 'IE7' : isIE8 ? 'IE8' : 'IE';
} else if (isGecko) {
	var isFF =  check(/firefox/);
	browserType = isFF ? 'Firefox' : 'Others';;
	jsType = isGecko2 ? 'Gecko2' : isGecko3 ? 'Gecko3' : 'Gecko';

	if(isFF){
		var versionStart = ua.indexOf('firefox') + 8;
		var versionEnd = ua.indexOf(' ', versionStart);
		if(versionEnd == -1){
			versionEnd = ua.length;
		}
		browserVersion = ua.substring(versionStart, versionEnd);
	}
} else if(isChrome){
	browserType = 'Chrome';
	jsType = isWebKit ? 'Web Kit' : 'Other';

	var versionStart = ua.indexOf('chrome') + 7;
	var versionEnd = ua.indexOf(' ', versionStart);
	browserVersion = ua.substring(versionStart, versionEnd);
}else{
	browserType = isOpera ? 'Opera' : isSafari ? 'Safari' : '';
}
/* END Browser detection */



/* Main Class */
function WPST() {
	
	/* Construct */
	this.phpDATA = wpst_data;
	/* Define icon button's html */
	this.add_button_html = "<i class='wpst-button wpst-icon-plus-squared' title='Add new note'>";
	this.cancel_button_html = "<i class='wpst-button wpst-icon-cancel-squared' title='Cancel'>";
	this.edit_button_html = "<i class='wpst-button wpst-icon-plus-squared'>";
	this.ok_button_html = "<i class='wpst-button wpst-icon-ok-squared'>";
	this.list_button_html = "<i class='wpst-button wpst-icon-th-list'>";
	this.block_button_html = "<i class='wpst-button wpst-icon-block' title='Hide Notes'>";
	this.show_button_html = "<i class='wpst-button wpst-icon-dot-circled' title='Show Notes'>"
	
	this.stickerHTML = "<div class='resize'><header></header><div class='textarea' contenteditable='true'></div><div class='ajax-loading'><img src='"+wpst_data.plugin_dir+"/scripts/images/ajax-loader.gif'></div></div><i class='wpst-icon-cancel'></i><i class='wpst-icon-ok'></i><div class='wpst-suggestions'></div>";
	this.screenWidth = getViewport("width");
	this.allStickers = new Array();
	this.isParentRelative = false;
	
	var h_position = jQuery("html").css("position"), 
		b_position = jQuery("body").css("position");
	if( b_position == "relative" || b_position == "absolute" || h_position == "relative" || h_position == "absolute" ) {
		this.isParentRelative = true;
	} 
	/* END Construct */
	
	
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
				css: { top: this.menuContainer.offset().top + "px" }
			}).appendTo('body');
		this.allStickers.push( this.currentSticker );
		return this.currentSticker;
	}
	
	this.saveSticker = function( sticker ) {
		sticker.find( '.textarea' ).html( this.parseTextFixer( sticker.find( '.textarea' ).html() ) ); // Replace plain urls with links and improve html
		properties = JSON.stringify( this.getProperties( sticker ) );
		url = document.URL.split('#')[0]; // Get Url without hash parameter
		note = sticker.find(".textarea").html();
		sticker_id = sticker.attr("id");
		userIDs = new Array();
		sticker.find(".textarea input").each(function(index, element){
			userIDs.push( $(element).data('userid') );
		});
		
		sticker.addClass("loading");
		jQuery.ajax({
			type: "POST",
			url: wpst_data.home_url+'/wp-admin/admin-ajax.php',
			data: { sticker_id: sticker_id, properties: properties, url: url, note: note, users: JSON.stringify( userIDs ), action: "wpst_save_sticker" },
			dataType: "html",
			success: function (dataBack) {
				if(dataBack == "OK") {
					sticker.removeClass("loading").addClass("sticked saved");
				}
				else {
					alert( dataBack );
					sticker.removeClass("loading");
				}
			},
			async:true
		});
	}
	
	this.deleteSticker = function( sticker ) {
		if( !sticker.hasClass("saved") ) {
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
				else {
					alert( dataBack );
					sticker.removeClass("loading");
				}
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
		  	sticker.html( this.stickerHTML ).find(".textarea").html( stickers[i].note );
			sticker.find(".resize").css({ width: sticker_properties.width+"px", height: sticker_properties.height+"px" });
			this.bindEvents( sticker );
			this.allStickers.push( sticker );
		}
	}
	
	this.calculateTopFix = function() {  /* Calculate additional top amount to fix jQuery UI position bug while dragging. Cross browser  */
		if( isWebKit && isOpera && this.isParentRelative )
			return jQuery(document).scrollTop() - jQuery("html").position().top;
		else if( isWebKit && isOpera )
			return jQuery(document).scrollTop();
		else if( isWebKit && this.isParentRelative )
			return jQuery(document).scrollTop() - jQuery("html").position().top;
		else if( isWebKit )
			return 0;
		else if( this.isParentRelative )
			return - jQuery("html").position().top;
		else 
			return jQuery(document).scrollTop();
	}
	
	this.parseTextFixer = function( text ) {
		text = text.replace( /&nbsp;/g, " " );
		text =  Autolinker.link( text, {truncate: 55, stripPrefix: false} );
		return text;
	}
	
	this.bindEvents = function( sticker ) {
		sticker.click(function(){
			recentClass( sticker );
		});
		sticker.find(".wpst-icon-ok").click(function(){
			WPST.saveSticker($(this).closest(".wpst-sticker-note"));
		});
		sticker.find(".wpst-icon-cancel").click(function(){
			WPST.deleteSticker($(this).closest(".wpst-sticker-note"));
		});
		sticker.draggable({
			handle: "header",
			scroll: false,
			start: function(event, ui) {
				ui.position.top -= WPST.calculateTopFix();
			},
			drag: function(event, ui) {
				ui.position.top -= WPST.calculateTopFix();
				sticker.removeClass("sticked");
				sticker.attr( "data-from-center", WPST.calcFromCenter( sticker.position().left ) );
			}
		});
		sticker.find(".resize").resizable({
			start: function() { $(this).closest(".wpst-sticker-note").removeClass("sticked"); }
		});
		sticker.find(".textarea").keyup(function(){
			$(this).closest(".wpst-sticker-note").removeClass("sticked");
			WPST.showSuggestions( sticker );
		});
	}
	
	this.showSuggestions = function( sticker ) {
		text = sticker.find( '.textarea' ).html().replace(/&nbsp;/g, ' ');
		sticker.find(".wpst-suggestions").empty();
		if( text.indexOf( " @" ) != -1 || text.indexOf( "@" ) == 0 || text.indexOf( ">@" ) != -1 ) {
			var tag = text.match(/@([a-zA-Z0-9_.\-]+)/)[1];
			tag = tag.replace("@", "" ); 
		}
		if( tag ) {
			jQuery.ajax({
				type: "POST",
				url: wpst_data.home_url+'/wp-admin/admin-ajax.php',
				data: { letters: tag, action: "wpst_get_users" },
				dataType: "text",
				success: function ( dataBack ) {
					sticker.find(".wpst-suggestions").empty();
					var suggestions = JSON.parse( dataBack );
					$.each( suggestions, function( i, user ){
						sticker.find(".wpst-suggestions").append( '<a class="users-tag" data-userid="' + user.ID + '">' + user.display_name + '</a>' );
					});
					sticker.find(".users-tag").click(function(){
						WPST.insertTag( sticker, $(this), tag );
					});
				},
				async:true
			});
		}
	}
	
	this.insertTag = function( sticker, tag, typedTag ) {
		var textarea = sticker.find(".textarea");
		textarea.html( textarea.html().replace( "@" + typedTag, "<input type='button' class='inline-tag' data-userid='" + tag.data("userid") + "' value='" + tag.text() + "'> &nbsp;" ) );
		//this.noteHtmlFix( 'mozilla', textarea ); // Fix note textarea html if needed in some browsers
		sticker.find(".wpst-suggestions").empty();
		placeCaretAtEnd( textarea.get(0) );
	}
	
	this.noteHtmlFix = function( browser, textarea ) {
		if( browser == 'mozilla' ) {
			textarea.find("button").each(function(index, element){
				$(element).replaceWith( "<input type='button' class='inline-tag' data-userid='" + $(element).attr('data-userid') + "' value='" + $(element).text() + "'>" );
			});
		}
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
	$(document).bind('keydown', function(e) {
	  if(e.ctrlKey && (e.which == 83)) {
		e.preventDefault();
		jQuery( ".wpst-sticker-note.recent" ).find( ".wpst-icon-ok" ).click();
		return false;
	  }
	});
	
	/* Create menu container if user has rights to create notes */
	if( wpst_data.wpst_current_caps.wpst_create ) { 
		var wpstMainContainer = WPST.createMenuContainer();
		wpstMainContainer.append( WPST.add_button_html, WPST.block_button_html, WPST.show_button_html );
	}
	
	$("i.wpst-icon-plus-squared").click(function(e) {
		var sticker = WPST.createNewSticker();
		sticker.append( WPST.stickerHTML );
		WPST.bindEvents( sticker );
	});
	
	$("i.wpst-icon-block").click(function(e) {
		$(this).hide();
		$("i.wpst-icon-dot-circled").css('display','inline-block');
		$(".wpst-sticker-note").hide();
	});
	
	$("i.wpst-icon-dot-circled").click(function(e) {
		$(this).hide();
		$("i.wpst-icon-block").css('display','inline-block');
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
});

function recentClass( sticker ) {
	jQuery( ".wpst-sticker-note.recent" ).removeClass( "recent" );
	sticker.addClass( "recent" );
}

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

function placeCaretAtEnd( el ) {
    el.focus();
    if (typeof window.getSelection != "undefined"
            && typeof document.createRange != "undefined") {
        var range = document.createRange();
        range.selectNodeContents(el);
        range.collapse(false);
        var sel = window.getSelection();
        sel.removeAllRanges();
        sel.addRange(range);
    } else if (typeof document.body.createTextRange != "undefined") {
        var textRange = document.body.createTextRange();
        textRange.moveToElementText(el);
        textRange.collapse(false);
        textRange.select();
    }
}

} )( jQuery );