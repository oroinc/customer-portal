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
      "confirmed": true,
      "email": "AmandaFCole@example.org",
      "firstName": "Amanda",     
      "lastName": "Cole",     
      "enabled": true,
      "password": "Password000!"
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
      "confirmed": true,
      "email": "AmandaMCole@example.org",
      "firstName": "Amanda",     
      "lastName": "Cole",     
      "enabled": true
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

### customer

#### create

{@inheritdoc}

**The required field.**

### userRoles

#### create

{@inheritdoc}

**Conditionally required field:**
This field is required when the **enabled** field value is `true`.

### enabled

#### create

{@inheritdoc}

**Note:**
The default value is `true`.

### confirmed

#### create

{@inheritdoc}

**Note:**
The default value is `true`.

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

### passwordChangedAt

#### create, update

{@inheritdoc}

**The read-only field. A passed value will be ignored.**

### passwordRequestedAt

#### create, update

{@inheritdoc}

**The read-only field. A passed value will be ignored.**

### loginCount

#### create, update

{@inheritdoc}

**The read-only field. A passed value will be ignored.**

### lastLogin

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

### organization

#### get_subresource

Retrieve the record of the organization a specific customer user record belongs to.

#### get_relationship

Retrieve the ID of the organization record which a specific customer user record will belong to.

#### update_relationship

Replace the organization a specific customer user record belongs to.

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "organizations",
    "id": "1"
  }
}
```
{@/request}

### owner

#### get_subresource

Retrieve the record of the user who is an owner of a specific customer user record.

#### get_relationship

Retrieve the ID of the user who is an owner of a specific customer user record.

#### update_relationship

Replace the owner of a specific customer user record.

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "users",
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

### salesRepresentatives

#### get_subresource

Retrieve the sales representatives records assigned to a specific customer user record.

#### get_relationship

Retrieve the IDs of sales representatives records assigned to a specific customer user record.

#### update_relationship

Replace the list of sales representatives assigned to a specific customer user record.

{@request:json_api}
Example:

```JSON
{
  "data": [
    {
      "type": "users",
      "id": "1"
    },
    {
      "type": "users",
      "id": "2"
    }
  ]
}
```
{@/request}

#### add_relationship

Set sales representatives records for a specific customer user record.

{@request:json_api}
Example:

```JSON
{
  "data": [
    {
      "type": "users",
      "id": "1"
    },
    {
      "type": "users",
      "id": "2"
    }
  ]
}
```
{@/request}

#### delete_relationship

Remove sales representatives records from a specific customer user record.

{@request:json_api}
Example:

```JSON
{
  "data": [
    {
      "type": "users",
      "id": "1"
    },
    {
      "type": "users",
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

### auth_status

#### get_subresource

Retrieve the customer user's authentication status.

#### get_relationship

Retrieve the ID of the customer user's authentication status.

#### update_relationship

Replace the customer user's authentication status.

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "customeruserauthstatuses",
    "id": "active"
  }
}
```
{@/request}


# Extend\Entity\EV_Cu_Auth_Status

## ACTIONS

### get

Retrieve a specific authentication status record.

The authentication status defines the actuality of the customer user's password, whether it is active, reset, or expired.

### get_list

Retrieve a collection of authentication status records.

The authentication status defines the actuality of the customer user's password, whether it is active, reset, or expired.
