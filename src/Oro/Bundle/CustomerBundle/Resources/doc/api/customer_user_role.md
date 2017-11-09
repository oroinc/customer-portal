# Oro\Bundle\CustomerBundle\Entity\CustomerUserRole

## ACTIONS

### get

Retrieve a specific customer user role record.

{@inheritdoc}

### get_list

Retrieve a collection of customer user role records.

The list of records that will be returned, could be limited by filters.

{@inheritdoc}

### create

Create a new customer user role record.

The created record is returned in the response.

{@inheritdoc}

{@request:json_api}
Example:

`</admin/api/customer_user_roles>`

```JSON
{
  "data": {
    "type": "customer_user_roles",
    "attributes": {
      "role": "ROLE_BACKEND_ADMINISTRATOR",
      "label": "Admin",
      "selfManaged": true,
      "public": true
    },
    "relationships": {
      "websites": {
        "data": [         
          {
            "type": "websites",
            "id": "7"
          }
        ]
      },
      "customerUsers": {
        "data": [
          {
            "type": "customer_users",
            "id": "9"
          },
          {
            "type": "customer_users",
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

`</admin/api/customer_user_roles/1>`

```JSON
{
  "data": {
    "type": "customer_user_roles",
    "id": "11",
    "attributes": {
      "role": "ROLE_BACKEND_ADMINISTRATOR",
      "label": "Admin",
      "selfManaged": true,
      "public": true
    },
    "relationships": {
      "websites": {
        "data": [         
          {
            "type": "websites",
            "id": "7"
          }
        ]
      },
      "customerUsers": {
        "data": [
          {
            "type": "customer_users",
            "id": "9"
          },
          {
            "type": "customer_users",
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

The list of records that will be deleted, could be limited by filters.

{@inheritdoc}

## FIELDS

### role

#### create

{@inheritdoc}

**The required field**

#### update

{@inheritdoc}

**Please note:**

*This field is **required** and must remain defined.*

### label

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

Retrieve the customer record assigned to a specific customer user role record.

#### get_relationship

Retrieve the IDs of the customer records assigned to a specific customer user role record.

#### update_relationship

Replace the list of customers assigned to a specific customer user role record.

{@request:json_api}
Example:

`</admin/api/customer_user_roles/1/relationships/customer>`

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

`</admin/api/customer_user_roles/1/relationships/customerUsers>`

```JSON
{
  "data": [
    {
      "type": "customer_users",
      "id": "1"
    },
    {
      "type": "customer_users",
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

`</admin/api/customer_user_roles/1/relationships/customerUsers>`

```JSON
{
  "data": [
    {
      "type": "customer_users",
      "id": "1"
    },
    {
      "type": "customer_users",
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

`</admin/api/customer_user_roles/1/relationships/customerUsers>`

```JSON
{
  "data": [
    {
      "type": "customer_users",
      "id": "1"
    },
    {
      "type": "customer_users",
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

`</api/customer_user_roles/1/relationships/organization>`

```JSON
{
  "data": {
    "type": "organizations",
    "id": "1"
  }
}
```
{@/request}

### websites

#### get_subresource

Retrieve the website records assigned to a specific customer user role record.

#### get_relationship

Retrieve the IDs of website records assigned to a specific customer user role record.

#### update_relationship

Replace the list of website assigned to a specific customer user role record.

{@request:json_api}
Example:

`</admin/api/customer_user_roles/1/relationships/websites>`

```JSON
{
  "data": [
    {
      "type": "websites",
      "id": "2"
    },
    {
      "type": "websites",
      "id": "3"
    }
  ]
}
```
{@/request}

#### add_relationship

Set website records for a specific customer user role record.

{@request:json_api}
Example:

`</admin/api/customer_user_roles/1/relationships/websites>`

```JSON
{
  "data": [
    {
      "type": "websites",
      "id": "2"
    },
    {
      "type": "websites",
      "id": "3"
    }
  ]
}
```
{@/request}

#### delete_relationship

Remove website records from a specific customer user role record.

{@request:json_api}
Example:

`</admin/api/customer_user_roles/1/relationships/websites>`

```JSON
{
  "data": [
    {
      "type": "websites",
      "id": "2"
    },
    {
      "type": "websites",
      "id": "3"
    }
  ]
}
```
{@/request}

