# Tandem Performance Drupal 8/9 Module 

Use this module when you want quick performance wins on PageSpeed.  This module comes with a variety of tools needed to get you a much better score without using heavy duty modules such as AdvAgg.

In conjunction with this module do the following setup:

### Setup Lazy

You will need to manually edit your main site's composer.json as such:

1. Add the asset packagist repo:


```
"repositories": [
    {
        "type": "composer",
        "url": "https://asset-packagist.org"
    },
]
```


2. Add the installer-types and installer-paths like this:

```
"extra": {
    "installer-types": ["bower-asset", "npm-asset"],
    "installer-paths": {
        ...
        "web/libraries/{$name}": ["type:drupal-library", "type:bower-asset", "type:npm-asset"],
        ...
    }
}
```

3. Install the library with:


```
composer require "bower-asset/lazysizes:5.1"
```

For more information [see the lazy install docs](https://www.drupal.org/docs/8/modules/lazy-load/how-to-use-composer-to-install-lazy-load-module-and-its-dependency).

### Images Setup

This module comes with settings for your Image API pipelines for ReSmush.it and WebP.  It also comes with lazy all set up as well.

To get your images to be more performant do the following:

1. Add the pipeline to all your image styles at /admin/config/media/image-styles
2. Recreate all image styles as responsive image styles (if they don't exist already) at: /admin/config/media/responsive-image-style
	- Use the all breakpoint and set your image style to the corresponding image style.  Use the same image style as a fallback.
3. Go to wherever your image fields are (media, content types, paragraphs, etc) and do the following:
	- Change the image style to the corresponding responsive image style
	- Check the lazy checkbox setting.
    - If the image field is using the Original Image, switch the image style to Original Image (w/ Optimizations)

This should allow all your images to lazy load, be compressed properly, and serve WebP variants of the image.

### Extras

This module comes with 2 image formatters:

1. oEmbed Lazy is to be used when you are embedding remote video urls as iframes.
2. Image URL WebP formatter to be used when you are using image urls as background images.  