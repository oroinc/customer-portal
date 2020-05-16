# Oro\Bundle\CustomerBundle\Entity\CustomerUserAddress

## ACTIONS

### get

Retrieve a specific <a href="https://www.oroinc.com/doc/orocommerce/current/user-guide/getting-started/common-actions/manage-address-book#user-guide-getting-started-address-book">customer user address</a> record.

{@inheritdoc}

### get_list

Retrieve a collection of <a href="https://www.oroinc.com/doc/orocommerce/current/user-guide/getting-started/common-actions/manage-address-book#user-guide-getting-started-address-book">customer user address</a> records.

{@inheritdoc}

### create

Create a new <a href="https://www.oroinc.com/doc/orocommerce/current/user-guide/getting-started/common-actions/manage-address-book#user-guide-getting-started-address-book">customer user address</a> record.

The created record is returned in the response.

{@inheritdoc}

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "customeruseraddresses",
    "attributes": {
      "city": "Rochester",
      "firstName": "Branda",
      "label": "Primary address",
      "lastName": "Sanborn",
      "middleName": null,
      "namePrefix": null,
      "nameSuffix": null,
      "organization": null,
      "phone": null,
      "postalCode": "14608",
      "primary": true,
      "street": "23400 Caldwell Road",
      "street2": null,
      "types": [
        {
          "default": true,
          "addressType": "billing"
        },
        {
          "default": true,
          "addressType": "shipping"
        }
      ]
    },
    "relationships": {
      "country": {
        "data": {
          "type": "countries",
          "id": "US"
        }
      },
      "customerUser": {
        "data": {
          "type": "customerusers",
          "id": "2"
        }
      },
      "owner": {
        "data": {
          "type": "users",
          "id": "1"
        }
      },
      "region": {
        "data": {
          "type": "regions",
          "id": "US-NY"
        }
      }
    }
  }
}
```
{@/request}

### update

Edit a specific <a href="https://www.oroinc.com/doc/orocommerce/current/user-guide/getting-started/common-actions/manage-address-book#user-guide-getting-started-address-book">customer user address</a> record.

The updated record is returned in the response.

{@inheritdoc}

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "customeruseraddresses",
    "id": "17",
    "attributes": {
      "phone": null,
      "primary": true,
      "label": "Primary address",
      "street": "23400 Caldwell Road",
      "street2": null,
      "city": "Rochester",
      "postalCode": "14608",
      "organization": null,
      "namePrefix": null,
      "firstName": "Branda",
      "middleName": null,
      "lastName": "Sanborn",
      "nameSuffix": null,
      "types": [
        {
          "default": true,
          "addressType": "billing"
        },
        {
          "default": true,
          "addressType": "shipping"
        }
      ]
    },
    "relationships": {
      "owner": {
        "data": {
          "type": "users",
          "id": "1"
        }
      },
      "country": {
        "data": {
          "type": "countries",
          "id": "RO"
        }
      },
      "region": {
        "data": {
          "type": "regions",
          "id": "RO-MS"
        }
      }
    }
  }
}
```
{@/request}

### delete

Delete a specific <a href="https://www.oroinc.com/doc/orocommerce/current/user-guide/getting-started/common-actions/manage-address-book#user-guide-getting-started-address-book">customer user address</a> record.

{@inheritdoc}

### delete_list

Delete a collection of <a href="https://www.oroinc.com/doc/orocommerce/current/user-guide/getting-started/common-actions/manage-address-book#user-guide-getting-started-address-book">customer user address</a> records.

{@inheritdoc}

## FIELDS

### city

#### create

{@inheritdoc}

**The required field.**

#### update

{@inheritdoc}

**Please note:**

*This field is **required** and must remain defined.*

### postalCode

#### create

{@inheritdoc}

**The required field.**

#### update

{@inheritdoc}

**Please note:**

*This field is **required** and must remain defined.*

### street

#### create

{@inheritdoc}

**The required field.**

#### update

{@inheritdoc}

**Please note:**

*This field is **required** and must remain defined.*

### firstName

#### create

{@inheritdoc}

**Conditionally required field:**
*Either **organization** or **firstName** and **lastName** must be defined.*

#### update

{@inheritdoc}

**Please note:**

*Either **organization** or **firstName** and **lastName** must remain defined.*

### lastName

#### create

{@inheritdoc}

**Conditionally required field:**
*Either **organization** or **firstName** and **lastName** must be defined.*

#### update

{@inheritdoc}

**Please note:**

*Either **organization** or **firstName** and **lastName** must remain defined.*

### organization

#### create

{@inheritdoc}

**Conditionally required field:**
*Either **organization** or **firstName** and **lastName** must be defined.*

#### update

{@inheritdoc}

**Please note:**

*Either **organization** or **firstName** and **lastName** must remain defined.*

### country

#### create

{@inheritdoc}

**The required field.**

#### update

{@inheritdoc}

**Please note:**

*This field is **required** and must remain defined.*

### region

#### create, update

{@inheritdoc}

**Conditionally required field:**
*State is required for some countries.*

### customerUser

#### create

{@inheritdoc}

**The required field.**

#### update

{@inheritdoc}

**The read-only field. A passed value will be ignored.**

### types

An array of address types.

Each element of the array is an object with two properties, **addressType** and **default**.

The **addressType** property is a string represents the type of the address, e.g. **shipping**, **billing**, etc. The full list of the address types can be received via `/api/addresstypes` resource.

The **default** property is a boolean and defines whether the address is used as a default address for shipping, billing, etc.

Example of data: **\[{"addressType": "billing", "default": false}, {"addressType": "shipping", "default": true}\]**

### systemOrganization

#### create, update

{@inheritdoc}

**The read-only field. A passed value will be ignored.**

## SUBRESOURCES

### country

#### get_subresource

Retrieve a record that contains information about the country that is mentioned in the customer user address.

#### get_relationship

Retrieve the ID of the country that is mentioned in the customer user address.

### customerUser

#### get_subresource

Retrieve a record of a contact person.

#### get_relationship

Retrieve the ID of a contact person.

### owner

#### get_subresource

Retrieve a record that contains information about the user who is assigned as a customer user address record owner in the management console.

#### get_relationship

Retrieve the ID of the user who is assigned as a customer user address record owner in the management console.

#### update_relationship

Replace the user who is assigned as a customer user address record owner in the management console.

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "users",
    "id": "2"
  }
}
```
{@/request}

### region

#### get_subresource

Retrieve a record that contains information about the region mentioned in a specific customer user address.

#### get_relationship

Retrieve the ID of the region mentioned in a specific customer user address.

### systemOrganization

#### get_subresource

Retrieve the record of the organization a specific customer user address record belongs to.

#### get_relationship

Retrieve the ID of the organization record which a specific customer user address record will belong to.
