(function($) {
    $(document).ready(function(){
        var $lb = $('input[name="lightbox"]'),
            $custom_lb = $('input[name="custom_lightbox"]'),
            $custom_lb_name = $('input[name="custom_lightbox_name"]'),
            $custom_lb_params = $('textarea[name="custom_lightbox_params"]'),
            $auto_play = $('textarea[name="auto_play"]'),
            $auto_play_interval = $('textarea[name="auto_play_interval"]');
            
        $lb.click(function(){
            if($lb.is(':checked')){
                $custom_lb.removeAttr('checked');
            }
        });
        $custom_lb.click(function(){
            if($custom_lb.is(':checked')){
                $lb.removeAttr('checked');
            }
        });
        
        $('#photoshow-options').submit(function(){
            
            // TODO: make this less half-assed
            var returnState = true,
                $errorList = $('#photoshow-error-list').empty().attr('style', '');
                
            if( $lb.is(':checked') && $custom_lb.is(':checked') ) {
                $errorList.append('<li style="list-style:disc; margin-left:20px;">"Use Default Lightbox" and "Use Custom Lightbox" can\'t both be selected.</li>');
                returnState = false;
            }

            if( $custom_lb.is(':checked') && $custom_lb_params.val().trim() == '' ) {            
                $custom_lb_params.val('{}');
            }
            
            if( $custom_lb.is(':checked') && $custom_lb_name.val().trim() == '' ) {            
                $errorList.append('<li style="list-style:disc; margin-left:20px;">"Custom Lightbox Name" can\'t be empty.</li>');
                returnState = false;
            }
            
            if( $auto_play.is(':checked') && $auto_play_interval.val().trim() == '' ) {            
                $errorList.append('<li style="list-style:disc; margin-left:20px;">An "Autoplay Interval" must be specified.</li>');
                returnState = false;
            }
            
            if( !returnState ) {
                $errorList.prepend('<li style="font-size:16px; color:#cc0000;">Please fix the following errors</li>');
                $errorList.css({
                    'border':'1px solid #de7d7d',
                    'padding':'10px',
                    'background-color':'#f8dfdf'
                });
            }
            return returnState;
        });
    });
}(jQuery));