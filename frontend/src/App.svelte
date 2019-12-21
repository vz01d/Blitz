<script>
    import { onMount } from 'svelte';
    
    import BlitzHeader from './templates/Header.svelte';
    import BlitzContent from './templates/Content.svelte';

    let settings;
    
    async function getSettings() {
        const res = await fetch(
            BLITZ.settingsUrl,
            {
                headers: {
                    'B-PID': BLITZ.currentID
                }
            }
        );

        // TODO: settings may return 404
        const data = await res.json();
        return data;
    }
    
    onMount(async() => {
        settings = getSettings();
    });
</script>
{#await settings then setting}
    {#if setting && setting.showHeader}
        <BlitzHeader
            logoUrl={setting.logoUrl}
            textColor={setting.textColor}
            backgroundColor={setting.backgroundColor}
            showNavigation={setting.showNavigation}
        />
        <main class="min-h-screen" role="main">
            <BlitzContent />
        </main>
    {/if}
{/await}