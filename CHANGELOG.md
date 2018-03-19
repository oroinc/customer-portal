## 2.7.0 (Unreleased)

### Changed
#### FrontendBundle
* Changed format of options that prepares `RuleEditorOptionsConfigurator` <sup>[[?]](https://github.com/oroinc/customer-portal/tree/2.7.0/src/Oro/Bundle/FrontendBundle/Form/OptionsConfigurator/RuleEditorOptionsConfigurator.php)</sup> , removed `entities` field and added `supportedNames` and `entityDataProvider`

## 2.6.0 (Unreleased)

### Changed
#### FrontendBundle
* Added `frontend` option for datagrids. This option should be set to `true` for all datagrids are used in the store frontend. For details see [Frontend Datagrids](./src/Oro/Bundle/FrontendBundle/Resources/doc/frontend-access.md#frontend-datagrids)

## 2.5.0 (2017-11-30)
[Show detailed list of changes](incompatibilities-2-5.md)
### Added
#### ACL
* In case when Customer Portal is installed together with CRM, the `Account Manager` role has full permissions for Account and Contact entities. The permissions for the `Account Manager` is not changed if Customer Portal is added to already installed CRM.

## 2.4.0 (2017-09-29)
[Show detailed list of changes](incompatibilities-2-4.md)

### Changed
#### FrontendBundle
* some inline underscore templates were moved to separate .html file for each template.
### Removed
#### CustomerBundle
* removed interface `ConstraintByValidationGroups`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/2.4.0/src/Oro/Bundle/OrderBundle/Validator/Constraints/ConstraintByValidationGroups.php "Oro\Bundle\OrderBundle\Validator\Constraints\ConstraintByValidationGroups")</sup>.
* removed constraint class `OrderAddress`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/2.4.0/src/Oro/Bundle/OrderBundle/Validator/Constraints/OrderAddress.php "Oro\Bundle\OrderBundle\Validator\Constraints\OrderAddress")</sup>.
* removed constraint validator `OrderAddressValidator`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/2.4.0/src/Oro/Bundle/OrderBundle/Validator/Constraints/OrderAddressValidator.php "Oro\Bundle\OrderBundle\Validator\Constraints\OrderAddressValidator")</sup>.
## 2.3.0 (2017-07-28)
[Show detailed list of changes](incompatibilities-2-3.md)
### Changed
#### CustomerBundle
* the DI container parameter `oro_customer.entity.owners` was changed
    - the option `local_level` was renamed to `business_unit`
    - the option `basic_level` was renamed to `user`
### Removed
#### CustomerBundle
* class `RecordOwnerDataListener`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/2.3.0/src/Oro/Bundle/CustomerBundle/EventListener/RecordOwnerDataListener.php "Oro\Bundle\CustomerBundle\EventListener\RecordOwnerDataListener")</sup>
    - constant `OWNER_TYPE_ACCOUNT` was removed, use `OWNER_TYPE_CUSTOMER` instead
## 2.2.0 (2017-05-31)
[Show detailed list of changes](incompatibilities-2-2.md)
