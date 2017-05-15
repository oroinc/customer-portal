
Sticky Panel View
=================

The sticky panel is used to display some elements when they leave the window view port.
Sticky panel is always visible, so elements that can be moved to the panel will always be visible too.

How to Usage
------------

To be able to show an element on the sticky panel, add the `data-sticky` attribute to this element
```html
    <div id="flash-messages" class="notification" data-sticky></div>
```

Customization
--------------

**Add a class to an element in the sticky panel**

Add the `toggleClass` option to the `data-sticky` attribute:
```html
    <div id="flash-messages" class="notification"
         data-sticky='{"toggleClass": "notification--medium"}'>
    </div>
```

**Add an element placeholder to the sticky panel**

Add a placeholder using the layout update:
```yaml
- '@add':
    id: sticky_element_notification
    parentId: sticky_panel_content
    blockType: container
```

Add placeholder template
```twig
{% block _sticky_element_notification_widget %}
    {% set attr = layout_attr_defaults(attr, {
        'id': 'sticky-element-notification'
    }) %}
    <div {{ block('block_attributes') }}></div>
{% endblock %}
```

Add the `placeholderId` option to the `data-sticky` attribute:
```html
    <div id="flash-messages" class="notification"
         data-sticky='{"placeholderId": "sticky-element-notification"}'>
    </div>
```

**Always show an element in the sticky panel**

Move an element to the sticky panel using the layout update:
```yaml
- '@move':
    id: notification
    parentId: sticky_element_notification
```
Several sticky panels
---------------------

To create a custom sticky panel on the page, do the following: 

- Import sticky panel to the layout and define the namespace for a new sticky block.

 ```yaml
 imports:
     -
         id: sticky_panel
         root: page_container
         namespace: top
 ```
- Each sticky panel should have its own name. You should define it with `@setOption`
 
    ```yaml
    - '@setOption':
        id: top_sticky_panel
        optionName: sticky_name
        optionValue: top-sticky-panel
    ```
    
- The `stick_to` option should be set to one of the following values: top (by default), bottom. This value defines the position calculation algorithm. 

    ```yaml
    - '@setOption':
        id: top_sticky_panel
        optionName: stick_to
     optionValue: bottom
    ```

- When a new panel has been added to the page, customize the page elements that will use it. Add the `data-sticky-target` attribute with a sticky name.
 
    ```html
         <div id="flash-messages" class="notification" data-sticky-target="top-sticky-panel"
             data-sticky='{"placeholderId": "sticky-element-notification"}'>
         </div>
    ```
