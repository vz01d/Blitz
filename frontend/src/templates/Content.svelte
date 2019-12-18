<script>
    import { onMount } from 'svelte';
    import { fade } from 'svelte/transition';
    import { Swipe, SwipeItem } from 'svelte-swipe';

    let settings = [];
    let content, title, thumb, slides, textColor, backgroundColor;
    let useSlider = false;
    let showTitle = true;

    // slider settings
    let autoplay = true;
    let delay = 3000; //ms
    let showIndicators = true;
    let transitionDuration = 1500; //ms

    onMount(async() => {
        // get content settings (including slider settings -> move this into separate)
        const contentSettings = await fetch(
            BLITZ.restUrl + '/settings?load=content',
            {
                headers: {
                    'X-WP-Nonce': BLITZ.nonce,
                    'F-PID': BLITZ.currentID
                }
            }
        );

        const postContent = await fetch(
            BLITZ.restBase + 'wp/v2/posts',
            {
                headers: {
                    'X-WP-Nonce': BLITZ.nonce,
                }
            }
        );

        console.log(BLITZ.restBase);

        const data = await contentSettings.json();
        title = data.postTitle;
        if (null === title) {
            title = '404';
        }

        thumb = data.postThumbnail;
        if ('' === thumb && data.sliderData.length > 0) {
            useSlider = true;
            slides = data.sliderData;
        }

        const postData = await postContent.json();
        content = postData[0].content.rendered;
        if (0 === content.length) {
            content = '<div class="text-center">the requested page couldn\'t be found. Please contact an administrator if you think this is an error.</div>';
        }

        showTitle = data.showTitle;

        // TODO: abstract
        textColor = data.textColor;
        backgroundColor = data.backgroundColor;
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

{#if content}
    <article transition:fade>
        <div class="article-header text-center">
            {#if showTitle}
                <h1 class="text-white bg-teal-400 p-12">{title}</h1>
            {/if}
            {#if true === useSlider}
                <div class="swipe-holder">
                    <Swipe {showIndicators} {autoplay} {delay} {transitionDuration}>
                        {#each slides as slide}
                            <SwipeItem>
                                <img src={slide.imageUrl} alt="">
                                {#if '' !== slide.text}
                                    <div class="slider-content">
                                        {@html slide.text}
                                        {#if slide.btnLink}
                                            <a
                                             style="background: {backgroundColor};color: {textColor};"
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
            {:else}
                {@html thumb}
            {/if}
        </div>
        <div class="container mx-auto p-4 mt-8">
            {@html content}
        </div>
    </article>
{/if}