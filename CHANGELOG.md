#Version 1.3 - 14.12.2019
    - version 1.3 released
    - frontend changes are now properly reflected
    - integrated [remote](https://github.com/sebot/Remote) to control your Sites using Blitz theme from a single WordPress instance
    - changed blitz menu to use sites url instead of /
    - added blitz-remote api integration to securely communicate between sites using AES-128-CTR wrapped inside a chiffre
#Version 1.2 - 20.11.2019  
    - version 1.2 released
    - changed from wp_localize_script to wp_rest for frontend settings and contents
    - optimized data handling
    - added settings for Text & Background Color to Design Tab
#Version 1.1 - 20.11.2019  
    - added basic templates for posts and pages  
    - added full gutenberg support  
    - added 404 page handling which can be selected from pages (gutenberg yay :)  
    - fixed some bugs with navigations  
#Version 1.1 - 18.11.2019  
    - added WordPress wp_body_open hook  
    - changed max-width for blocks in editor
    - removed backend logo for now
    - added a "preview" function to blitz ringmenu
    - added first version of startpage and url handling
    - added view & menu items to blitz backend menu
    - changed assetpipeline
    - different classes for Frontend && Backend
#Version 1.1 - 16.11.2019  
    - implemented blitz ring menu which will change the WordPress dashboard entirely
#Version 1.1 - 15.11.2019  
    - added very basic asset setup for backend (jquery, backbone)  
    - added very basic setup for frontend (sveltejs/smelte)  
    - added Theme settings including major overhaul of the WP Dashboard, Login etc.