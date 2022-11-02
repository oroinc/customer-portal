# Oro\Bundle\CustomerBundle\Entity\CustomerGroup

## ACTIONS

### get

Retrieve a specific customer group record.

{@inheritdoc}

### get_list

Retrieve a collection of customer group records.

{@inheritdoc}

### create

Create a new customer group record.

The created record is returned in the response.

{@inheritdoc}

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "customergroups",
    "attributes": {
      "name": "Guests"
    }
  }
}
```
{@/request}

### update

Edit a specific customer group record.

The updated record is returned in the response.

{@inheritdoc}

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "customergroups",
    "id": "1",
    "attributes": {
      "name": "Guests"
    }
  }
}
```
{@/request}

### delete

Delete a specific customer group record.

{@inheritdoc}

### delete_list

Delete a collection of customer group records.

{@inheritdoc}

## FIELDS

### name

#### create

{@inheritdoc}

**The required field.**

#### update

{@inheritdoc}

**This field must not be empty, if it is passed.**

## SUBRESOURCES

### organization

#### get_subresource

Retrieve the record of the organization a specific customer group record belongs to.

#### get_relationship

Retrieve the ID of the organization record which a specific customer group record belongs to.

#### update_relationship

Replace the organization a specific customer group record belongs to.

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

Retrieve the record of the user who is an owner of a specific customer group record.

#### get_relationship

Retrieve the ID of the user who is an owner of a specific customer group record.

#### update_relationship

Replace the owner of a specific customer group record.

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "users",
    "id": "5"
  }
}
```
{@/request}

### paymentTerm

#### get_subresource

Retrieve a record of payment term assigned to a specific customer group record.

#### get_relationship

Retrieve ID of payment term record assigned to a specific customer group record.

#### update_relationship

Replace the payment term assigned to a specific customer group record.

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
