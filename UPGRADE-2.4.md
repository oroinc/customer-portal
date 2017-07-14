UPGRADE FROM 2.3 to 2.4
=======================

Some inline underscore templates from `FrontendBundle` bundle, were moved to separate .html file for each template:

CustomerBundle
--------------
- Removed interface `Oro\Bundle\OrderBundle\Validator\Constraints\ConstraintByValidationGroups`.
- Removed constraint class `Oro\Bundle\OrderBundle\Validator\Constraints\OrderAddress`.
- Removed constraint validator `Oro\Bundle\OrderBundle\Validator\Constraints\OrderAddressValidator`.
