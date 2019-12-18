<script>
    import { onMount } from "svelte";
    import { fade } from 'svelte/transition';

    let footerNav = [];

    function getFooterNav() {
        return BLITZ.data.navigation.footer;
    }

    onMount(async() => {
        const response = await fetch(
            BLITZ.restUrl + '/settings?load=footer',
            {
                headers: {
                    'X-WP-Nonce': BLITZ.nonce
                }
            }
        );

        const data = await response.json();
        footerNav = data.navigation.footer;
    });
</script>

{#if footerNav}
    <footer transition:fade>
        <div class="md:flex border-t-2 border-teal-400 mt-32">
            <div class="md:flex-1 p-6 text-center">
                <ul class="list-none">
                    {#each footerNav as menuItem}
                        <li class="text-teal-400 p-2 inline-block hover:bg-teal-400 hover:text-white"><a class="p-6" href={menuItem.url} title="go to {menuItem.title}">{menuItem.title}</a></li>
                    {/each}
                </ul>
            </div>
        </div>
    </footer>
{/if}