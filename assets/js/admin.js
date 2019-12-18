window.blitz = (function (window, document, $) {
    let app = {};
  
    app.init = function() {
        if (true != BLITZ.adminBarEnabled) {
            $('#wpadminbar').hide();
        }

        if (true != BLITZ.adminMenuEnabled) {
            $('#adminmenuback, #adminmenuwrap').hide();
        }
        
        if (true == BLITZ.uiEnabled) {
            /*
            if (undefined != BLITZ.logo) {
                $('body').prepend('<img class="backend-logo" src="'+BLITZ.logo+'">');
            }
            */

            $('#wpwrap').css('background-color', BLITZ.adminBgColor);
            $('h1').css('color', BLITZ.adminTextColor);

            // wp primary buttons
            $('h1, h2, h3, h4, h5, h6, p, a').css(
                {
                    'color': BLITZ.adminTextColor
                }
            );
            $('.button-primary').css(
                {
                    'background-color': BLITZ.adminTextColor,
                    'color': BLITZ.adminBgColor,
                    'border': 'none'
                }
            );
            
            $('.button-primary').mouseover(function() {
                $(this).css(
                    {
                        'background-color': BLITZ.adminBgColor,
                        'color': BLITZ.adminTextColor,
                        'border': '1px solid ' + BLITZ.adminTextColor
                    }
                );
    
            }).mouseout(function() {
                $(this).css(
                    {
                        'background-color': BLITZ.adminTextColor,
                        'color': BLITZ.adminBgColor
                    }
                );
            });

            $('body').append(
                '<div id="blitz-modal" style="display:none;">' + 
                    '<span style="cursor:pointer;font-size:60px;color: ' + BLITZ.adminTextColor + '" id="blitz-m-close" class="dashicons dashicons-no-alt"></span>' +
                    '<div class="modal-content"></div>' + 
                '</div>'
            );

            $('#blitz-m-close').click(function(){
                $('#blitz-modal .modal-content').html('');
                $('#blitz-modal').hide(600);
            });
        }
    }
    
    $(document).ready(app.init);
  
    return app;
})(window, document, jQuery);