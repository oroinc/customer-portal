# Oro\Bundle\CustomerBundle\Entity\Customer

## ACTIONS  

### create

{@inheritdoc}

{@request:json_api}

Example:

`</api/customers>`

```JSON
{  
    "data":{  
       "type":"customers",
       "attributes":{  
          "name":"new customer"
       },
       "relationships":{  
          "parent":{  
             "data":{  
                "type":"customers",
                "id":"1"
             }
          },
          "owner":{  
             "data":{  
                "type":"users",
                "id":"1"
             }
          },
          "organization":{  
             "data":{  
                "type":"organizations",
                "id":"1"
             }
          },
          "salesRepresentatives":{  
             "data":[  
                {  
                   "type":"users",
                   "id":"1"
                }
             ]
          },
          "internal_rating":{  
             "data":{  
                "type":"customer_rating",
                "id":"1_of_5"
             }
          },
          "group":{  
             "data":{  
                "type":"customer_groups",
                "id":"1"
             }
          }
       }
    }
 }
```
{@/request}
