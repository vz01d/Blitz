<script>
    import { onMount } from 'svelte';
    import { fade } from 'svelte/transition';
    import { Swipe, SwipeItem } from 'svelte-swipe';

    // slider settings
    let sliderData;
    let autoplay = true;
    let delay = 3000; //ms
    let showIndicators = true;
    let transitionDuration = 1500; //ms

    export let sliderId;

    onMount(async() => {
        const slidesReq = await fetch(
            BLITZ.slidesUrl,
            {
                headers: {
                    'X-WP-Nonce': BLITZ.nonce,
                    'B-PID'     : sliderId
                }
            }
        )

        const slides = await slidesReq.json();
        sliderData = slides;
    });
</script>

<style lang="postcss">
    :root {
        --sv-swipe-indicator-active-color: #fff;
    }
    .swipe-holder {
        position: relative;
        height: 70vh;
        width: 100%;
    }
    img {
        max-width: 100%;
        height: auto;
    }
    .slider-action {
        pointer-events: fill;
        padding: 10px;
        margin-top: 15px;
        display: inline-block;
    }
    .slider-content {
        position: absolute;
        z-index: 9999;
        top: 50%;
        transform: translateY(-50%);
        left: 15%;
        max-width: 25%;
        text-align: left;
        background: rgba(234, 234, 234, 0.8);
        padding: 25px;
    }
</style>

{#await sliderData then slides}
    {#if slides}
        <div class="swipe-holder" transition:fade>
            <Swipe {showIndicators} {autoplay} {delay} {transitionDuration}>
                {#each slides as slide}
                    <SwipeItem>
                        <img src={slide.imageUrl} alt="">
                        {#if '' !== slide.text}
                            <div class="slider-content">
                                {@html slide.text}
                                {#if slide.btnLink}
                                    <a
                                        style="background: #000;color: #fff;"
                                        class="slider-action"
                                        href={slide.btnLink.url} 
                                        target={slide.btnLink.target} 
                                        title={slide.btnLink.title}>
                                        {slide.btnLink.title} 
                                    </a>
                                {/if}
                            </div>
                        {/if}
                    </SwipeItem>
                {/each}
            </Swipe>
        </div>
    {/if}
{/await}