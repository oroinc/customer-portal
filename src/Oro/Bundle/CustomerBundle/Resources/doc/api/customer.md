# Oro\Bundle\CustomerBundle\Entity\Customer

## ACTIONS

### get

Retrieve a specific customer record.

{@inheritdoc}

### get_list

Retrieve a collection of customer records.

{@inheritdoc}

### create

Create a new customer record.

The created record is returned in the response.

{@inheritdoc}

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "customers",
    "attributes": {
      "name": "Company AB"
    },
    "relationships": {     
      "children": {
        "data": [
          {
            "type": "customers",
            "id": "2"
          },
          {
            "type": "customers",
            "id": "3"
          }
        ]
      },
      "group": {
        "data": {
          "type": "customer_groups",
          "id": "1"
        }
      },
      "users": {
        "data": [
          {
            "type": "customer_users",
            "id": "1"
          },
          {
            "type": "customer_users",
            "id": "2"
          }
        ]
      }     
    }
  }
}
```
{@/request}

### update

Edit a specific customer record.

The updated record is returned in the response.

{@inheritdoc}

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "customers",
    "id": "1",
    "attributes": {
      "name": "Company AC"
    },
    "relationships": {     
      "children": {
        "data": [
          {
            "type": "customers",
            "id": "2"
          }
        ]
      },
      "group": {
        "data": {
          "type": "customer_groups",
          "id": "1"
        }
      },
      "users": {
        "data": [
          {
            "type": "customer_users",
            "id": "1"
          }
        ]
      }     
    }
  }
}
```
{@/request}

### delete

Delete a specific customer record.

{@inheritdoc}

### delete_list

Delete a collection of customer records.

{@inheritdoc}

## FIELDS

### name

#### create

{@inheritdoc}

**The required field**

#### update

{@inheritdoc}

**Please note:**

*This field is **required** and must remain defined.*

## SUBRESOURCES

### children

#### get_subresource

Retrieve a set of records of child customers assigned to a specific customer record.

#### get_relationship

Retrieve IDs of child customers records assigned to a specific customer record.

#### update_relationship

Replace the list of child customers assigned to a specific customer record.

{@request:json_api}
Example:

```JSON
{
  "data": [
    {
      "type": "customers",
      "id": "2"
    },
    {
      "type": "customers",
      "id": "3"
    }
  ]
}
```
{@/request}

#### add_relationship

Set child customers records for a specific customer record.

{@request:json_api}
Example:

```JSON
{
  "data": [
    {
      "type": "customers",
      "id": "2"
    },
    {
      "type": "customers",
      "id": "3"
    }
  ]
}
```
{@/request}

#### delete_relationship

Remove child customers records from a specific customer record.

{@request:json_api}
Example:

```JSON
{
  "data": [
    {
      "type": "customers",
      "id": "2"
    },
    {
      "type": "customers",
      "id": "3"
    }
  ]
}
```
{@/request}

### group

#### get_subresource

Retrieve the customer groups records a specific customer record is assigned to.

#### get_relationship

Retrieve the IDs of the customer group records which a specific customer record is assigned to.

#### update_relationship

Replace the list of customer group records a specific customer record is assigned to.

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "customer_groups",
    "id": "2"
  }
}
```
{@/request}

### internal_rating

#### get_subresource

Retrieve a record of internal rating assigned to a specific customer record.

#### get_relationship

Retrieve the ID of internal rating record assigned to a specific customer record.

#### update_relationship

Replace the internal rating record assigned to a specific customer record

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "customer_rating",
    "id": "3_of_5"
  }
}
```
{@/request}

### organization

#### get_subresource

Retrieve the record of the organization a specific customer record belongs to.

#### get_relationship

Retrieve the ID of the organization record which a specific customer record belongs to.

#### update_relationship

Replace the organization a specific customer record belongs to.

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

Retrieve the record of the user who is an owner of a specific customer record.

#### get_relationship

Retrieve the ID of the user who is an owner of a specific customer record.

#### update_relationship

Replace the owner of a specific customer record.

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

### parent

#### get_subresource

Retrieve the parent customer assigned to a specific customer record.

#### get_relationship

Retrieve the ID of the parent customer record assigned to a specific customer record.

#### update_relationship

Replace the parent customer assigned to a specific customer record.

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "customers",
    "id": "4"
  }
}
```
{@/request}

### users

#### get_subresource

Retrieve the customer user records assigned to a specific customer record.

#### get_relationship

Retrieve the IDs of the customer user records assigned to a specific customer record.

#### update_relationship

Replace the list of customer users assigned to a specific customer record.

{@request:json_api}
Example:

```JSON
{
  "data": [
    {
      "type": "customer_users",
      "id": "3"
    },
    {
      "type": "customer_users",
      "id": "11"
    }
  ]
}
```
{@/request}

#### add_relationship

Set customer user records for a specific customer record.

{@request:json_api}
Example:

```JSON
{
  "data": [
    {
      "type": "customer_users",
      "id": "3"
    },
    {
      "type": "customer_users",
      "id": "11"
    }
  ]
}
```
{@/request}

#### delete_relationship

Remove customer user records from a specific customer record.

{@request:json_api}
Example:

```JSON
{
  "data": [
    {
      "type": "customer_users",
      "id": "3"
    },
    {
      "type": "customer_users",
      "id": "11"
    }
  ]
}
```
{@/request}

### salesRepresentatives

#### get_subresource

Retrieve a record of sales representatives assigned to a specific customer record.

#### get_relationship

Retrieve the IDs of the sales representatives records assigned to a specific customer record.

#### update_relationship

Replace the list of sales representatives assigned to a specific customer record.

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
      "id": "3"
    }
  ]
}
```
{@/request}

#### add_relationship

Set sales representatives records for a specific customer record.

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
      "id": "3"
    }
  ]
}
```
{@/request}

#### delete_relationship

Remove the sales representatives records from a specific customer record.

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
      "id": "3"
    }
  ]
}
```
{@/request}

### paymentTerm

#### get_subresource

Retrieve a record of payment term assigned to a specific customer record.

#### get_relationship

Retrieve ID of payment term record assigned to a specific customer record.

#### update_relationship

Replace the payment term assigned to a specific customer record.

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "paymentterms",
    "id": "2"
  }
}
```
{@/request}

# Extend\Entity\EV_Acc_Internal_Rating

## ACTIONS

### get

Retrieve a specific customer rating record.

Customer rating defines an internal customer's rank ("1 of 5", "5 of 5" ).

### get_list

Retrieve a collection of customer rating records.

Customer rating defines an internal customer's rank ("1 of 5", "5 of 5" ).
