# ORO Frontend Styles Customization

# Overview

This document gives examples of how to customize the theme for the desired design.
For the theme developer, the subject is given the opportunity to change:
* [the color scheme](#how-change-the-color-scheme);
* [typography](#how-to-change-fonts-and-typography);
* [media breakpoints](#how-to-change-media-breakpoints);
* [indentation](#how-to-change-offsets);

Also, the theme developer can [disable](#how-to-override-or-disable-files), [delete](#how-to-override-or-disable-files)
unnecessary files through `assets.yml`
and **create** a [separate css](https://github.com/orocrm/platform/blob/master/src/Oro/Bundle/LayoutBundle/Resources/doc/config_definition.md)
or completely **disable** all ORO `.scss` [files](#how-remove-unnecessary-oro-files).

> All  theme configs files must located in path: `Resources/public/{theme_name/settings/*.scss`

## How change the color scheme.

You need to create your own list of colors and merge it with `$color-palette` using SASS function `map_merge($map1, $map2)`.
Keys from your color palette will rewrite already existing or supplement.

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

To get the color you need, you can use the function `get-color($palette, $key);`;

````scss
.input {
    color: get-color('secondary', 'main');
}
````

## How to change fonts and typography
For disabling all ORO fonts - you must override `$theme-fonts` variable and set empty `map`;

````scss
$theme-fonts: ();
````

For updating yor must merge `$theme-fonts` with your `$theme-custom-fonts`

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
    ),
    'secondary': (
        'family': 'Roboto',
        'variants': (
            (
                'path': '#{$global-url}/orofrontend/default/fonts/roboto/roboto-regular-webfont',
                'weight': 700,
                'style': normal
            )
        )
    )
);

$theme-fonts: map_merge($theme-fonts, $theme-custom-fonts);
````

If you want **disable ORO fonts** not override you must set:
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
    ),
    'secondary': (
        'family': '...',
        'variants': (
            (
                'path': '...',
                'weight': normal,
                'style': normal
            )
        )
    )
);

@include use-font-face($your-fonts);
````

> `@mixin use-font-face` call dynamically `font-face` with `$your-fonts`.


For change font size, line-height theme developer can override next variables:
````scss
// Offsets;

// Fonts families
$base-font: map_get(map_get($your-fonts, 'main'), 'family'),;

// Fonts sizes
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

For update media breakpoints media theme developer cant change next breakpoints:

````scss
// Desktop Media Breakpoint;

$breakpoint-desktop: 1100px;
$breakpoint-tablet: $breakpoint-desktop - 1px;
$breakpoint-tablet-small: 992px;
$breakpoint-mobile-landscape: 640px;
$breakpoint-mobile: 414px;
````

## How to change Offsets

For update media breakpoints media theme developer cant change next breakpoints:

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

Override some `scss/less/css` - developer override(or remove) any styles file, registered in `Resources/views/layouts/
{theme_name}/config/assets.yml`
For this, you should write next config in your `Resources/views/layouts/{theme_name}`.

````yml
styles:
    inputs:
        - 'bundles/oroform/blank/scss/styles.scss': ~ // file will be removed from build process
        - 'bundles/oroform/blank/scss/styles.scss': 'bundles/oroform/your_theme/scss/styles.scss' // file will be overridden
````

## How remove unnecessary ORO files

Remove all `scss/less/css`: all themes use styles registered in this theme and from parent themes.
You can't change this behaviour without changes in assets build logic.
To remove all assets you should override `oro_layout.assetic.layout_resource` service in your bundle and customize assets collect logic.

