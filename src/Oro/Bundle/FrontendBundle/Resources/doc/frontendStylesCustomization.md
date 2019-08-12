# ORO Frontend Styles Customization

# Overview

This document gives examples of how to customize the theme for the desired design.
A theme developer can introduce changes to the following settings:
* [the color scheme](#how-change-the-color-scheme);
* [typography](#how-to-change-fonts-and-typography);
* [media breakpoints](#how-to-change-media-breakpoints);
* [indentation](#how-to-change-offsets);

Also, the theme developer can [disable](#how-to-override-or-disable-files), [delete](#how-to-override-or-disable-files)
unnecessary files through `assets.yml`
and **create** a [separate css](https://github.com/orocrm/platform/blob/master/src/Oro/Bundle/LayoutBundle/Resources/doc/config_definition.md)
or completely **disable** all ORO `.scss` [files](#how-remove-unnecessary-oro-files).

> All  theme config files must be located in the following path: `Resources/public/{theme_name/settings/*.scss`

## How change the color scheme.

You need to create your own list of colors and merge it with `$color-palette` using SASS function `map_merge($map1, $map2)`.
This way, your color scheme will rewrite or extend the already existing $color-palette.

````scss
$theme-color-palette: (
    'primary': (
        'main': #37435c,
    ),
    'secondary': (
        'main': #fcb91d,
    ),
    'additional': (
        'ultra': #fff
    )
) !default;

$color-palette: map_merge($color-palette, $theme-color-palette);

````

To get the color you need, use the `get-color($palette, $key);` function.

````scss
.input {
    color: get-color('secondary', 'main');
}
````

## How to change fonts and typography
To disable all ORO fonts, override `$theme-fonts` variable and set `map` empty;

````scss
$theme-fonts: ();
````

To update fonts, merge `$theme-fonts` with your `$theme-custom-fonts`

````scss
$theme-custom-fonts: (
    'main': (
        'family': 'Lato',
         'variants': (
             (
                 'path': '#{$global-url}/orofrontend/default/fonts/lato/lato-regular-webfont',
                 'weight': 400,
                 'style': normal
             ),
             (
              'path': '#{$global-url}/orofrontend/default/fonts/lato/lato-bold-webfont',
              'weight': 700,
              'style': normal
             )
         ),
         'formats': ('woff', 'woff2')
    ),
    'secondary': (
        'family': 'Roboto',
        'variants': (
            (
                'path': '#{$global-url}/orofrontend/default/fonts/roboto/roboto-regular-webfont',
                'weight': 700,
                'style': normal
            )
        ),
        'formats': ('woff', 'woff2')
    )
);

$theme-fonts: map_merge($theme-fonts, $theme-custom-fonts);
````

To disable all Oro fonts without overriding them with yours:
1. Set in your $theme-fonts: ();
2. Call mixin font-face() or use-font-face();

````scss
$theme-fonts: ();

// Using font-face
@include font-face($font-family, $file-path, $font-weight, $font-style);

// Using use-font-face
$your-fonts: (
    'main': (
        'family': '...',
         'variants': (
             (
                 'path': '..',
                 'weight': normal,
                 'style': normal
             ),
             (
              'path': '...',
              'weight': 700,
              'style': normal
             )
         ),
         'formats': ('woff', 'woff2')
    ),
    'secondary': (
        'family': '...',
        'variants': (
            (
                'path': '...',
                'weight': normal,
                'style': normal
            )
        ),
        'formats': ('woff', 'woff2')
    )
);

@include use-font-face($your-fonts);
````

> `@mixin use-font-face` call dynamically `font-face` with `$your-fonts`.


To change the font size and line-height, override the next variables:
````scss
// Offsets;

// Font families
$base-font: get-font-name('main');

// Font sizes
$base-font-size: 14px;
$base-font-size--large: 16px;
$base-font-size--xs: 11px;
$base-font-size--s: 13px;
$base-font-size--m: 20px;
$base-font-size--l: 23px;
$base-font-size--xl: 26px;
$base-line-height: 1.35;
````

## How to change media breakpoints

To update media breakpoints, change the next breakpoints:

````scss
// Default Media Breakpoint;

$breakpoint-desktop: 1100px;
$breakpoint-tablet: $breakpoint-desktop - 1px;
$breakpoint-tablet-small: 992px;
$breakpoint-mobile-big: 767px;
$breakpoint-mobile-landscape: 640px;
$breakpoint-mobile: 414px;
$breakpoint-mobile-small: 360px;

````

To add, update media queries theme developer must create files with global-settings `your-theme/settings/global-settings.scss`
and update list with custom breakpoints. These breakpoints will be synchronized with [viewport manager](https://github.com/orocrm/platform/blob/master/src/Oro/Bundle/UIBundle/Resources/doc/reference/client-side/viewport-manager.md)
 

````scss
$custom-breakpoints: (
    'my-custom-breakpoint': __your-rule__, //  add a new rule
    'desktop': __your-rule__,              // update an existing rule
);

$breakpoints: merge-breakpoints($oro_breakpoints, $custom-breakpoints) !default;
````

To disable some media query theme developer must set breakpoint to null
````scss
$custom-breakpoints: (
    'desktop': null                        // disable an existing rule
);

$breakpoints: merge-breakpoints($oro_breakpoints, $custom-breakpoints) !default;
````

## How to change Offsets

To update Offsets, change the next variables:

````scss
// Offsets;

$offset-y: 15px;
$offset-y-m: 10px;
$offset-y-s: 5px;
$offset-x: 15px;
$offset-x-m: 10px;
$offset-x-s: 5px;
````

## How to override or disable files

To remove or override `scss/css`, create an assets.yml file in your theme and write the following config in `Resources/views/layouts/{theme_name}`

````yml
styles:
    inputs:
        - 'bundles/oroform/blank/scss/styles.scss': ~ // file will be removed from build process
        - 'bundles/oroform/blank/scss/styles.scss': 'bundles/oroform/your_theme/scss/styles.scss' // file will be overridden
````

## How remove unnecessary ORO files

Remove all `scss/css`: all themes use styles registered in this theme and from parent themes.
You cannot change this behavior without changes in assets build logic.
To remove all assets, override `oro_layout.assetic.layout_resource` service in your bundle and customize assets collect logic.

