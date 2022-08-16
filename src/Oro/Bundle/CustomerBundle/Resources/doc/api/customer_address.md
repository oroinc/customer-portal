# Oro\Bundle\CustomerBundle\Entity\CustomerAddress

## ACTIONS

### get

Retrieve a specific customer address record.

{@inheritdoc}

### get_list

Retrieve a collection of customer address records.

{@inheritdoc}

### create

Create a new customer address record.

The created record is returned in the response.

{@inheritdoc}

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "customeraddresses",
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
      "customer": {
        "data": {
          "type": "customers",
          "id": "1"
        }
      },
      "country": {
        "data": {
          "type": "countries",
          "id": "US"
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

Edit a specific customer address record.

The updated record is returned in the response.

{@inheritdoc}

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "customeraddresses",
    "id": "17",
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

### delete

Delete a specific customer address record.

{@inheritdoc}

### delete_list

Delete a collection of customer address records.

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

### customer

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

Retrieve a record of the country assigned to a specific address record.

#### get_relationship

Retrieve the ID of the country assigned to a specific address record.

### customer

#### get_subresource

Retrieve a record of a customer a specific address belongs to.

#### get_relationship

Retrieve the ID of a customer a specific address belongs to.

### owner

#### get_subresource

Retrieve a record that contains information about the user who is assigned as a customer address record owner in the management console.

#### get_relationship

Retrieve the ID of the user who is assigned as a customer address record owner in the management console.

#### update_relationship

Replace the user who is assigned as a customer address record owner in the management console.

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

Retrieve a record of the region assigned to a specific address record.

#### get_relationship

Retrieve the ID of the region assigned to a specific address record.

### systemOrganization

#### get_subresource

Retrieve the record of the organization a specific customer address record belongs to.

#### get_relationship

Retrieve the ID of the organization record which a specific customer address record will belong to.
