/*****************************************************************************
    
	1. FUNCTIONS
		1.1. GLOBAL WRAP FUNCTION
		1.2. NAV SHOW / HIDE
		1.3. SCROLL DETECT
		1.4. TOGGLE COLLECTIONS
		1.5. POSITION COLLECTION IMAGES
		1.6. STYLE BUTTONS
		1.7. MOVE NEWS IMAGES TO RIGHT-HAND COLUMN
		1.8. SINGLE IMAGE SLIDESHOW
		1.9. FILTER PRODUCTS

*****************************************************************************/

	// 1.1. GLOBAL WRAP FUNCTION

		// WORD COUNTER
	function wordCount(str) { 
	  return str.split(" ").length;
	}

		// ONE WORD FUNCTION
	function oneWord () {
		$(".last_word").each( function(){
			// console.log( $(this).text() );
			if ( $(this).has("a").length ) {
				// target is child
				var elmt = $(this).find("a");
			} else {
				var elmt = $(this);
			}	
			elmt.css("text-align","center").stretch_text();	
		});
	}

	var liH;
	function textWrap () {
		console.log("textWrap");
		$(".wrap").each( function(){
			// CHECK IF THIS CONTAINS A TAG
			if ( $(this).has("a").length ) {
				// console.log( $(this).text(), " link" );
				elmt = $(this).find("a");
			} else {
				// console.log( $(this).text() );
				elmt = $(this);
			}

			var txt = elmt.text().trim();
			// console.log(txt);
			var noWords = wordCount( txt );
			if ( noWords === 1 ) {
				// ONE WORD
				elmt.wrapInner("<span class='last_word'></span>");
				$(this).css({
					"text-align" : "center"
					//"text-align-last" : "center"
				});
			} else {
				// MULTIPLE WORDS
					// IF 3 WORDS AND NOT IN NAV
				if ( noWords === 3 && !$(this).hasClass("no_break") ) {
					// REMOVE LAST WORD
					var wordArray = txt.split(" ");
					var lastWord = wordArray[2];
					var lastIndex = txt.lastIndexOf(" ");
					txt = txt.substring( 0, lastIndex );
					elmt.html(txt + " <span class='last_word'>" + lastWord + "</span>");
					// lastWord.wrap("<span class='last_word'></span>").appendTo( $(this) );
				} else {
					elmt.widowFix();										
				}				
			}

		});
		// ONE WORD LOOP
		oneWord();
		// SET LI HEIGHT
		liCalc();
	}

	// 1.XX. CHECK IF LINE BREAK — JUST IN INFO FOR THE TIME BEING

	function breakCheck () {
		$("#info .wrap").each( function(){
			// CHECK IF MORE THAN ONE WORD
			var thisTxt = $(this).text().trim();
			var noWords = wordCount( thisTxt );
			if ( noWords > 1 ) {
				// GET CURRENT HEIGHT + FONT HEIGHT
				var thisH = $(this).height();
				var fontH = parseFloat ( $(this).css("font-size") );
				// IF 2 FONT HEIGHTS CAN FIT
				if ( thisH > ( 2 * fontH ) ) {
					// console.log(thisH, fontH, thisTxt);
					// IF 2 WORDS: WRAP EACH WORD IN LAST-WORD SPAN					
					if ( noWords === 2 ) {
						// $(this).find("span").unwrap();
						$(this).lettering("words");
						$(this).find("span").addClass("last_word");
					}
				}	
			}		
		});	
		// ONE WORD LOOP
		oneWord();	
	}

	// 1.XX. LI HEIGHT CALC

	function liCalc () {
		// DEFINE LI HEIGHT
		liH = parseInt ( $("#nav_home").css("font-size") ) + 18;
		//console.log( liH );
		$("#nav li").not(".nav_hidden").css("height", liH);		
	}

	// 1.2. NAV SHOW / HIDE

	function navShow () {
		console.log("navShow");

		// BG IS FIXED AT CURRENT SCROLLTOP POSITION
		var currentPos = $(window).scrollTop();
		console.log(currentPos);
		$(".page").css({
			"position" : "fixed", 
			"top" : 0 - currentPos
		}).attr( "data-position", currentPos );

		// NAV – ABSOLUTE

		$("#nav").css({
			"position" : "absolute"
		});


		$("#nav").removeClass("hidden");
		$("#nav_dropdown").css({
			// "height" : (liH * 5) + 12
			"height" : "100vh"
		});
	
		// SHOW CLOSE BUTTON
		$("#secondary_nav ul").fadeOut();
		$("#collection_filter").fadeOut();
		$("#nav_close").fadeIn();

		// BG DROPDOWN
		$("#nav_bg").css("height","100vh");
			// + FADE-IN
		$("#nav_bg_top").css("opacity","1");

	}

	// $("#nav_dropdown").css("height", 5 * liH);
	function navHide () {
		console.log("navHide");

		// BG UNFIXED
		$(".page").css({
			"position" : "", 
			"top" : ""
		});
		// SET SCROLLTOP 
		var pagePos = $(".page").attr("data-position");
		$(window).scrollTop( pagePos );

		// NAV – FIXED
		$("#nav").css({
			"position" : ""
		});

		$("#nav").addClass("hidden");
		$("#nav_dropdown").css("height", "0px");
		// hide collections
		$(".nav_hidden").each( function(){
			var thisHref = $(this).find("a").data("href");
			$(this).css("height","").find("a").attr("href", "").css("cursor","text");	
		});
		
		// HIDE CLOSE BUTTON
		$("#secondary_nav ul").fadeIn();
		$("#nav_close").fadeOut();

		// BG DROP-UP
		$("#nav_bg").css("height","0vh");
			// + FADE-OUT
		setTimeout( function(){
			$("#nav_bg_top").css("opacity","0");
		}, 800);

	}

	// 1.3. NAV HIDE ON SCROLL

	var lastScrollTop = 0;
	function scrollDetect () {
		var current = $(this).scrollTop();
		// console.log(current, lastScrollTop);
	   	if (current > (lastScrollTop + 100) ){
	       // navHide();
	   	} 
	   	lastScrollTop = current;
	}

	// 1.4. TOGGLE COLLECTIONS

	function collToggle ( main ) {
		console.log("collToggle");
		var colls;
		$("#nav_dropdown").css("height", "auto");
		if ( !$(".nav_collection").hasClass("clicked") && main !== "main" ) {
			console.log(165);
			// get number of collections
			var colls = parseInt( $(".nav_collection").attr("data-length") );
			$(".nav_collection").addClass("clicked");
			// make visible +
			// activate links for .nav_hidden, not for current
			$(".nav_hidden").each( function(){
				var thisHref = $(this).find("a").data("href");
				$(this).css("height", liH).find("a").attr("href", thisHref).css("cursor","");	
			});
		} else {
			console.log(176);
			colls = 1;
			$(".nav_collection").removeClass("clicked");
			// hide hidden
			// deactivate links
			$(".nav_hidden").each( function(){
				var thisHref = $(this).find("a").data("href");
				$(this).css("height","").find("a").attr("href", "").css("cursor","text");	
			});
		}	
		setTimeout( function(){
			$("#nav_dropdown").css("height", liH * ( colls + 4 ) );
		}, 1000);
	}

	// 1.5. POSITION COLLECTION IMAGES

	function imagesPrep () {
		// IF ON COLLECTION PAGE, POSITION IMAGES
		if ( $(".page_collection").length ) {
			// FIRST TIME TEST
			if ( $("body").hasClass("first_time") ) {
				$("body").removeClass("first_time") 
				console.log("imagesPrep exit");
			} else {
				console.log("imagesPrep");
				// HIDE IMAGES
				$(".page_collection li").hide(); // IN CSS

				// NEED TO REMOVE PREVIOUSLY ADDED ROWS
				$(".collection_row").each( function(){
					$(this).find(".product").prependTo( $(this).parents("ul") );
				}).remove();

				var noImages = $(".selected-product").length;
				var total = 0;
				/* recalculated on resize */
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
					$(".selected-product").slice( total, total+number ).wrapAll("<div class='collection_row'></div>").alterClass("child-*", "child-" + number);
					total += number; 

					if ( i === 3 ) {
						i = 0;
					} else {
						i++;	
					}
				} // end of while	

				// SHOW IMAGES
				
				$(".page_collection .selected-product").fadeIn("slow");
			}

		}
	}

	// 1.6. STYLE BUTTONS

	function buttonResize () {
		$("a.button, a.shipping-calculator-button").each( function(){
	 		// var thisW = $(this).width();
			$(this).parent().addClass("button_wrapper").css(
				"max-width", $(this).width()
			);
		});		
	}

	// 1.7. MOVE NEWS IMAGES TO RIGHT-HAND COLUMN

	function newsPrep () {
		if ( $("#news").length ) {
			console.log("newsPrep");
			$(".news_text").each( function(){
				$(this).find("img").appendTo( $(this).next(".news_images") )
			});		
		}
	}

	// 1.8. SINGLE IMAGE SLIDESHOW

	function slideShowInit () {
		
		$(".single_additional_images").each( function(){
			var count = $(this).find(".position_right").length;
			if ( count > 1 ) {
				// IF MORE THAN ONE IMAGE START GALLERY
				//$(this).find(".position_right").css("cursor","e-resize").addClass("gallery");
				$(this).find(".position_right").wrapAll("<span class='gallery'></span>");

			}
			console.log( 298, count );
		});

			// TMP

				$(".position_right").each( function(){
					console.log( 313, $(this).parents(".gallery").length );
					if ( $(this).parents(".gallery").length === 0 ) {
						$(this).siblings(".gallery_arrow").hide();
					}
				});
		
	}

	function slideShowGo ( click ) {
		var gall = click.parents(".gallery");
		click.find(".position_right:last-child").prependTo( click );
	}

	// 1.9. FILTER PRODUCTS

	function filterProducts ( click ) {
		console.log("filterProducts");
		// GET TAG OF CLICKED CATEGORY
		var thisTag = click.text().toLowerCase();
		// REPLACE SPACES BY HYPHENS
		thisTag = thisTag.replace(" ","-");
		var thisClass = "product-tag-" + thisTag;
		// console.log( thisTag, thisClass );

		$(".product").not(".single_product").hide();
		$(".selected-product").removeClass("selected-product");	
		
		// LOOP THROUGH ITEMS ON PAGE
		$(".product").not(".single_product").each( function(){
			if ( $(this).hasClass( thisClass ) ) {
				$(this).show().addClass("selected-product");
			}
		});	

		// SCROLL TO TOP OF COLLECTION
		var collTop;
		if ( $(".page_collection").length ) {
			collTop = $(".page_collection").offset().top;
		} else {
			collTop = 0;
		}
		console.log( 341, collTop, $(window).scrollTop() );
		$("html,body").animate({
			scrollTop: collTop
		}, 500);

		$(".selected").removeClass("selected");
		$(".clear_filter").hide();

		// IF ON COLLECTION PAGE RUN IMG PREP
		if ( click.parents("#collection_filter").attr("data-page") === "collection" ) {
			imagesPrep();			
		}

		// click.addClass("selected").next("img").show();
		click.addClass("selected");

		// REPLACE FILTER TOGGLE WITH SELECTED TAG
		$("#filter_toggle").css("cursor","text").text( click.text() ).next("img").show();
		$("#collection_filter").hide();


		// ENSURE FILTER TOGGLE IS VISIBLE ON SINGLE PAGES
		$("#filter_toggle").addClass("filter_vis");
	}

	function filterClear() {
		console.log("filterClear");
		// RESET 
		$(".product").show();
		$(".product").addClass("selected-product");	
		$(".selected").removeClass("selected");
		$(".clear_filter").hide();	
		$("#filter_toggle").removeClass("filter_vis").css("cursor","pointer").text( "Filter" );

		imagesPrep();

		// WHY DOES THIS CAUSE PAGE TO MOVE UP????
	}

	// 1.10. RESET QUANTITY INPUTS

	function resetQuantities () {
		// CHECK NOT ON CART PAGE
		if ( $("#cart").length === 0 && $(".quantity").length ) {
			console.log("resetQuantities");
			$(".quantity").each( function(){
				$(this).find("input").attr("value", 1);
			});			
		} 
	}

	// 1.XX. SET SECONDARY NAV HEIGHT

	function secNavH () {
		if ( $(window).height <= 500 ) {
			var navH = $("#secondary_nav ul").height();
			$("#secondary_nav").css( "height", navH );
		}
	}

	// 1.XX. SINGLE DESCRIPTION TOGGLE

	var descVis = false;
	function descToggle ( click ) {
		console.log("descToggle");
		if ( !descVis ) {
			click.next(".product_desc").css({
				"height" : "auto",
				"max-height" : 400 	
			});
			descVis = true;
		} else {
			click.next(".product_desc").css({
				"height" : "",
				"max-height" : "" 	
			});
			descVis = false;
		}
	}

	// 1.XX. SIZES SELECT

	function radioCheck ( click ) {
		// CLICK IS ON LABEL
		console.log( 465, click.text() );
		click.parents(".product-addon").find("label").css( "border-bottom", "" );
		click.css( "border-bottom", "2px solid black" );	
	}

		// SIZES POSITION

	function radioPos ( ) {
		$(".product-addon").each( function(){
			// LOOP TO GET COUNT AND WIDTH
			var radioCount = 0,
			radioWidth = 0;
			$(this).children().not(".clear").each( function(i){
				radioCount++;
				radioWidth += $(this).width();
			});
			console.log( 481, radioCount, radioWidth );
			var container = $(this).width();
			var diff = container - radioWidth;
			var diffPerc = Math.floor( diff / ( radioCount - 1 ) / container * 100 );
			console.log(diffPerc);
			$(this).find("p").css( "margin-left", diffPerc + "%" );
			$(this).find("p").eq( radioCount - 2 ).css({
				"position" : "absolute",
				"right" : 0
			});
		});
		
	}

	// 1.XX. HIDE DOUBLES

	// function hideDoubles(){
	// 	// IF HAS CLASS
	// 	$(".page_collection li").each( function(){
	// 		if ( $(this).hasClass("product-tag-shorts") || $(this).hasClass("product-tag-leggings") || $(this).hasClass() ) {

	// 		}
	// 	});
	// }

	// 1.XX. PAGE INIT

	function pageInit () {
		textWrap();
		buttonResize();	
		imagesPrep();
		slideShowInit(); 
		resetQuantities();
		newsPrep();
		secNavH(); 
		breakCheck();
		iframeResize();
		radioPos();
	}

	// 1.XX. IFRAMES RESIZE

	function iframeResize () {
		if ( $("#news").length ) {
			$("iframe").each( function(){
				var thisR = $(this).attr("width") / $(this).attr("height");
				$(this).css( "height", $(this).width() / thisR );
			});			
		} 
	}




