UPGRADE FROM 2.2 to 2.3
=======================

**IMPORTANT**
-------------

The class `Oro\Bundle\SecurityBundle\SecurityFacade`, services `oro_security.security_facade` and `oro_security.security_facade.link`, and TWIG function `resource_granted` were marked as deprecated.
Use services `security.authorization_checker`, `security.token_storage`, `oro_security.token_accessor`, `oro_security.class_authorization_checker`, `oro_security.request_authorization_checker` and TWIG function `is_granted` instead.
In controllers use `isGranted` method from `Symfony\Bundle\FrameworkBundle\Controller\Controller`.
The usage of deprecated service `security.context` (interface `Symfony\Component\Security\Core\SecurityContextInterface`) was removed as well.
All existing classes were updated to use new services instead of the `SecurityFacade` and `SecurityContext`:

- service `security.authorization_checker`
    - implements `Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface`
    - the property name in classes that use this service is `authorizationChecker`
- service `security.token_storage`
    - implements `Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface`
    - the property name in classes that use this service is `tokenStorage`
- service `oro_security.token_accessor`
    - implements `Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface`
    - the property name in classes that use this service is `tokenAccessor`
- service `oro_security.class_authorization_checker`
    - implements `Oro\Bundle\SecurityBundle\Authorization\ClassAuthorizationChecker`
    - the property name in classes that use this service is `classAuthorizationChecker`
- service `oro_security.request_authorization_checker`
    - implements `Oro\Bundle\SecurityBundle\Authorization\RequestAuthorizationChecker`
    - the property name in classes that use this service is `requestAuthorizationChecker`

CustomerBundle
--------------
- Class `Oro\Bundle\CustomerBundle\Acl\Group\AclGroupProvider`
    - removed implementation of `Symfony\Component\DependencyInjection\ContainerAwareInterface`
    - removed method `getSecurityFacade`
- Class `Oro\Bundle\CustomerBundle\Acl\Voter\CustomerUserRoleVoter`
    - method `getSecurityFacade` was replaces with `getAuthorizationChecker` and `getTokenAccessor`
- Class `Oro\Bundle\CustomerBundle\Acl\Voter\CustomerVoter`
    - method `getSecurityFacade` was replaces with `getAuthorizationChecker`
- Class `Oro\Bundle\CustomerBundle\Controller\Frontend\CustomerUserRoleController`
    - removed method `getSecurityFacade`
- Class `Oro\Bundle\CustomerBundle\EventListener\RecordOwnerDataListener`
    - removed method `getSecurityContext`
- Class `Oro\Bundle\CustomerBundle\Form\Type\FrontendCustomerUserRoleSelectType`
    - changed the constructor signature: parameter `Registry $registry` was replaced with `ManagerRegistry $registry`
- Class `Oro\Bundle\CustomerBundle\EventListener\OrmDatasourceAclListener`
    - changed the constructor signature: parameter `MetadataProviderInterface $metadataProvider` was replaced with `OwnershipMetadataProviderInterface $metadataProvider`
- Class `Oro\Bundle\CustomerBundle\EventListener\RecordOwnerDataListener`
    - constant `OWNER_TYPE_ACCOUNT` was renamed to `OWNER_TYPE_CUSTOMER`
- Class `Oro\Bundle\CustomerBundle\Form\Handler\AbstractCustomerUserRoleHandler`
    - changed the signature of method `setChainMetadataProvider`: parameter `ChainMetadataProvider $chainMetadataProvider` was replaced with `ChainOwnershipMetadataProvider $chainMetadataProvider`
- Class `Oro\Bundle\CustomerBundle\Owner\Metadata\FrontendOwnershipMetadataProvider`
    - removed property `localLevelClass`
    - removed property `basicLevelClass`
    - removed method `getSecurityConfigProvider`
    - changed the constructor signature: new signature is `__construct(array $owningEntityNames, ConfigManager $configManager, EntityClassResolver $entityClassResolver, TokenAccessorInterface $tokenAccessor, CacheProvider $cache)`
- Class `Oro\Bundle\CustomerBundle\Owner\EntityOwnershipDecisionMaker`
    - changed the constructor signature: new signature is `__construct(OwnerTreeProviderInterface $treeProvider, ObjectIdAccessor $objectIdAccessor, EntityOwnerAccessor $entityOwnerAccessor, OwnershipMetadataProviderInterface $ownershipMetadataProvider, TokenAccessorInterface $tokenAccessor, ManagerRegistry $doctrine)`
- Class `Oro\Bundle\CustomerBundle\Owner\FrontendOwnerTreeProvider`
    - changed the constructor signature: parameter `MetadataProviderInterface $ownershipMetadataProvider` was replaced with `OwnershipMetadataProviderInterface $ownershipMetadataProvider`
- The DI container parameter `oro_customer.entity.owners` was changed
    - the option `local_level` was renamed to `business_unit`
    - the option `basic_level` was renamed to `user`
