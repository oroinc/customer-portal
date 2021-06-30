# Oro\Bundle\CustomerBundle\Entity\CustomerUser

## ACTIONS

### get

Retrieve a specific customer user record.

{@inheritdoc}

### get_list

Retrieve a collection of customer user records.

{@inheritdoc}

### create

Create a new customer user record.

The created record is returned in the response.

{@inheritdoc}

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "customerusers",
    "attributes": {
      "email": "AmandaFCole@example.org",
      "firstName": "Amanda",
      "lastName": "Cole",
      "password": "Password000!"
    }
  }
}
```
{@/request}

### update

Edit a specific customer user record.

The updated record is returned in the response.

{@inheritdoc}

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "customerusers",
    "id": "1",
    "attributes": {
      "email": "AmandaMCole@example.org",
      "firstName": "Amanda",
      "lastName": "Cole"
    },
    "relationships": {
      "userRoles": {
        "data": [
          {
            "type": "customeruserroles",
            "id": "1"
          }
        ]
      },
      "customer": {
        "data": {
          "type": "customers",
          "id": "1"
        }
      }
    }
  }
}
```
{@/request}

### delete

Delete a specific customer user record.

{@inheritdoc}

### delete_list

Delete a collection of customer user records.

{@inheritdoc}

## FIELDS

### userRoles

#### update

{@inheritdoc}

**Note:**
At least one role must be assigned to an user.

### email

#### create

{@inheritdoc}

**The required field.**

#### update

{@inheritdoc}

**This field must not be empty, if it is passed.**

### password

#### create

{@inheritdoc}

### firstName

#### create

{@inheritdoc}

**The required field.**

#### update

{@inheritdoc}

**This field must not be empty, if it is passed.**

### lastName

#### create

{@inheritdoc}

**The required field.**

#### update

{@inheritdoc}

**This field must not be empty, if it is passed.**

### enabled

#### create, update

{@inheritdoc}

**The read-only field. A passed value will be ignored.**

### confirmed

#### create, update

{@inheritdoc}

**The read-only field. A passed value will be ignored.**

## SUBRESOURCES

### customer

#### get_subresource

Retrieve the customer record a specific customer user record is assigned to.

#### get_relationship

Retrieve the ID of the customer record which a specific customer user record is assigned to.

#### update_relationship

Replace the customer record a specific customer user record is assigned to.

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "customers",
    "id": "1"
  }
}
```
{@/request}

### userRoles

#### get_subresource

Retrieve the role records assigned to a specific customer user record.

#### get_relationship

Retrieve the IDs of role records records assigned to a specific customer user record.

#### update_relationship

Replace the list of role records assigned to a specific customer user record.

{@request:json_api}
Example:

```JSON
{
  "data": [
    {
      "type": "customeruserroles",
      "id": "1"
    },
    {
      "type": "customeruserroles",
      "id": "2"
    }
  ]
}
```
{@/request}

#### add_relationship

Set role records for a specific customer user record.

{@request:json_api}
Example:

```JSON
{
  "data": [
    {
      "type": "customeruserroles",
      "id": "2"
    }
  ]
}
```
{@/request}

#### delete_relationship

Remove role records from a specific customer user record.

{@request:json_api}
Example:

```JSON
{
  "data": [
    {
      "type": "customeruserroles",
      "id": "2"
    }
  ]
}
```
{@/request}

### addresses

#### get_subresource

Retrieve a record of address assigned to a specific customer user record.

#### get_relationship

Retrieve IDs of address records assigned to a specific customer user record.
