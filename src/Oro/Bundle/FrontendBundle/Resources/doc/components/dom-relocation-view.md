# Dom Relocation Global View

Dom Relocation View uses when you need to move dom element from one container to another on browser window resize.
For example: move menu list from top bar to hamburger menu dropdown in cases when you cannot do this using css @media queries.

## How to Use

To enable moving an element from one container to another on window resize add 'data-dom-relocation-options'
attributes to corresponding element as it is showing below:
```html
    <div class="element-to-move"
         data-dom-relocation-options="{
            responsive: [
                {
                    viewport: {maxScreenType: 'tablet'},
                    moveTo: '#parent_container' // jQuery selector,
                    sibling: '#sibling_element' // jQuery selector,
                    prepend: true // Boolean,
                    endpointClass: 'some-class-add-after-move' // String
                }
            ]
         }"
    >
    <!-- Other content -->
    </div>
```

## Options

### responsive
**Type:** Array

Set multiple moveTo targets for different types of screens.
Like this:
```javascript
responsive: [
    {
        viewport: {maxScreenType: 'tablet'},
        moveTo: '[data-target-example-1]', // jQuery selector
        sibling: '[data-target-example-1] > div:eq(2)', // jQuery selector
        prepend: true // Boolean
    },
    {
        viewport: {maxScreenType: 'mobile'},
        moveTo: '[data-target-example-2]', // jQuery selector
        prepend: true, // Boolean
        endpointClass: 'moved-to-parent' // String
    }
]
```
It's working with same logic like css @media, so last item of array have higher priority.

### viewport
**Type:** Object

**Default:** '{}'

Option describes when should relocate DOM element. All available screen type defined by [Viewport Manager](../../../../../../../../platform/src/Oro/Bundle/UIBundle/Resources/doc/reference/client-side/viewport-manager.md).

### moveTo
**Type:** String

Set target selector where should move element.

### sibling
**Type:** String

Set sibling element for position moved element

### prepend
**Type:** String

Change behavior to append element into target parent
If set `true` element going to prepend target element
If set `false` element append to end of parent

### endpointClass
**Type:** String

Set class name, going to add class after move on need breakpoint
