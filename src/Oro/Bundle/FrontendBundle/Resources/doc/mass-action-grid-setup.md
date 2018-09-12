# How to organize setup mass action on datagrid

How to setup datagrid mass actions in Management Console described in [Mass Action Datagrid Extension](../../../../../../../platform/src/Oro/Bundle/DataGridBundle/Resources/doc/backend/extensions/mass_action.md) article.

Let's consider using mass delete of customer users on Front Store datagrid page.
First of all you should check that `oro_datagrid_front_mass_action` route is enabled for frontend. In `routing.yml` 
add `frontend: true` option:

``` yml
oro_datagrid_front_mass_action:
    ...
   options:
       frontend: true 
```

In the corresponding grid configuration (`datagrids.yml`) specify following options:

``` yml
frontend-customer-customer-user-grid:
    ...
   mass_actions:
        delete:
            label: Delete
            type: delete
            icon: trash
            entity_name: Oro\Bundle\CustomerBundle\Entity\CustomerUser
            data_identifier: customerUser.id
            name: delete
            frontend_type: delete-mass
            route: oro_datagrid_front_mass_action
            acl_resource:  oro_customer_frontend_customer_user_delete
            handler: oro_customer.datagrid.extension.mass_action.handler.delete
```
As we see we should add `mass_actions` section with params:
 - `delete` specifies type of mass action
 - `entity_name`, `data_identifier` describes main entity and its identifier
 - `frontend_type: delete-mass` point to use predefined action located in `DataGridBundle/Extension/MassAction/Actions/Ajax/DeleteMassAction.php`
 - `route` - route that used for mass action processing. In our case: `oro_datagrid_front_mass_action`
 - `acl_resource` - ACL resource identifier
 - `handler` - service, responsible for mass delete handling. For example, logged user should not allowed to delete himself. For this 
 case we extending `DeleteMassActionHandler` with our custom logic:
 
```php

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\DataGridBundle\Extension\MassAction\DeleteMassActionHandler;

class CustomersDeleteActionHandler extends DeleteMassActionHandler
{
 /**
  * {@inheritdoc}
  */
 protected function isDeleteAllowed($entity)
 {
     /** @var CustomerUser $entity */
     if ($this->tokenAccessor->getUserId() === $entity->getId()) {
         return false;
     }

     return parent::isDeleteAllowed($entity);
 }
}
```
and register your service:
 
``` yml
oro_customer.datagrid.extension.mass_action.handler.delete:
    class: Oro\Bundle\CustomerBundle\Datagrid\Extension\MassAction\CustomersDeleteActionHandler
    parent: oro_datagrid.extension.mass_action.handler.delete
```

