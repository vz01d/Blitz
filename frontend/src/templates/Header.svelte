<script>
    import { onMount } from "svelte";
    import { fade } from 'svelte/transition';

    let mainNav = [];
    let isHome = true;

    export let showNavigation;
    export let logoUrl;
    export let textColor = '';
    export let backgroundColor = '';

    function isBaseUrl(url) {
        return document.location.href.replace(/\/$/, '') === url;
    }

    onMount(async() => {
        const response = await fetch(
            BLITZ.contentUrl + '?load=header',
            {
                headers: {
                    'X-WP-Nonce': BLITZ.nonce,
                    'B-PID': BLITZ.currentID
                }
            }
        );
        
        const data = await response.json();
        mainNav = data.navigation.main;
        isHome = isBaseUrl(data.baseUrl);
    });
</script>

<header transition:fade>
    <div class="md:flex">
        <div class="md:w-1/4 p-6">
            {#if logoUrl}
                {#if ! isHome}
                    <a href="/" title="go to home page">
                        <img src="{logoUrl}" title="site logo" alt="site logo" />
                    </a>
                {:else}
                    <img src="{logoUrl}" title="site logo" alt="site logo" />
                {/if}
            {/if}
        </div>
        <div class="md:w-3/4 p-6 text-right">
            {#if showNavigation}
                <ul class="list-none">
                    {#each mainNav as menuItem}
                        <li style="background:{backgroundColor};color:{textColor};" class="p-2 inline-block"><a class="p-6" href={menuItem.url} title="go to {menuItem.title}">{menuItem.title}</a></li>
                    {/each}
                </ul>
            {/if}
        </div>
    </div>
</header>