# Oro\Bundle\CustomerBundle\Entity\CustomerUserRole

## ACTIONS

### get

Retrieve a specific customer user role record.

{@inheritdoc}

### get_list

Retrieve a collection of customer user role records.

{@inheritdoc}

### create

Create a new customer user role record.

The created record is returned in the response.

{@inheritdoc}

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "customeruserroles",
    "attributes": {
      "role": "ROLE_BACKEND_ADMINISTRATOR",
      "label": "Admin",
      "selfManaged": true,
      "public": true
    },
    "relationships": {
      "customerUsers": {
        "data": [
          {
            "type": "customerusers",
            "id": "9"
          },
          {
            "type": "customerusers",
            "id": "4"
          }
        ]
      }
    }
  }
}
```
{@/request}

### update

Edit a specific customer user role record.

The updated record is returned in the response.

{@inheritdoc}

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "customeruserroles",
    "id": "11",
    "attributes": {
      "role": "ROLE_BACKEND_ADMINISTRATOR",
      "label": "Admin",
      "selfManaged": true,
      "public": true
    },
    "relationships": {
      "customerUsers": {
        "data": [
          {
            "type": "customerusers",
            "id": "9"
          },
          {
            "type": "customerusers",
            "id": "4"
          }
        ]
      }
    }
  }
}
```
{@/request}

### delete

Delete a specific customer user role record.

{@inheritdoc}

### delete_list

Delete a collection of customer user role records.

{@inheritdoc}

## FIELDS

### role

#### create

{@inheritdoc}

**The required field.**

#### update

{@inheritdoc}

**This field must not be empty, if it is passed.**

### label

#### create

{@inheritdoc}

**The required field.**

#### update

{@inheritdoc}

**This field must not be empty, if it is passed.**

## SUBRESOURCES

### customer

#### get_subresource

Retrieve the customer record assigned to a specific customer user role record.

#### get_relationship

Retrieve the IDs of the customer records assigned to a specific customer user role record.

#### update_relationship

Replace the list of customers assigned to a specific customer user role record.

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

### customerUsers

#### get_subresource

Retrieve the customer user records assigned to a specific customer user role.

#### get_relationship

Retrieve the IDs of the customer user records assigned to a specific customer user role record.

#### update_relationship

Replace the list of customer users assigned to a specific customer user role record.

{@request:json_api}
Example:

```JSON
{
  "data": [
    {
      "type": "customerusers",
      "id": "1"
    },
    {
      "type": "customerusers",
      "id": "4"
    }
  ]
}
```
{@/request}

#### add_relationship

Set customer user records for a specific customer user role record.

{@request:json_api}
Example:

```JSON
{
  "data": [
    {
      "type": "customerusers",
      "id": "1"
    },
    {
      "type": "customerusers",
      "id": "4"
    }
  ]
}
```
{@/request}

#### delete_relationship

Remove customer user records from a specific customer user role record.

{@request:json_api}
Example:

```JSON
{
  "data": [
    {
      "type": "customerusers",
      "id": "1"
    },
    {
      "type": "customerusers",
      "id": "4"
    }
  ]
}
```
{@/request}

### organization

#### get_subresource

Retrieve the record of the organization a specific customer user role record belongs to.

#### get_relationship

Retrieve the ID of the organization record which a specific customer user role record will belong to.

#### update_relationship

Replace the organization a specific customer user role record belongs to.

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

