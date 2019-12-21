<script>
    import { onMount } from 'svelte';
    import { fade } from 'svelte/transition';
    import Slider from './Slider.svelte';

    let posts;

    onMount(async() => {
        const postRequest = await fetch(
            BLITZ.restBase + 'wp/v2/' + BLITZ.pt + '?include[]=' + BLITZ.currentID
        );

        const postData = await postRequest.json();
        posts = postData;
    });
</script>

<style lang="postcss">
    
</style>

{#await posts then post}
    {#if post}
        {#each post as p}
            {#if false !== p.slider}
                <Slider sliderId={p.slider.ID} />
            {/if}
            <article transition:fade>
                {@html p.content.rendered}
            </article>
        {/each}
    {/if}
{/await}