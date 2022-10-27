# Oro\Bundle\CustomerBundle\Api\Model\Login

## ACTIONS

### create

Validates the customer user email and password, and if the credentials are valid, returns the API access key
that can be used for subsequent API requests.

{@request:json_api}
Example of the request:

```JSON
{
  "meta": {
    "email": "user@example.com",
    "password": "123"
  }
}
```

Example of the response:

```JSON
{
  "meta": {
    "apiKey": "22b7172bbf9cdcfaa7bac067dabcb07d358ce511"
  }
}
```
{@/request}

## FIELDS

### email

The customer user email.

**The required field.**

### password

The customer user password.

**The required field.**

### apiKey

The API access key.
