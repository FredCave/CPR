jQuery(document).ready(function($){
   
    var data = {
        'action': 'get_post_information',
		'post_id': 237
    };

    $(window).on("click", function(){
    	alert();
	    $.post(ajaxurl, data, function(response) {
	        alert('Server response from the AJAX URL ' + response);
	    });    	
    });

});