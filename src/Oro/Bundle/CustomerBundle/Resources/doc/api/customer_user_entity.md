# Oro\Bundle\CustomerBundle\Entity\CustomerUser

## ACTIONS  

### create

{@inheritdoc}

Create customer user.
Sample data of create request:

`{  
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
}`