<script>
    import { onMount } from "svelte";
    import { fade } from 'svelte/transition';

    let showHeader, showNav;
    let mainNav = [];
    let logoUrl;
    let isHome = true;
    let textColor = '';
    let backgroundColor = '';

    function isBaseUrl(url) {
        return document.location.href.replace(/\/$/, '') === url;
    }

    onMount(async() => {;
        const response = await fetch(
            BLITZ.restUrl + '/settings?load=header',
            {
                headers: {
                    'X-WP-Nonce': BLITZ.nonce
                }
            }
        );
        
        const data = await response.json();
        mainNav = data.navigation.main;
        showHeader = data.showHead;
        showNav = data.showNav;
        logoUrl = data.logo;
        isHome = isBaseUrl(data.baseUrl);
        textColor = data.textColor;
        backgroundColor = data.backgroundColor;
    });
</script>