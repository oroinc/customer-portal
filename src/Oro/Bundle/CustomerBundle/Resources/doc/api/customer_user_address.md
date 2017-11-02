# Oro\Bundle\CustomerBundle\Entity\CustomerUserAddress

## ACTIONS  

### get

Retrieve a specific <a href="https://www.orocommerce.com/documentation/current/user-guide/getting-started/common-actions/manage-address-book#user-guide-getting-started-address-book">customer user address</a> record.

{@inheritdoc}

### get_list

Retrieve a collection of <a href="https://www.orocommerce.com/documentation/current/user-guide/getting-started/common-actions/manage-address-book#user-guide-getting-started-address-book">customer user address</a> records.

{@inheritdoc}

The list of records that will be returned can be limited by filters.

### create

Create a new <a href="https://www.orocommerce.com/documentation/current/user-guide/getting-started/common-actions/manage-address-book#user-guide-getting-started-address-book">customer user address</a> record.

{@inheritdoc}

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
        }
      }
    }
}
```
{@/request}

### update

Update a specific <a href="https://www.orocommerce.com/documentation/current/user-guide/getting-started/common-actions/manage-address-book#user-guide-getting-started-address-book">customer user address</a> record. 

{@inheritdoc}

The request can contain only the fields we wish to update.

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

Delete a specific <a href="https://www.orocommerce.com/documentation/current/user-guide/getting-started/common-actions/manage-address-book#user-guide-getting-started-address-book">customer user address</a> record.

{@inheritdoc}

### delete_list

Delete a collection of <a href="https://www.orocommerce.com/documentation/current/user-guide/getting-started/common-actions/manage-address-book#user-guide-getting-started-address-book">customer user address</a> records.

{@inheritdoc}

The list of records that will be deleted must be limited by filters.

## SUBRESOURCES

### country

#### get_subresource

Retrieve a record that contains information about the country that is mentioned in the customer user address.

#### get_relationship

Retrieve the id of the country that is mentioned in the customer user address.

#### update_relationship

Replace the country in the specific customer user address.

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

Retrieve a record that contains information about a customer user who is assigned as a customer user address record owner in the front store.

#### get_relationship

Retrieve the id of the customer user who is assigned as a customer user address record owner in the front store.

#### update_relationship

Replace the frontend owner - a customer user who is assigned as a customer user address record owner in the front store.

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

Retrieve a record that contains information about the user who is assigned as a customer user address record owner in the management console.

#### get_relationship

Retrieve the id of the user who is assigned as a customer user address record owner in the management console.

#### update_relationship

Replace the user who is assigned as a customer user address record owner in the management console.

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

Retrieve a record that contains information about the region mentioned in a specific customer user address.

#### get_relationship

Retrieve the id of the region mentioned in a specific customer user address.

#### update_relationship

Replace the region mentioned in a specific customer user address.

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

Retrieve a record that contains information about the organization that is linked to a specific customer user address record.

#### get_relationship

Retrieve the id of the organization that is linked to a specific customer user address record.
