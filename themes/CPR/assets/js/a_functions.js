/*****************************************************************************
    
	1. GENERAL FUNCTIONS
		1.1. PAGE INIT
		1.2. GLOBAL WRAP FUNCTION
		1.3. LINE BREAK CHECK 

	2. LANDING PAGE
		2.1. LANDING INIT

	3. NAV FUNCTIONS
		3.1. LI HEIGHT CALC
		3.2. NAV SHOW / HIDE
		3.3. SHOW / HIDE COLLECTIONS BASED ON CURRENT PAGE
		3.4. TOGGLE COLLECTIONS
		3.5. SET SECONDARY NAV HEIGHT
		3.6. SECONDARY NAV SHOW ON HOME PAGE
		3.7. NEWSLETTER INIT

	4. COLLECTION FUNCTIONS	
		4.1. COLLECITON INIT
		4.2. POSITION COLLECTION IMAGES	
		4.3. FILTER PRODUCTS
		4.4. REDIRECT BOTTOM PRODUCTS
		4.5. LAZYLOAD IMAGES

	5. SINGLE FUNCTIONS
		5.1. SINGLE IMAGE SLIDESHOW
		5.2. SINGLE INFO INIT
		5.3. SELECT SIZES 
		5.4. SINGLE DESCRIPTION TOGGLE

	6. OTHER PAGE FUNCTIONS
		6.1. STYLE BUTTONS
		6.2. MOVE NEWS IMAGES TO RIGHT-HAND COLUMN
		6.3. IFRAMES RESIZE
		6.4. CAMPAIGN IMAGES RESIZE
		6.5. ADD CLASSES TO TERMS SUBTITLES
		6.6. INFO PAGE 

	7. CART / WHOLESALE 
		7.1. CART SHIPPING
		7.2. ORDER FORM INIT

*****************************************************************************/

// 1. GENERAL FUNCTIONS 	

	// 1.1. PAGE INIT

	function pageInit () {
		// ON ALL PAGES
		textWrap();
		newsletterInit(); 

		if ( $("#home").length ) {		
			landingInit();
		}
		if ( $(".collection").length ) {
			filterInit();
		}
		if ( $(".page_collection").length ) {
			imagesPrep();
		}
		if ( $(".single_page").length ) {
			lazySizes.init();
			slideShowInit();
			singleInit();
		}
		if ( $("#loading").length ) {
			pageShow();
		}
		if ( $("#news").length ) {
			newsPrep();
			iframeResize();
		}
		if ( $("#campaign").length ) {
			iframeResize();
			campaignImages();
		}
		if ( $("#terms_and_conditions").length ) {
			termsClasses();
		}
		if ( $("#checkout").length ) {
			shippingCheck();
		}
		if ( $(".wholesale_page").length ) {
			wholesaleInit();
		}
	}

	function pageShow () {
		$("#loading").css( "opacity", "0" );
		$(".page").css( "opacity", "1" );
		// IF NOT ON FRONT OR SINGLE PAGE SHOW 2ND NAV
		if ( !$(".single_page").length && !$("#home").length ) {
			$("#secondary_nav").show();
		}
	}

	// 1.2. GLOBAL WRAP FUNCTION

		// WORD COUNTER
	function wordCount(str) { 
	  return str.split(" ").length;
	}

		// ONE WORD FUNCTION
	function oneWord () {

	}

	function textWrap () {
		console.log("textWrap");
		$(".wrap").each( function(i){
			if ( !$(this).hasClass("wrapped") ) {
				var target;
				if ( $(this).find("a").length ) {
					target = $(this).find("a");
				} else {
					target = $(this);
				}
				var string = target.text();
				var chars = string.split('');
				target.text( chars.join(" ") );
				$(this).addClass("wrapped");				
			}
		});				
	}

	// 1.3. LINE BREAK CHECK — JUST IN INFO FOR THE TIME BEING

	function breakCheck () {

	}

	// 1.4. STOP ANIMATIONS

	function animationStop () {
		// if ( !$("#landing_page").hasClass("slider_active") ) {
	}

// 2. LANDING PAGE

	// 2.1. LANDING PAGE INIT

	function landingInit () {
		console.log("landingInit");
		// RESET WINDOW TO TOP
		$("html,body").animate({
			scrollTop : 0
		}, 10 );
		$("#landing_page").waitForImages( function(){
			$(this).find("li:first-child").addClass("visible");
		});
	}

	// 2.2. LANDING ANIMATE DOWN

	function landingDown() {
		console.log("landingDown");
		// HIDE STORE LINK
		$("#landing_page_access").fadeOut();
		// ANIMATE
		$("#landing_page").animate({
			marginTop: "-100vh"
		}, 1000, function(){
			// SHOW FILTER
			$("#secondary_nav").fadeIn();			
		});
	}

	// 2.3. LANDING SLIDER FORWARD

	var collLoaded = false;
	function landingForward () {
		console.log("landingForward");
		var landingVis = $("#landing_page .visible");
		// CHECK IF NEXT
		if ( landingVis.next().length ) {
			// NEXT SLIDE
			landingVis.removeClass("visible").next().addClass("visible");
			// LOAD COLLECTION
			if ( !collLoaded ) {
				collectionInit();
				collLoaded = true;
			}
		} else {
			// SCROLL DOWN
			landingDown();
		}
	}	

	// 2.4. LANDING SLIDER BACK

	function landingBack () {
		console.log("landingBack");
		var landingVis = $("#landing_page .visible");
		// CHECK IF PREV
		if ( landingVis.prev().length ) {
			landingVis.removeClass("visible").prev().addClass("visible");
		}
	}	

// 3. NAV FUNCTIONS 	

	// 3.1. LI HEIGHT CALC

	function liCalc () {
		console.log( "liCalc" );

	}

	// 3.2. NAV SHOW / HIDE

	function navShow () {
		console.log("navShow");
		// BG IS FIXED AT CURRENT SCROLLTOP POSITION
		var currentPos = $(window).scrollTop();
		// console.log(currentPos);
		$(".page").css({
			"position" : "fixed", 
			"top" : 0 - currentPos
		}).attr( "data-position", currentPos );
		// NAV – ABSOLUTE
		$("#nav").css({
			"position" : "absolute"
		});
		$("#nav_dropdown").css({
			"height" : "100vh"
		}).removeClass("hidden");
		// SHOW CLOSE BUTTON
		$("#secondary_nav ul").fadeOut();
		$("#collection_filter").fadeOut();
		$("#nav_close").fadeIn();
		// BG FADE IN
		$("#nav_bg").css("opacity","1");
	}

	function navHide () {
		console.log("navHide");
		// BG UNFIXED
		$(".page").css({
			"position" : "", 
			"top" : ""
		});
		// SET SCROLLTOP 
		var pagePos = $(".page, .single_page").attr("data-position");
		$(window).scrollTop( pagePos );
		// NAV – UNFIXED
		$("#nav").css({
			"position" : ""
		});
		$("#nav").addClass("hidden");
		$("#nav_dropdown").css("height", "0px").addClass("hidden");
		// HIDE COLLECTIONS
		$(".nav_hidden").each( function(){
			var thisHref = $(this).find("a").data("href");
			$(this).css("height","").find("a").attr("href", "").css("cursor","text");	
		});	
		// HIDE CLOSE BUTTON
		$("#secondary_nav ul").fadeIn();
		$("#nav_close").fadeOut();
		// BG FADE OUT
		$("#nav_bg").css("opacity","0");
	}

	// 3.3. NAV LI COMPRESS / RESET

	function navLiCompress ( navLi ) {		
		// IF WINDOW WIDER THAN 800
		if ( $(window).width() > 800 ) {
			console.log("navLiCompress");
			// IF ONE WORD 
			if ( navLi.hasClass("wrap") ) {
				// GET STRING
				var target;
				if ( navLi.find("a").length ) {
					target = navLi.find("a");
				} else {
					target = navLi;
				}
				var string = target.text();
				// REMOVE SPACES
				string = string.replace(/\s/g, '');
				target.text( string );
			}
			// CENTRE TEXT
			navLi.css({
				"text-align" : "center",
				"text-align-last" : "center"
			});
		}
	}

	function navLiReset ( navLi ) {
		// IF WINDOW WIDER THAN 800
		if ( $(window).width() > 800 ) {
			console.log("navLiReset");
			// IF ONE WORD 
			if ( navLi.hasClass("wrap") ) {
				// GET STRING
				var target;
				if ( navLi.find("a").length ) {
					target = navLi.find("a");
				} else {
					target = navLi;
				}
				var string = target.text();
				// ADD SPACES 
				var chars = string.split('');
				target.text( chars.join(" ") );
			}
			// JUSTIFY TEXT
			navLi.css({
				"text-align" : "",
				"text-align-last" : ""
			});
		}		
	}

	// 3.3. SHOW / HIDE NAV COLLECTIONS DEPENDING ON CURRENT PAGE

	function currentColl () {
		console.log("currentColl");
		
	}

	// 3.4. TOGGLE COLLECTIONS

	function collToggle () {
		console.log("collToggle");
		// GET HEIGHT OF LIs
		var liHeight = $(".nav_collection").height();
		if ( !$(".nav_collection").hasClass("clicked") ) {
			$(".nav_collection").addClass("clicked");
			// MAKE VISIBLE + ACTIVATE LINKS
			$(".nav_hidden").each( function(){
				var thisHref = $(this).find("a").data("href");
				$(this).css({
					"height" : liHeight,
					"margin-top" : "0px"
				});
				$(this).find("a").attr("href", thisHref).css("cursor","");	
			});
		} else {
			$(".nav_collection").removeClass("clicked");
			// HIDE HIDDEN + DEACTIVATE LINKS
			$(".nav_hidden").each( function(){
				var thisHref = $(this).find("a").data("href");
				$(this).css({
					"height" : "",
					"margin-top" : ""
				}).find("a").attr("href", "").css("cursor","text");	
			});
		}	
	}

	// 3.5. SET SECONDARY NAV HEIGHT

	function secNavH () {

	}

	// 3.6. SECONDARY NAV SHOW ON HOME PAGE

	function secondaryNavVis ( scrollPos ) {	

	}

	// 3.7. NEWSLETTER INIT

	function newsletterInit () {	
		console.log("newsletterInit");
		$(".mc-field-group input").attr( "placeholder", "Newsletter" );
	}	


// 4. COLLECTION FUNCTIONS

	// 4.1. COLLECITON INIT

	function collectionInit ( ) {
		console.log("collectionInit");
		imagesPrep();

	}

	// 4.2. SINGLE COLLECTION INIT

	function singleCollCheck ( scrollPos ) {
		if ( $(".single_page").length ) {
			console.log("singleCollCheck");
			var loadLimit = $(".single_product .single_info_wrapper").offset().top - ( $(window).height() * 0.5 );
			var filterLimit = $(".single_product").height() - $(window).height();
			if ( scrollPos > loadLimit && !$(".single_collection").hasClass("loaded") ) {
				console.log("init");
				imagesPrep();
				$(".single_collection").addClass("loaded");
			} 

			if ( scrollPos > filterLimit ) {
				$("#filter_toggle").removeClass("hide_filter");
			} else {
				$("#filter_toggle").addClass("hide_filter");
			} 		
		}

	}

	// 4.2. POSITION COLLECTION IMAGES

	function imagesPrep ( filter ) {
		// IF ON COLLECTION PAGE OR WHOLESALE PAGE, POSITION IMAGES
		if ( $(".collection").length ) {
			// FIRST TIME TEST
			if ( $("body").hasClass("first_time") ) {
				$("body").removeClass("first_time") 
				console.log("imagesPrep exit");
			} else {
				console.log("imagesPrep");
				// HIDE IMAGES
				$(".non_single_product").hide();
				// NEED TO REMOVE PREVIOUSLY ADDED ROWS
				$(".collection_row").each( function(){
					$(this).find(".product").prependTo( $(this).parents("ul") );
				}).remove();

				// DURING FILTERING BOTTOMS ARE NOT HIDDEN
				if ( !filter ) {
					// FILTER OUT ALL BOTTOMS
					$(".bottom").removeClass("selected-product");					
				}

				// FILTER ALL EMPTY PRODUCTS
				$(".product").each( function() {
					if ( !$(this).find(".product_image").length ) {
						$( this ).removeClass("selected-product");	
					}
				});
				var noImages = $(".selected-product").length;
				var total = 0;
				//  recalculated on resize
				var arrayLarge = [3,8,2,5];
				var arrayMid = [3,1,5,2];
				var arraySmall = [3,1,2,1];
				// while loop corresponds to each row
				var i = 0;
				while ( total < noImages ) {
					if ( $(window).width() <= 600 ) {
						number = arraySmall[ i ];
					} else if ( $(window).width() <= 780 ) {
						number = arrayMid[ i ];
					} else {
						number = arrayLarge[ i ];
					}
					// if number of images left is less than array number
					if ( ( noImages - total ) < number ) {
						number = noImages - total;
					}	
					// REMOVE EXISTING CLASSES BEGINNING WITH CHILD-*
					$(".collection .selected-product").slice( total, total+number ).wrapAll("<div class='collection_row'></div>").alterClass("child-*", "child-" + number);
					total += number; 

					if ( i === 3 ) {
						i = 0;
					} else {
						i++;	
					}
				} // END OF WHILE
				// SET IMAGE WIDTH
				$(".collection .selected-product").find(".product_image").each( function(){
					var imgRatio = $(this).attr("width") / $(this).attr("height");
					// WIDTH BASED ON PARENT HEIGHT
					var newWidth = $(this).parents("li").height() * imgRatio;
					$(this).css( "width", newWidth ).addClass("lazyload").attr( "data-ratio", imgRatio );
				});
				// INITIATE LAZYSIZES
				lazySizes.init();
				// SHOW IMAGES			
				$(".collection .selected-product").fadeIn("slow");
			}
		}
	}

	// 4.3. RESIZE IMAGE WIDTH

	function imgWidth () {
		console.log("imgWidth");
		$(".collection .selected-product").find(".product_image").each( function(){
			var imgRatio = $(this).attr("data-ratio");
			var newWidth = $(this).parents("li").height() * imgRatio;
			$(this).css( "width", newWidth );
		});
	}

	// 4.4. FILTER PRODUCTS

	function filterToggle () {
		// CHECK IF TAG NOT ALREADY SELECTED
		var click = $("#filter_toggle"),
			target = $("#collection_filter");
		if ( click.text().toLowerCase() === "filter" ) {
			console.log("filter_toggle");
			if ( !target.is(':visible') ) {
				target.show();
				// IF SMALL SCREEN ADD BG
				if ( $(window).width() <= 500 ) {
					// BG FADE IN
					$("#nav_bg").css("opacity","1");
				}
			} else {
				target.hide();
				// IF SMALL SCREEN REMOVE BG
				if ( $(window).width() <= 500 ) {
					// BG FADE IN
					$("#nav_bg").css("opacity","");
				}
				// CHECK IF ONE OF THE CATEGORIES HAS BEEN SELECTED
				$(".filter").each( function(){
					if ( $(this).hasClass("selected") ) {
						filterClear();	
					}
				});
			}
		}		
	}

	function filterShow () {
		console.log("filterShow");
	}

	function filterInit () {
		console.log("filterInit");
		// LOOP THROUGH PHP GENERATED TAGS
		$("#collection_filter li").each( function(){
			var filterText = $(this).find("a").attr("id");
			if ( !$(".product-tag-" + filterText).length ) {
				$(this).hide();
			} 
		});
	}

	function filterProducts ( click ) {
		console.log("filterProducts");
		// GET TAG OF CLICKED CATEGORY
		var thisTag = click.text().toLowerCase();
		// REPLACE SPACES BY HYPHENS
		thisTag = thisTag.replace(" ","-");
		var thisClass = "product-tag-" + thisTag;

		//$(".product").not(".single_product").hide();
		$(".product").hide();
		$(".selected-product").removeClass("selected-product");	
		// LOOP THROUGH ITEMS ON PAGE
		//$(".product").not(".single_product").each( function(){
		$(".product").each( function(){
			if ( $(this).hasClass( thisClass ) ) {
				$(this).addClass("selected-product");
			}
		});	
	
		$(".selected").removeClass("selected");
		$(".clear_filter").hide();

		// RUN IMAGES PREP WITH FILTER PARAMETER
		imagesPrep( true );			

		click.addClass("selected").next("img").show();
		click.addClass("selected");

		// REPLACE FILTER TOGGLE WITH SELECTED TAG
		$("#filter_toggle").css("cursor","text").text( click.text() ).next("img").show();
		$("#collection_filter").hide();

		// ENSURE FILTER TOGGLE IS VISIBLE ON SINGLE PAGES
		$("#filter_toggle").addClass("filter_vis");
		
		// SCROLL TO TOP OF COLLECTION
		var collTop;
		if ( $(".collection").length ) {
			collTop = $(".collection").offset().top;
		} else {
			collTop = 0;
		}
		
		$("html,body").animate({
			scrollTop: collTop
		}, 500);

		// IF BG VISIBLE
		if ( $("#nav_bg").css("opacity") > 0 ) {
			// BG FADE IN
			$("#nav_bg").css("opacity","");
		}

	}

	function filterClear() {
		console.log("filterClear");
		// RESET 
		//$(".product").not(".single_product").addClass("selected-product");
		$(".product").addClass("selected-product");	
		$(".selected").removeClass("selected");
		$(".clear_filter").hide();	
		$("#filter_toggle").removeClass("filter_vis").css("cursor","pointer").text( "Filter" );

		imagesPrep();

		var collTop;
		if ( $(".collection").length ) {
			collTop = $(".collection").offset().top;
		} else {
			collTop = 0;
		}
		$("html,body").animate({
			scrollTop: collTop
		}, 1000);
	}

	// 4.5. REDIRECT BOTTOM PRODUCTS

	function bottomRedirect () {
	
	}

	// 4.6. LAZYLOAD IMAGES

	function imagesVis ( ) {

	}

// 5. SINGLE FUNCTIONS

	// 5.1. SINGLE IMAGE SLIDESHOW

	function slideShowInit () {
		console.log("slideShowInit");
		$(".single_info_wrapper").each( function(){
			var imgs = $(this).find(".single_images_right");
			var count = imgs.find("img").length;
			var limit;
			if ( $(window).width() <= 800 ) {
				limit = 1;
			} else {
				limit = 2;
			}
			if ( count > limit ) {
				// IF MORE THAN ONE IMAGE ( + LEFT IMG ) : START GALLERY
				imgs.css({
					"cursor" : "pointer"
				}).addClass("gallery"); 
				imgs.find("img").each( function(){
					$(this).wrap("<li></li>");
				});
				// DISABLE FIRST IMAGE ( LEFT IMAGE )
				imgs.find("li:first-child").addClass("invisible");
				// MAKE LAST IMAGE VISIBLE
				imgs.find("li:last-child").addClass("visible");
				imgs.siblings(".gallery_arrow").show();
			}
		});
		// RUN RESIZE TO ADAPT TO SMALL SCREENS
		slideShowResize();
	}

	function slideShowGo ( gallery ) {
		console.log("slideShowGo");
		// CLICK = .GALLERY
		// IF NEXT EXISTS
		if ( gallery.find(".visible").next().length ) {			
			// MAKE NEXT VISIBLE
			gallery.find(".visible").removeClass("visible").next().addClass("visible");
		} else {		
			// GO BACK TO BEGINNING
			gallery.find(".visible").removeClass("visible");
			if ( gallery.find(".invisible").length ) {
				// IF ON SINGLE PAGE
				gallery.find("li:nth-child(2)").addClass("visible");	
			} else {
				gallery.find("li:first-child").addClass("visible");				
			}
		}
	}

	function slideShowResize () {
		console.log("slideShowResize");
		if ( $(window).width() <= 800 ) {
			$(".single_images_right").each( function(){
				$(this).find(".invisible").removeClass("invisible").addClass("was_invisible");
			});	
		} else {
			$(".single_info_right").each( function(){
				$(this).find(".was_invisible").removeClass("was_invisible").addClass("invisible");
			});				
		}
	}

	// 5.2. SINGLE INFO INIT

	function singleInit () {
		// WRAP AMOUNT
		$(".single_info .amount").addClass("wrap");
		// RUN TEXT WRAP
		textWrap();
		// POSITION SIZES OPTIONS
		radioPos();
	}

	// 5.3. SELECT SIZES 

	function radioInit () {
		console.log("radioInit");
	}

	function radioCheck ( click ) {
		console.log("radioCheck");
		// LOOP THROUGH INPUTS
		var selected = false;
		click.parents("tr").find("td").each( function(){
			if ( $(this).find("input").prop( "checked" ) ) {
				selected = true;
				click.parents(".variations_form").find(".single_add_to_cart_button").addClass("button_active");	
			} 
		});
	}

		// SIZES POSITION

	function radioPos ( ) {
		console.log("radioPos");
		// WINDOW WIDTH CHECK
		// if ( $(window).width() > 600 ) {
		// 	$(".info_justified .variations tr").each( function(){
		// 		// LOOP TO GET COUNT AND WIDTH
		// 		var radioCount = 0,
		// 		radioWidth = 0;
		// 		$(this).children().not(".clear").each( function(i){
		// 			radioCount++;
		// 			radioWidth += $(this).width();
		// 		});
		// 		// WIDTH OF WHOLE INFO WRAPPER
		// 		var container = $(this).width();
		// 		var diff = container - radioWidth;
		// 		var diffPerc = Math.floor( diff / ( radioCount - 1 ) / container * 100 );
		// 		console.log( container, radioWidth, diff, diffPerc );
		// 		$(this).find("td").css( "margin-right", diffPerc + "%" );
		// 		// LAST HAS MARGIN REMOVED
		// 		$(this).find("td").eq( radioCount - 1 ).css({
		// 			"position" : "absolute",
		// 			"right" : 0,
		// 			"margin-right" : 0
		// 		});
		// 	});				
		// }
	}

	// 5.4. SINGLE DESCRIPTION TOGGLE

	var descVis = false;
	function descToggle ( click ) {
		console.log("descToggle");
		if ( !descVis ) {
			console.log(497);
			click.siblings(".product_desc").css({
				"height" : "auto",
				"max-height" : 400,
				"padding-bottom" : "16px" 	
			});
			descVis = true;
		} else {
			console.log(503);
			click.siblings(".product_desc").css({
				"height" : "",
				"max-height" : "",
				"padding-bottom" : "" 	
			});
			descVis = false;
		}
	}

	// 5.5. SINGLE INFO HOVER

	function singleInfoOn ( target, load ) {
		if ( $(window).width() > 800 || load ) {
			console.log("singleInfoOn");
			target.css({
				"text-align" : "center",
				"text-align-last" : "center"
			});
			// REMOVE SPACES IN .WRAPS
			target.find(".wrap").each( function(){
				// GET STRING
				var wrap;
				if ( $(this).find("a").length ) {
					wrap = $(this).find("a");
				} else {
					wrap = $(this);
				}
				var string = wrap.text();
				// REMOVE SPACES
				string = string.replace(/\s/g, '');
				wrap.text( string );
			});
			// SIZES
			target.find(".variations td").css("margin-right", "");
			target.find(".variations td:last-child").css({
				"position": "relative"
			});
		}
	}

	function singleInfoOff ( target ) {
		if ( $(window).width() > 800 ) {
			console.log("singleInfoOff");
			target.css({
				"text-align" : "",
				"text-align-last" : ""
			});
			// REMOVE SPACES IN .WRAPS
			target.find(".wrap").each( function(){
				// GET STRING
				var wrap;
				if ( $(this).find("a").length ) {
					wrap = $(this).find("a");
				} else {
					wrap = $(this);
				}
				var string = wrap.text();
				// ADD SPACES
				var chars = string.split('');
				wrap.text( chars.join(" ") );
			});
			// SIZES
			radioPos();
		}
	}

// 6. OTHER PAGE FUNCTIONS

	// 6.1. STYLE BUTTONS

	function buttonResize () {
	
	}

	// 6.2. MOVE NEWS IMAGES TO RIGHT-HAND COLUMN

	function newsPrep () {		
		console.log("newsPrep");
		$(".news_post").each( function(){
			$(this).find("img").appendTo( $(this).find("#news_images") );
			// WAIT UNTIL IMAGES HAVE LOADED
			$(this).find("#news_images").waitForImages(function() {
			    $(this).prev("#news_text").css( "min-height", $(this).find("img").height() ); 
			});			
		});		
	}

	// 6.3. IFRAMES RESIZE

	function iframeResize () {		
		console.log("iframeResize");
		$("iframe").each( function(){
			var thisR = $(this).attr("width") / $(this).attr("height");
			var newH = $(this).width() / thisR;
			var maxH = $(window).height() * 0.8;
			console.log( 868, maxH, maxH * thisR );
			// IF TALLER THAN PARENT
			if ( newH > maxH ) {
				$(this).css({
					"height" : maxH,
					"width" : maxH * thisR
				});	
			} else {
				$(this).css({
					"height" : newH,
					"width" : "" 	
				});	
			}

			// console.log( $(this).width(), thisR );
			// RESIZE PARENT 
			$(this).parents(".news_content").css( "min-height", newH );
			// IF ON CAMPAIGN PAGE RESIZE IMAGE FRAME
			if ( $(".campaign_images").length ) {
				// RUN IMAGE RESIZE FUNCTION
				campaignImages();
			}
		});					
	}

	// 6.4. CAMPAIGN IMAGES RESIZE

	function campaignImages () {
		console.log("campaignImages");
		var ratios = [];
		$(".campaign_photos img").each( function(){
			// GET RATIOS
			var thisRatio = $(this).attr("width") / $(this).attr("height");
			if ( !isNaN(thisRatio) ) {
				ratios.push( thisRatio );
			}
			if ( $(this).attr("width") > $(this).attr("height") ) {
				// LANDSCAPE
				$(this).css({
					"width" : "100%",
					"height" : "auto"
				});
			} else {
				// PORTRAIT
				$(this).css({
					"width" : "auto",
					"height" : "100%"
				});	
			}		
		});
		// GET SMALLEST RATIO
		Array.min = function( array ){
		    return Math.min.apply( Math, array );
		};
		var finalRatio = Array.min(ratios);
		var finalH = $(".campaign_photos").width() * finalRatio;
		if ( finalH > $(window).height() * 0.8 ) {
			// IF IMAGES TOO TALL FOR SCREEN
			$(".campaign_images").css({
				"height" : $(window).height() * 0.8,
				"width" : $(window).height() * 0.8 / finalRatio
			});
		} else {
			$(".campaign_images").css({
				"height" : finalH,
				"width" : ""
			});
		}
		
		// INIT CAMPAIGN SLIDESHOW
		$(".campaign_images li").eq(0).addClass("visible");
	}

	// 6.5. ADD CLASSES TO TERMS SUBTITLES

		// WORD COUNTER
	function wordCount(str) { 
	  return str.split(" ").length;
	}

	function termsClasses () {
		console.log("termsClasses");
		// CHECK IF ONE WORD
		$("strong").each( function(){
			var string = $(this).text().trim();
			if ( wordCount(string) === 1 ) {
				$(this).addClass("wrap");	
			}		
		});
		// REINITIALISE TEXT WRAP
		textWrap();
	}

	// 6.6. INFO PAGE 

	function infoFix () {
		if ( $("#info").length ) {
			console.log("infoFix");
				
		}
	}

// 7. WHOLESALE ORDER FORM

	// 7.1. CART SHIPPING

	function shippingCheck () {
		// console.log("shippingCheck");
		// console.log($("#shipping_method").find("#shipping_method_0_free_shipping").length);
		// switch ( true ) {
		// 	case $("#shipping_method").find("#shipping_method_0_free_shipping").length > 0 :
		// 		console.log("free_shipping");
		// 		$("#shipping_method").find("#shipping_method_0_free_shipping").prop( "checked", "checked" ).parent("li").show();
		// 		break;
		// 	case $("#shipping_method").find("#shipping_method_0_local_delivery").length > 0 :
		// 		console.log("germany");
		// 		$("#shipping_method").find("#shipping_method_0_local_delivery").prop( "checked", "checked" ).parent("li").show();
		// 		break;
		// 	case $("#shipping_method").find("#shipping_method_0_international_delivery").length > 0 :
		// 		console.log("europe");
		// 		$("#shipping_method").find("#shipping_method_0_international_delivery").prop( "checked", "checked" ).parent("li").show();
		// 		break;
		// }
	}

	// 7.1. WHOLESALE INIT

	function wholesaleInit () {
		console.log("wholesaleInit");
		$(".variable_price .price").each( function(){
			$(this).find("del .amount").prepend("RRP: ").unwrap();
		});
		// INITIATE LAZYSIZES
		console.log(983);
		lazySizes.init();
	}

	// 7.2. WHOLESALE OTHER COLOURS

		// HOVER

	function wsaleOtherColoursHover ( click ) {
		// console.log("wsaleOtherColoursHover");
		// GET TARGET TITLE
		var targetText = click.find("p").text();
		// INJECT INTO PLACEHOLDER
		click.parents(".wholesale_product_title").find(".wholesale_other_colours_title").text( targetText );
	}

	function wsaleOtherColoursUnhover( click ) {
		// console.log("wsaleOtherColoursUnhover");
		// EMPTY PLACEHOLDER
		click.parents(".wholesale_product_title").find(".wholesale_other_colours_title").text( "");
	}

		// CLICK

	function wsaleOtherColoursClick ( click ) {
		console.log("wsaleOtherColoursClick");
		// GET TARGET ID
		var targetId = click.data("id");
		// GET OFFSET OF TARGET
		var targetOffset = $("#" + targetId).offset().top;
		var padding = parseInt ( $(".page").css("padding-top") );
		console.log(973, padding);
		if ( targetOffset > 0 ) {
			console.log( 884, targetOffset );
			$("html,body").animate({
				scrollTop : targetOffset - padding
			}, 1000 );
		}
	}

	// 7.3. WHOLESALE FILTER TOGGLE

	function wsaleFilterToggle ( click ) {
		console.log("wsaleFilterToggle");
		var wrapper = $("#search_wrapper");
		var termsH = $("#wsale_filter_terms").height();
		var termsPadding = 80;
		if ( $(window).width() < 980 && $(window).width() > 500 ) {
			termsPadding = 140;
		} 
		console.log( $(window).width(), termsH, termsPadding );
		// CHECK IF HIDDEN
		if ( wrapper.hasClass("hidden") ) {
			console.log("show");
			wrapper.css({
				"height" : termsH + termsPadding
			});
			wrapper.find("#wsale_filter_terms").css({
				"padding-top": "0"
			});
			wrapper.removeClass("hidden");
		} else {
			console.log("hide");
			wrapper.css({
				"height" : ""
			});
			wrapper.find("#wsale_filter_terms").css({
				"padding-top": ""
			});
			wrapper.addClass("hidden");
		}
	}	

	// 7.4. WHOLESALE FILTER TOGGLE

	function wsaleFilter ( click ) {
		console.log("wsaleFilter");
		// GET TARGET
		var target = click.data("target");
		// GET CLASS
		if ( click.hasClass("wsale_term_cat") ) {
			// CATEGORY
				// GET AVAILABLE OPTIONS FROM DROPDOWN
			var dropdown = $("#wwof_product_search_category_filter");
			dropdown.find("option").each( function(){
				var optionName = $(this)[0].innerHTML;
				var optionSlug = optionName.toLowerCase().split(" ").join("-");
				if ( target === optionSlug ) {
					// GET VAL
					var thisVal = $(this)[0].index;
					// REMOTELY SELECT IN DROPDOWN
					dropdown.prop( "selectedIndex", thisVal );
					// CLEAR TEXT FIELD
					$("#wwof_product_search_form").val("");
				}
			});
		} else if ( click.hasClass("wsale_term_tag") ) {
			// TAG
			// PLURAL TO SINGULAR
			var lastLetter = target.slice(-1);
			var secondLastLetter = target.slice(-2);
			if ( secondLastLetter === "es" ) {
				target = target.substring(0, target .length - 2);
			} else if ( lastLetter === "s" ) {
				target = target.substring(0, target .length - 1);
			}
			// REMOVE HYPHENS FOR TSHIRTS		
			if ( target.indexOf("-") > -1 ) {
				target = target.split("-").join("");
			}
				// INJECT TEXT IN SEARCH FIELD
			$("#wwof_product_search_form").val( target );
		}
		// TRIGGER CLICK
		$("#wwof_product_search_btn")[0].click();

		// HIGHLIGHT SELECTED TAG
		$("#wsale_filter_terms a").css( "border-bottom", "" );
		click.css( "border-bottom", "2px solid black" );

	}	

	function wsaleFilterReset () {
		console.log("wsaleFilterReset");
		// CLEAR SELECTED TERMS
		$("#wsale_filter_terms a").css( "border-bottom", "" );
		// CLEAR TEXT FIELD
		$("#wwof_product_search_form").val("");
	}

	// 7.5. QUANTITY RESET

	// function quantityReset () {
	// 	console.log("quantityReset");

 //        $(".quantity").each( function(){

 //            console.log( $(this).find(".input-text").attr("value") );
 //            // $(this).find(".input-text").reset();

 //        });
	// }




