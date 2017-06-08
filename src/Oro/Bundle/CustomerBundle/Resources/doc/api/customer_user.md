# Oro\Bundle\CustomerBundle\Entity\CustomerUser

## ACTIONS  

### create

{@inheritdoc}

{@request:json_api}

Example:

`</api/customer_users>`

```JSON
{  
   "data":{  
      "type":"customer_users",
      "attributes":{  
         "username":"test2341@test.com",
         "password":"123123123123Aa",
         "email":"test2341@test.com",
         "firstName":"Customer user",
         "lastName":"Customer user"
      },
      "relationships":{  
         "customer":{  
            "data":{  
               "type":"customers",
               "id":"1"
            }
         },
         "roles":{  
            "data":[  
               {  
                  "type":"customer_user_roles",
                  "id":"1"
               }
            ]
         }
      }
   }
}
```
{@/request}
