# Oro\Bundle\CustomerBundle\Entity\CustomerUserAddress

## ACTIONS

### get

Retrieve a specific customer user address record.

{@inheritdoc}

### get_list

Retrieve a collection of customer user address records.

{@inheritdoc}

### create

Create a new customer user address record.

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

Edit a specific customer user address record.

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

Delete a specific customer user address record.

{@inheritdoc}

### delete_list

Delete a collection of customer user address records.

{@inheritdoc}

## FIELDS

### city

#### create

{@inheritdoc}

**The required field.**

#### update

{@inheritdoc}

**This field must not be empty, if it is passed.**

### postalCode

#### create

{@inheritdoc}

**The required field.**

#### update

{@inheritdoc}

**This field must not be empty, if it is passed.**

### street

#### create

{@inheritdoc}

**The required field.**

#### update

{@inheritdoc}

**This field must not be empty, if it is passed.**

### firstName

#### create

{@inheritdoc}

**Conditionally required field:**
Either **organization** or **firstName** and **lastName** must be defined.

#### update

{@inheritdoc}

**Conditionally required field:**
Either **organization** or **firstName** and **lastName** must remain defined.

### lastName

#### create

{@inheritdoc}

**Conditionally required field:**
Either **organization** or **firstName** and **lastName** must be defined.

#### update

{@inheritdoc}

**Conditionally required field:**
Either **organization** or **firstName** and **lastName** must remain defined.

### organization

#### create

{@inheritdoc}

**Conditionally required field:**
Either **organization** or **firstName** and **lastName** must be defined.

#### update

{@inheritdoc}

**Conditionally required field:**
Either **organization** or **firstName** and **lastName** must remain defined.

### country

#### create

{@inheritdoc}

**The required field.**

#### update

{@inheritdoc}

**This field must not be empty, if it is passed.**

### region

#### create, update

{@inheritdoc}

**Conditionally required field:**
A state is required for some countries.

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

## FILTERS

### addressType

Filter records by address type, e.g. shipping, billing, etc.

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

### region

#### get_subresource

Retrieve a record that contains information about the region mentioned in a specific customer user address.

#### get_relationship

Retrieve the ID of the region mentioned in a specific customer user address.
