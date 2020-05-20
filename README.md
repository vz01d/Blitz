# Blitz
an OOP WordPress theme using sveltejs and smelte
you can read more about both technologies here:

*MAINTAIN NOTE: * this will not be improved further for several reasons (acf pro requirement for example). Feel free to use the code as you like, copy any concepts, modify, redistribute as you wish. *NOT MAINTAINED USE AT YOUR OWN RISK*

https://svelte.dev  
https://github.com/matyunya/smelte

*installation*

clone or download the theme folder and run composer install.

> Warning: this Theme is using acf builder: https://github.com/StoutLogic/acf-builder which depends on ACF pro: https://www.advancedcustomfields.com/pro/ after clone make sure you include it as acf in the vendor folder or change the themes functions.php and helper/acfinit.php to your desire.

However I suggest you do not integrate anything into the functions.php file in addition, if you need to add a filter simply use the OOP way or the Child Theme. (more on that later)

## Frontend

built using svelte https://svelte.dev  
**installation**
> cd /blitz/frontend  
  npm install  
  npm run build  
  npm run autobuild

scripts will output bundle.js into public folder from which it is enqueued into WordPress.  
npm run autobuild allows you to make changes to the svelte app and have updates in realtime.  
However I do not suggest to use this in production.

## Backend

**installation**
> cd /blitz  
  composer install

The backend is a php application which changes the way  
the WordPress dashboard operates by running through a series of scripts and filters. 

## Gutenberg

This Theme is entirely focused on Gutenberg, it ships no fancy pagebuilder and does only the really necessary or provides you the option to disable any fancy flowers.

## Child Theme Support
    ... planned, not quite there yet, until then I suggest to only use the theme if you know what you're doing ...

## Licensing
    The theme is opensource and free to use for anyone on personal or non-profit projects as well as commercial ones. However if you consider using the Theme for any commercial Projects it would be great if you contribute to further developments of the theme by funding me.  
