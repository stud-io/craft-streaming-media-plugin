# Rendering Stream Assets

## Querying for Elements

You can use the `stream_assets()` Craft twig variable to query for stream assets from the front end. As it's a regular [`ElementQuery`](), you can use functions like [`one()`](), [`all()`]() or [`id()`]() to limit results.

```twig
<!-- Get all Stream Assets -->
{% set stream_asset = craft.stream_assets().all() %}

<!-- Get single Stream Asset from query parameter 'id' -->
{% set stream_asset = craft.stream_assets().id(craft.app.request.getQueryParam('id')|number_format).one %}
```


## Twig Player Macros

We've included Twig templates in the form of Macros, which you can use to render media assets with a single line of code.

Before using the macros, you'll have to import them into your templates:

```twig
{% import "streaming-media/player" as streaming_media_player %}
```

Then you can call the player() function as follows:

```twig
{{ streaming_media_player.player(stream_asset, {}) }}
```


### Player options

The following options are supported through the player() macro's second argument:

- `controls` - Boolean whether to display controls in the player or not. Defaults to `true`.
- `autoplay` - Boolean whether the media should autoplay when the page loads. Defaults to `false`.
- `muted` - Boolean whether the media should be muted by default or not. Defaults to `false`.
- `offset` - Integer controlling where the video will begin to start in seconds. Defaults to `0`.
- `playback_authentication` - Options for controlling playback authentication.
  - `expire` - Set to a date and time after which the media can no longer be played.
