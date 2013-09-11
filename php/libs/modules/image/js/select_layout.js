(function($) {
	$(document).ready(function() {

        $('.gallery img').click(function(){

        	$('.gallery').fadeOut();
        	$('p').fadeOut(function(){
        		$(this).parent().append('<h3 class="loading">Loading images from Instagram<small> - this may take some time</small></h3>');

        		$('h3').bind('fade-cycle', function() {
				    $(this).fadeOut('slow', function() {
				        $(this).fadeIn('slow', function() {
				            $(this).trigger('fade-cycle');
				        });
				    });
				});
				$('h3').trigger('fade-cycle');
        	});



            window.location.replace('~/' + $(this).attr('data-id'));

            $('.gallery img').css('border-color', '');
            $(this).css('border-color', 'red');
        })



	});
})(jQuery);