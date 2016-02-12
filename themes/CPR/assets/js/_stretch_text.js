$.fn.stretch_text = function(){

	var	elmt = $(this);
    console.log( elmt.text() );      
    var cont_width;
    
	if ( $(this).parents("#nav").length ) {
		cont_width = $("#nav").width();	
	} else {
		cont_width = $(this).parent().width();
		//console.log( $(this).text(), cont_width );
	} 
    var txt           = elmt.text().trim(),
        one_line      = $('<span class="stretch_it">' + txt + '</span>'),
        nb_char       = txt.length, 
        spacing       = cont_width/nb_char,
        txt_width;

	elmt.html(one_line).css({"letter-spacing": "0"});
		txt_width = one_line.width();
		// console.log("container width: ", cont_width, "text width: ", txt_width);

    if (txt_width < cont_width){	
        var char_width = txt_width/nb_char,
            ltr_spacing = spacing - char_width + (spacing - char_width)/nb_char;   
        one_line.css({'letter-spacing': ltr_spacing});
    } else {
        one_line.contents().unwrap();
        // elmt.addClass('justify');
    }

};