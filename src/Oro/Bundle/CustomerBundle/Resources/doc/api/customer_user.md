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
      "username": "AmandaFCole@example.org",
      "password": "Password000!"
    },
    "relationships": {
      "roles": {
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
      },      
      "website": {
        "data": {
          "type": "websites",
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
      "enabled": true,      
      "username": "AmandaMCole@example.org"
    },
    "relationships": {
      "roles": {
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
      },      
      "website": {
        "data": {
          "type": "websites",
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

### website

#### create

{@inheritdoc}

**The required field**

### customer

#### create

{@inheritdoc}

**The required field**

### roles

#### create

{@inheritdoc}

**Conditionally required field:**
This field is required when "enabled" field value is "true".

### enabled

#### create

{@inheritdoc}

'true' by default

### confirmed

#### create

{@inheritdoc}

'true' by default

### email

#### create

{@inheritdoc}

**The required field**

#### update

{@inheritdoc}

**Please note:**

*This field is **required** and must remain defined.*

### username

#### create

{@inheritdoc}

**The required field**

#### update

{@inheritdoc}

**Please note:**

*This field is **required** and must remain defined.*

### password

#### create

{@inheritdoc}

**The required field**

### firstName

#### create

{@inheritdoc}

**The required field**

#### update

{@inheritdoc}

**Please note:**

*This field is **required** and must remain defined.*

### lastName

#### create

{@inheritdoc}

**The required field**

#### update

{@inheritdoc}

**Please note:**

*This field is **required** and must remain defined.*

## SUBRESOURCES

### customer

#### get_subresource

Retrieve the customer record a specific customer user record is assigned to.

#### get_relationship

Retrieve the ID of the customer record which a specific customer user record is assigned to.

#### update_relationship

Replace customer record a specific customer user record is assigned to.

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

### website

#### get_subresource

Retrieve a record of website assigned to a specific customer user record.

#### get_relationship

Retrieve IDs of website records assigned to a specific customer user record.

#### update_relationship

Replace a website assigned to a specific customer user record.

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "websites",
    "id": "1"
  }
}
```
{@/request}

### roles

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
