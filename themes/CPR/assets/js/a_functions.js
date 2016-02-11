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

	function hasWhiteSpace(s) {
		return s.indexOf(' ') >= 0;
	}

	function textWrapInit () {	
		$(".wrap").each( function(){
			var elmt;
			if ( $(this).has("a").length ) {
				// target is child
				elmt = $(this).find("a");
			} else {
				elmt = $(this);
			}		
			var txt = elmt.text().trim();
			if ( hasWhiteSpace( txt ) ) {
				// STRING
				elmt.wrapInner("<span class='string_wrap'></span>").append("<span class='break'></span>");
			} else {
				elmt.wrapInner("<span class='word_wrap'></span>");
			}
		});
	}

	$.fn.stretch_text = function(){
		var elmt;
		if ( $(this).has("a").length ) {
			// target is child
			elmt = $(this).find("a");
		} else {
			elmt = $(this);
		}	    
	    var cont_width;
	    
    	if ( $(this).parents("#nav").length ) {
			cont_width = $("#nav").width();	
    	} else {
    		cont_width = elmt.parent(".wrap").width();
    		//console.log( $(this).text(), cont_width );
    	} 
	    var txt           = elmt.text(),
	        one_line      = $('<span class="stretch_it">' + txt + '</span>'),
	        nb_char       = elmt.text().length,
	        spacing       = cont_width/nb_char,
	        txt_width;
   	    
    	elmt.html(one_line).css({'letter-spacing': "0"});
   		txt_width = one_line.width();
   		// console.log(txt, cont_width, nb_char, txt_width);
	
	    if (txt_width < cont_width){
	        var char_width = txt_width/nb_char,
	            ltr_spacing = spacing - char_width + (spacing - char_width)/nb_char;   
	        one_line.css({'letter-spacing': ltr_spacing});
	    } else {
	        one_line.contents().unwrap();
	        // elmt.addClass('justify');
	    }

	};

	var liH;

	function textWrapCalc () {
		
		// JUST FOR SINGLE WORDS
		
		$(".word_wrap").each( function(){			
			var elmt;
			if ( $(this).has("a").length ) {
				// target is child
				elmt = $(this).find("a");
			} else {
				elmt = $(this);
			}		
			// console.log( $(this).text() );
			$(this).css("text-align","center").stretch_text()				
		});

		// CALCULATE LI HEIGHT HERE

		liH = $("#nav_dropdown .string_wrap").height() + 18;
		//console.log( liH );
		$("#nav li").css("height", liH);

		$(".string_wrap").each( function(){
			if ( $(this).parent("h2").length ) {
				$("h2").css( "height", $(this).height() );
			} else if ( $(this).parent("h3").length ) {
				$("h3").css( "height", $(this).height() );
			} else {
				$(this).parent(".wrap").css( "height", $(this).height() );
			}
		});

	}

	// 1.2. NAV SHOW / HIDE



	function navShow () {
		console.log("navShow");
		$("#nav").removeClass("hidden");
		$("#nav_dropdown").css({
			"height" : (liH * 5) + 12
		});
	
		// SHOW CLOSE BUTTON
		$("#secondary_nav ul").fadeOut();
		$("#nav_close").fadeIn();

	}

	// $("#nav_dropdown").css("height", 5 * liH);
	function navHide () {
		console.log("navHide");
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

	}

	// 1.3. SCROLL DETECT

	var lastScrollTop = 0;
	function scrollDetect () {
		var current = $(this).scrollTop();
		// console.log(current, lastScrollTop);
	   	if (current > (lastScrollTop + 100) ){
	       navHide();
	   	} 
	   	lastScrollTop = current;
	}

	// 1.4. TOGGLE COLLECTIONS

	function collToggle ( main ) {
		var colls;
		$("#nav_dropdown").css("height", "auto");
		if ( !$(".nav_collection").hasClass("clicked") && main !== "main" ) {
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
			$("#nav_dropdown").css("height", liH * ( colls + 3 ) );
		}, 1000);
	}

	// 1.5. POSITION COLLECTION IMAGES

	function imagesPrep () {
		console.log("imagesPrep");
		// HIDE IMAGES
		$(".page_collection li").hide();

		// NEED TO REMOVE PREVIOUSLY ADDED ROWS
		$(".row").each( function(){
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
			number = arrayLarge[ i ];
			// if number of images left is less than array number
			if ( ( noImages - total ) < number ) {
				number = noImages - total;
			}	
			// REMOVE EXISTING CLASSES BEGINNING WITH CHILD-*
			$(".selected-product").slice( total, total+number ).wrapAll("<div class='row'></div>").alterClass("child-*", "child-" + number);
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

	$(".news_text").each( function(){
		$(this).find("img").appendTo( $(this).next(".news_images") )
	});

	// 1.8. SINGLE IMAGE SLIDESHOW

	function slideShowInit () {
		
		$(".single_additional_images").each( function(){
			var count = $(this).find(".position_right").length;
			if ( count > 1 ) {
				// IF MORE THAN ONE IMAGE START GALLERY
				//$(this).find(".position_right").css("cursor","e-resize").addClass("gallery");
				$(this).find(".position_right").wrapAll("<span class='gallery'></span>");
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

		var thisTag = click.text().toLowerCase();
		var thisClass = "product-tag-" + thisTag;
				
		$(".product").hide();
		$(".selected-product").removeClass("selected-product");	
		
		// LOOP THROUGH ITEMS ON PAGE
		$(".product").each( function(){
			if ( $(this).hasClass( thisClass ) ) {
				$(this).show().addClass("selected-product");
			}
		});	

		// SCROLL TO TOP
		$("html,body").animate({
			scrollTop: 0
		}, 500);

		$(".selected").removeClass("selected");
		$(".clear_filter").hide();

		// IF ON COLLECTION PAGE RUN IMG PREP
		if ( click.parents("#collection_filter").attr("data-page") === "collection" ) {
			imagesPrep();			
		}

		click.addClass("selected").next("img").show();
	}

	function filterClear( click ) {
		console.log("filterClear");
		// RESET 
		$(".product").show();
		$(".product").addClass("selected-product");	
		$(".selected").removeClass("selected");
		$(".clear_filter").hide();	

		imagesPrep();
	}

	// 1.10. RESET QUANTITY INPUTS

	function resetQuantities () {
		console.log("resetQuantities");
		$(".quantity").each( function(){
			$(this).find("input").attr("value", 1);
		});		
	}





