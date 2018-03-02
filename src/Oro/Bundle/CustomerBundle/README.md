OroCustomerBundle
====================

Description:
------------

Bundle provides functionality used to manage B2B customer, customer groups and customer user entities.
This bundle also should contain any code used to handle associations between accounts and
similar customer related functionality.

Bundle responsibilities:
------------------------

- Customer User CRUD.
- Possibility to assign Roles to Customer Users.
- Activate and deactivate Customer Users.
- Send welcome email.
- Password edit and automatic password generation for new Customer User.

Expected dependencies:
----------------------


ACL:
----

The `OroCustomerBundle` extends security model of `OroSecurityBundle` for entities which should be accessible for Customer Users on front store.
It adds few new fields to ownership configuration of entities.

Example of configuration of frontend permissions for entity. It can be described additionally to basic ownership configuration.

``` php

<?php
....

 /**
 * @ORM\Entity()
 * @Config(
 *      defaultValues={
 *          "ownership"={
 *              "frontend_owner_type"="FRONTEND_USER",
 *              "frontend_owner_field_name"="customerUser",
 *              "frontend_owner_column_name"="customer_user_id",
 *              "frontend_customer_field_name"="customer",
 *              "frontend_customer_column_name"="customer_id"
 *          },
 *          "security"={
 *              "type"="ACL",
 *              "group_name"="commerce",
 *          },
 *      }
 * )
 */
 class SomeEntity extends ExtendSomeEntity
 {
     /**
      * @var Customer
      *
      * @ORM\ManyToOne(targetEntity="Oro\Bundle\CustomerBundle\Entity\Customer")
      * @ORM\JoinColumn(name="customer_id", referencedColumnName="id", onDelete="SET NULL")
      */
     protected $customer;
     
     /**
      * @var CustomerUser
      *
      * @ORM\ManyToOne(targetEntity="Oro\Bundle\CustomerBundle\Entity\CustomerUser")
      * @ORM\JoinColumn(name="customer_user_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
      */
     protected $customerUser;
 ...
 }
 
 ```
