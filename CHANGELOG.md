## 2.4.0 (Unreleased)
[Show detailed list of changes](file-incompatibilities-2-4-0.md)
### Changed
* **FrontendBundle** some inline underscore templates were moved to separate .html file for each template.
### Removed
* **CustomerBundle** removed interface `Oro\Bundle\OrderBundle\Validator\Constraints\ConstraintByValidationGroups`.
* **CustomerBundle** removed constraint class `Oro\Bundle\OrderBundle\Validator\Constraints\OrderAddress`.
* **CustomerBundle** removed constraint validator `Oro\Bundle\OrderBundle\Validator\Constraints\OrderAddressValidator`.
## 2.3.0 (2017-07-28)
[Show detailed list of changes](file-incompatibilities-2-3-0.md)
### Changed
* **CustomerBundle** the DI container parameter `oro_customer.entity.owners` was changed
    - the option `local_level` was renamed to `business_unit`
    - the option `basic_level` was renamed to `user`
### Removed
* **CustomerBundle** class `Oro\Bundle\CustomerBundle\EventListener\RecordOwnerDataListener`
    - constant `OWNER_TYPE_ACCOUNT` was removed, use `OWNER_TYPE_CUSTOMER` instead
## 2.2.0 (2017-05-31)
[Show detailed list of changes](file-incompatibilities-2-2-0.md)
