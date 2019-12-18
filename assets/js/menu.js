window.ringmenu = (function (window, document, $) {
    let app = {};
  
    app.init = function() {
        // add ring menu
        $('body').append(
            '<div class="blitz-ringmenu">' +
                '<div style="display:none;" id="fet-ringmenu">' +
                    '<a href="'+ BLITZ.siteUrl +'/wp-admin/index.php"> ' +
                    '<span class="fet-menu-item dashicons dashicons-dashboard"></span></a>' +
                    '<a id="fet-create-new" href="#"> ' +
                    '<span class="fet-menu-item dashicons dashicons-edit"></span></a>' +
                    '<a href="'+ BLITZ.siteUrl +'/wp-admin/nav-menus.php"> ' +
                    '<span class="fet-menu-item dashicons dashicons-admin-links"></span></a>' +
                    '<a href="'+ BLITZ.siteUrl +'/wp-admin/admin.php?page=blitz-set"> ' +
                    '<span class="fet-menu-item dashicons dashicons-admin-generic"></span></a>' +
                    '<a href="'+ BLITZ.siteUrl +'/wp-admin/plugins.php"> ' +
                    '<span class="fet-menu-item dashicons dashicons-admin-plugins"></span></a>' +
                    '<a href="'+ BLITZ.siteUrl +'/wp-admin/users.php"> ' +
                    '<span class="fet-menu-item dashicons dashicons-admin-users"></span></a>' +
                    '<a href="'+ BLITZ.siteUrl +'/wp-admin/site-health.php"> ' +
                    '<span class="fet-menu-item dashicons dashicons-shield"></span></a>' +
                    '<a target="_blank" href="' + BLITZ.postUrl + '"> ' +
                    '<span class="fet-menu-item dashicons dashicons-visibility"></span></a>' +
                '</div>' +
                '<span id="fet-menu-handle" class="fet-menu-item dashicons dashicons-image-filter"></span>' +
            '</div>'
        ); 

        // ring menu styles
        app.applyThemeColors('.fet-menu-item');

        $('#fet-menu-handle').click(function(){
            $('#fet-ringmenu').toggle(600);
        });

        $('#fet-create-new').click(function(e){
            e.preventDefault();

            let postTypes = BLITZ.postTypes;
            let output = '';

            for(let i = 0;i < postTypes.length;i++) {
                let postType = postTypes[i];
                let labelText = app.ucFirst(postTypes[i].replaceAll('-', ' '));
                output += '<a style="text-decoration:none;" href="/wp-admin/post-new.php?post_type='+ postType +'" title="create new "'+ labelText +'>' +
                '<div style="color: ' + BLITZ.adminTextColor + ';background-color:' + BLITZ.adminTextColor + 
                ';" class="select-area" data-type="post"><h2>'+ labelText +'</h2></div></a>';
            }

            $('#fet-ringmenu').toggle(600);
            $('#blitz-modal').css({'background-color': app.hex2rgba(BLITZ.adminBgColor, 0.8)});
            $('#blitz-modal .modal-content').html(
                '<div class="post-create-container">' +
                    output +
                '</div>'
            )

            app.applyThemeColors('.select-area');
            $('#blitz-modal').show(600);
        });
    }

    app.applyThemeColors = function(selector){
        $(selector).css({
            'background-color': BLITZ.adminTextColor,
            'color': BLITZ.adminBgColor,
            'border': '1px solid ' + BLITZ.adminTextColor
        });
        $(selector).mouseover(function() {
            $(this).css({
                'background-color': BLITZ.adminBgColor,
                'color': BLITZ.adminTextColor,
                'border': '1px solid ' + BLITZ.adminTextColor
            });
        }).mouseout(function() {
            $(this).css({
                'background-color': BLITZ.adminTextColor,
                'color': BLITZ.adminBgColor
            });
        });
    }

    app.hex2rgba = (hex, alpha = 1) => {
        const [r, g, b] = hex.match(/\w\w/g).map(x => parseInt(x, 16));
        return `rgba(${r},${g},${b},${alpha})`;
    };

    app.ucFirst = function(text) {
        return text.toLowerCase()
        .split(' ')
        .map((s) => s.charAt(0).toUpperCase() + s.substring(1))
        .join(' ');
    };
    
    $(document).ready(app.init);
  
    return app;
})(window, document, jQuery);

String.prototype.replaceAll = function(search, replacement) {
    var target = this;
    return target.split(search).join(replacement);
};
