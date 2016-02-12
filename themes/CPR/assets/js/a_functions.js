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

	/* WORD COUNTER */
	function wordCount(str) { 
	  return str.split(" ").length;
	}

	function textWrap ( ) {
		console.log("textWrap");
		$(".wrap").each( function(){
			var txt = $(this).text().trim();
			var noWords = wordCount( txt );
			if ( noWords === 1 ) {
				// ONE WORD
				$(this).wrapInner("<span class='last_word'></span>");
			} else {
				// MULTIPLE WORDS
				if ( noWords === 3 ) {
					// REMOVE LAST WORD
					var wordArray = txt.split(" ");
					var lastWord = wordArray[2];
					var lastIndex = txt.lastIndexOf(" ");
					txt = txt.substring( 0, lastIndex );
					$(this).html(txt + " <span class='last_word'>" + lastWord + "</span>");
					// lastWord.wrap("<span class='last_word'></span>").appendTo( $(this) );
				} else {
					$(this).widowFix();										
				}				
			}

		});
		// ONE WORD LOOP
		oneWord();
	}

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
			$("#nav_dropdown").css("height", liH * ( colls + 3 ) );
		}, 1000);
	}

	// 1.5. POSITION COLLECTION IMAGES

	function imagesPrep () {
		console.log("imagesPrep");
		// HIDE IMAGES
		$(".page_collection li").hide();

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
			number = arrayLarge[ i ];
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
		// CHECK NOT ON CART PAGE
		if ( $("#cart").length === 0 && $(".quantity").length ) {
			console.log("resetQuantities");
			$(".quantity").each( function(){
				$(this).find("input").attr("value", 1);
			});			
		} 
	}

	// 1.XX. PAGE INIT

	function pageInit () {
		//textWrapInit();
		//textWrapCalc();
		textWrap();
		buttonResize();
		slideShowInit(); 
		resetQuantities();
		newsPrep();		
	}




