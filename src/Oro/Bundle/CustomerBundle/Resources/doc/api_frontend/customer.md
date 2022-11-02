# Oro\Bundle\CustomerBundle\Entity\Customer

## ACTIONS

### get

Retrieve a specific customer record.

{@inheritdoc}

### get_list

Retrieve a collection of customer records.

{@inheritdoc}

## SUBRESOURCES

### addresses

#### get_subresource

Retrieve records of addresses assigned to a specific customer record.

#### get_relationship

Retrieve IDs of address records assigned to a specific customer record.

### children

#### get_subresource

Retrieve a set of records of child customers assigned to a specific customer record.

#### get_relationship

Retrieve IDs of child customers records assigned to a specific customer record.

### group

#### get_subresource

Retrieve the customer group record a specific customer record is assigned to.

#### get_relationship

Retrieve the ID of the customer group record which a specific customer record is assigned to.

### parent

#### get_subresource

Retrieve the parent customer assigned to a specific customer record.

#### get_relationship

Retrieve the ID of the parent customer record assigned to a specific customer record.

### users

#### get_subresource

Retrieve the customer user records assigned to a specific customer record.

#### get_relationship

Retrieve the IDs of the customer user records assigned to a specific customer record.
