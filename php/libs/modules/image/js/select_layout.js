(function($) {
	$(document).ready(function() {

        $('.gallery img').click(function(){

            window.location.replace('~/' + $(this).attr('data-id'));

            $('.gallery img').css('border-color', '');
            $(this).css('border-color', 'red');
        })

	});
})(jQuery);