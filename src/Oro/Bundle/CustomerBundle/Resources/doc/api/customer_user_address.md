# Oro\Bundle\CustomerBundle\Entity\CustomerUserAddress

## ACTIONS  

### get

Retrieve a specific customer user address record.

### get_list

Retrieve a collection of customer user address records.

The list of records that will be returned can be limited by filters.

### create

Create a new customer user address record.

The created record is returned in the response, with a 201 Created status code.

{@request:json_api}
Example:

`</admin/api/customer_user_addresses>`

```JSON
{
  "data":
    {
      "type": "customer_user_addresses",
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
        "street2": null
      },
      "relationships": {
        "country": {
          "data": {
            "type": "countries",
            "id": "US"
          }
        },
        "frontendOwner": {
          "data": {
            "type": "customer_users",
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
        },
        "systemOrganization": {
          "data": {
            "type": "organizations",
            "id": "1"
          }
        }
      }
    }
 
}
```
{@/request}

### update

Update a specific customer user address record. The request can contain only the fields we wish to update.

The updated record is returned in the response.

{@request:json_api}
Example:

`</admin/api/customer_user_addresses/17>`

```JSON
{
   "data":{
      "type":"customer_user_addresses",
      "id":"17",
      "attributes":{
         "phone":null,
         "primary":true,
         "label":"Primary address",
         "street":"23400 Caldwell Road",
         "street2":null,
         "city":"Rochester",
         "postalCode":"14608",
         "organization":null,
         "namePrefix":null,
         "firstName":"Branda",
         "middleName":null,
         "lastName":"Sanborn",
         "nameSuffix":null
      },
      "relationships":{
         "frontendOwner":{
            "data":{
               "type":"customer_users",
               "id":"2"
            }
         },
         "owner":{
            "data":{
               "type":"users",
               "id":"1"
            }
         },
         "systemOrganization":{
            "data":{
               "type":"organizations",
               "id":"1"
            }
         },
         "country":{
            "data":{
               "type":"countries",
               "id":"RO"
            }
         },
         "region":{
            "data":{
               "type":"regions",
               "id":"RO-MS"
            }
         }
      }
   }
}
```
{@/request}

### delete

Delete a specific customer user address record.

### delete_list

Delete a collection of customer user addresses records.

The list of records that will be deleted must be limited by filters.

## SUBRESOURCES

### country

#### get_subresource

Retrieve a record containing information about the country assigned to a specific customer user address.

#### get_relationship

Retrieve the id of the country assigned to a specific customer user address.

#### update_relationship

Replace the country of a specific customer user address record belongs to.

{@request:json_api}
Example:

`</admin/api/customer_user_addresses/12/relationships/country>`

```JSON
{
  "data": {
    "type": "countries",
    "id": "RO"
  }
}
```
{@/request}

### frontendOwner

#### get_subresource

Retrieve a record containing information about the frontend owner (of type customer_users) assigned to a specific customer user address.

#### get_relationship

Retrieve the id of the frontend owner (of type customer_users) assigned to a specific customer user address.

#### update_relationship

Replace the frontend owner (of type customer_users) of a specific customer user address record belongs to.

{@request:json_api}
Example:

`</admin/api/customer_user_addresses/12/relationships/frontendOwner>`

```JSON
{
  "data": {
    "type": "customer_users",
    "id": "11"
  }
}
```
{@/request}

### owner

#### get_subresource

Retrieve a record containing information about the owner (of type users) assigned to a specific customer user address.

#### get_relationship

Retrieve the id of the owner (of type users) assigned to a specific customer user address.

#### update_relationship

Replace the owner (of type users) of a specific customer user address record belongs to.

{@request:json_api}
Example:

`</admin/api/customer_user_addresses/12/relationships/owner>`

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

Retrieve a record containing information about the region assigned to a specific customer user address.

#### get_relationship

Retrieve the id of the region assigned to a specific customer user address.

#### update_relationship

Replace the region of a specific customer user address record belongs to.

{@request:json_api}
Example:

`</admin/api/customer_user_addresses/12/relationships/region>`

```JSON
{
  "data": {
    "type": "regions",
    "id":"RO-MS"
  }
}
```
{@/request}

### systemOrganization

#### get_subresource

Retrieve a record containing information about the organization assigned to a specific customer user address.

#### get_relationship

Retrieve the id of the organization assigned to a specific customer user address.

#### update_relationship

Replace the organization of a specific customer user address record belongs to.

{@request:json_api}
Example:

`</admin/api/customer_user_addresses/12/relationships/systemOrganization>`

```JSON
{
  "data": {
    "type": "organizations",
    "id": "2"
  }
}
```
{@/request}
