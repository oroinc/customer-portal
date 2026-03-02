# Oro\Bundle\CommerceMenuBundle\Api\Model\CommerceMenuItem

## ACTIONS

### get_list

Retrieve a collection of menu items as a flat list.

## FIELDS

### name

The unique identifier of the menu item.

### label

The localized label of the menu item.

### uri

The URI or URL of the menu item.

### description

The localized description of the menu item.

When the description comes from the menu item form (MenuUpdate), it is already localized for the current locale. When it comes from navigation config (e.g. a translation key), it is translated for the current locale.

### link_attributes

An object that contains HTML attributes for the menu item link.

For example, when the menu item is configured to open in a new window, the object contains ``target`` property with value ``_blank``.

### extras

Additional properties of the menu item.

It is an object with the following properties:

**icon** is a string that contains the icon identifier for the menu item.

**position** is an integer that indicates the position of the menu item in the menu.

**image** is a string that contains the URL of the image for the menu item.

**screens** is an array that contains screen identifiers where the menu item should be displayed.

**max_traverse_level** is an integer that limits the depth of menu tree traversal.

**menu_template** is a string that contains the menu template identifier for the menu item.

### contentNode

The web catalog content node associated with the menu item.

### parent

The parent menu item in the menu hierarchy.

### resource

Resource information for the menu item.

It is an object with the following properties:

**isSlug** is a boolean that indicates whether the URI is a slug.

**redirectUrl** is a string that contains the redirect URL.

**redirectStatusCode** is an integer that contains the HTTP status code for redirect.

**resourceType** is a string that contains the type of resource.

**apiUrl** is a string that contains the API URL for the resource.
