UPGRADE FROM 1.0 to 1.1
=======================

General
-------
* Changed minimum required `php` version to **7.0**.
* Updated dependency to [fxpio/composer-asset-plugin](https://github.com/fxpio/composer-asset-plugin) composer plugin to version **1.3**.
* Composer updated to version **1.4**.
```
    composer self-update
    composer global require "fxp/composer-asset-plugin"
```
* For upgrade from **1.0** use the command:
```bash
php app/console oro:platform:update --env=prod --force
```

CommerceMenuBundle
-------------
* The class [`UpdateEntityConfigWarmer`](https://github.com/orocommerce/orocommerce/tree/1.0.0/src/Oro/Bundle/CommerceMenuBundle/CacheWarmer/UpdateEntityConfigWarmer.php "Oro\Bundle\CommerceMenuBundle\CacheWarmer\UpdateEntityConfigWarmer") was removed.
* The method [`MenuExtension::__construct`](https://github.com/orocrm/customer-portal/tree/2.1.0/src/Oro/Bundle/CommerceMenuBundle/Twig/MenuExtension.php "Oro\Bundle\CommerceMenuBundle\Twig\MenuExtension") has been updated. Pass `Symfony\Component\DependencyInjection\ContainerInterface` as a first argument of the method instead of `Knp\Menu\Matcher\MatcherInterface`.

CustomerBundle
-------------
* The following classes were removed:
    - [`CustomerGroupController`](https://github.com/orocommerce/orocommerce/tree/1.0.0/src/Oro/Bundle/CustomerBundle/Controller/Api/Rest/CustomerGroupController.php "Oro\Bundle\CustomerBundle\Controller\Api\Rest\CustomerGroupController")
    - [`CustomerUserController`](https://github.com/orocommerce/orocommerce/tree/1.0.0/src/Oro/Bundle/CustomerBundle/Controller/Frontend/Api/Rest/CustomerUserController.php "Oro\Bundle\CustomerBundle\Controller\Frontend\Api\Rest\CustomerUserController")
* The method [`CommerceCustomerAddressController::deleteAction`](https://github.com/orocommerce/orocommerce/tree/1.0.0/src/Oro/Bundle/CustomerBundle/Controller/Api/Rest/CommerceCustomerAddressController.php "Oro\Bundle\CustomerBundle\Controller\Api\Rest\CommerceCustomerAddressController") was removed.
* The method [`CustomerUserAddressController::deleteAction`](https://github.com/orocommerce/orocommerce/tree/1.0.0/src/Oro/Bundle/CustomerBundle/Controller/Api/Rest/CustomerUserAddressController.php "Oro\Bundle\CustomerBundle\Controller\Api\Rest\CustomerUserAddressController") was removed.
* The method [`CustomerGroupRepository::getBatchIterator`](https://github.com/orocommerce/orocommerce/tree/1.0.0/src/Oro/Bundle/CustomerBundle/Entity/Repository/CustomerGroupRepository.php "Oro\Bundle\CustomerBundle\Entity\Repository\CustomerGroupRepository") was removed.
* The method [`SystemConfigListener::getSettingsKey`](https://github.com/orocommerce/orocommerce/tree/1.0.0/src/Oro/Bundle/CustomerBundle/EventListener/SystemConfigListener.php "Oro\Bundle\CustomerBundle\EventListener\SystemConfigListener") was removed.
* The method [`CustomerUserRoleUpdateFrontendHandler::setRequestStack`](https://github.com/orocommerce/orocommerce/tree/1.0.0/src/Oro/Bundle/CustomerBundle/Form/Handler/CustomerUserRoleUpdateFrontendHandler.php "Oro\Bundle\CustomerBundle\Form\Handler\CustomerUserRoleUpdateFrontendHandler") was removed.
* The following methods in class [`CustomerUserRoleUpdateHandler`](https://github.com/orocommerce/orocommerce/tree/1.0.0/src/Oro/Bundle/CustomerBundle/Form/Handler/CustomerUserRoleUpdateHandler.php "Oro\Bundle\CustomerBundle\Form\Handler\CustomerUserRoleUpdateHandler") were removed:
   - `applyCustomerLimits`
   - `setRequestStack`
* The following methods in class [`FrontendCustomerUserTypedAddressType`](https://github.com/orocommerce/orocommerce/tree/1.0.0/src/Oro/Bundle/CustomerBundle/Form/Type/FrontendCustomerUserTypedAddressType.php "Oro\Bundle\CustomerBundle\Form\Type\FrontendCustomerUserTypedAddressType") were removed:
   - `buildForm`
   - `preSetData`
* The method [`FrontendOwnerTreeProvider::getCache`](https://github.com/orocommerce/orocommerce/tree/1.0.0/src/Oro/Bundle/CustomerBundle/Owner/FrontendOwnerTreeProvider.php "Oro\Bundle\CustomerBundle\Owner\FrontendOwnerTreeProvider") was removed.
* The method [`CustomerUserAddressController::cgetAction`](https://github.com/orocrm/customer-portal/tree/2.1.0/src/Oro/Bundle/CustomerBundle/Controller/Api/Rest/CustomerUserAddressController.php "Oro\Bundle\CustomerBundle\Controller\Api\Rest\CustomerUserAddressController") has been updated. Pass `Symfony\Component\HttpFoundation\Request` as a second argument of the method. Pass `Symfony\Component\HttpFoundation\Request` as a second argument of the method instead of `mixed`.
* The method [`SecurityController::loginAction`](https://github.com/orocrm/customer-portal/tree/2.1.0/src/Oro/Bundle/CustomerBundle/Controller/SecurityController.php "Oro\Bundle\CustomerBundle\Controller\SecurityController") has been updated. Pass `Symfony\Component\HttpFoundation\Request` as a first argument of the method. Pass `Symfony\Component\HttpFoundation\Request` as a first argument of the method instead of `mixed`.
* The method [`AddressProvider::setListRouteName`](https://github.com/orocrm/customer-portal/tree/2.1.0/src/Oro/Bundle/CustomerBundle/Layout/DataProvider/AddressProvider.php "Oro\Bundle\CustomerBundle\Layout\DataProvider\AddressProvider") has been updated. Pass `mixed` as a second argument of the method.
* The method [`CustomerExtension::__construct`](https://github.com/orocrm/customer-portal/tree/2.1.0/src/Oro/Bundle/CustomerBundle/Twig/CustomerExtension.php "Oro\Bundle\CustomerBundle\Twig\CustomerExtension") has been updated. Pass `Symfony\Component\DependencyInjection\ContainerInterface` as a first argument of the method instead of [`CustomerUserProvider`](https://github.com/orocommerce/orocommerce/tree/1.0.0/src/Oro/Bundle/CustomerBundle/Security/CustomerUserProvider.php "Oro\Bundle\CustomerBundle\Security\CustomerUserProvider").

FrontendBundle
-------------
* Updated service definition for `oro_frontend.extension.transtation_packages_provider`
    - changed class to `Oro\Bundle\FrontendBundle\Provider\TranslationPackagesProviderExtension`
    - changed publicity to `false`
* The following classes were removed:
    - [`BreadcrumbManager`](https://github.com/orocommerce/orocommerce/tree/1.0.0/src/Oro/Bundle/FrontendBundle/Menu/BreadcrumbManager.php "Oro\Bundle\FrontendBundle\Menu\BreadcrumbManager")
    - [`TranslationPackagesProviderExtension`](https://github.com/orocommerce/orocommerce/tree/1.0.0/src/Oro/Bundle/FrontendBundle/Provider/TranslationPackagesProviderExtension.php "Oro\Bundle\FrontendBundle\Provider\TranslationPackagesProviderExtension")
* The method [`FrontendController::exceptionAction`](https://github.com/orocrm/customer-portal/tree/2.1.0/src/Oro/Bundle/FrontendBundle/Controller/FrontendController.php "Oro\Bundle\FrontendBundle\Controller\FrontendController") has been updated. Pass `mixed` as a first argument of the method instead of `Symfony\Component\HttpFoundation\Request`.

WebsiteBundle
-------------
* The method [`OroWebsiteExtension::__construct`](https://github.com/orocrm/customer-portal/tree/2.1.0/src/Oro/Bundle/WebsiteBundle/Twig/OroWebsiteExtension.php "Oro\Bundle\WebsiteBundle\Twig\OroWebsiteExtension") has been updated. Pass `Symfony\Component\DependencyInjection\ContainerInterface` as a first argument of the method instead of [`WebsiteManager`](https://github.com/orocommerce/orocommerce/tree/1.0.0/src/Oro/Bundle/WebsiteBundle/Manager/WebsiteManager.php "Oro\Bundle\WebsiteBundle\Manager\WebsiteManager").
* The method [`WebsitePathExtension::__construct`](https://github.com/orocrm/customer-portal/tree/2.1.0/src/Oro/Bundle/WebsiteBundle/Twig/WebsitePathExtension.php "Oro\Bundle\WebsiteBundle\Twig\WebsitePathExtension") has been updated. Pass `Symfony\Component\DependencyInjection\ContainerInterface` as a first argument of the method instead of [`WebsiteUrlResolver`](https://github.com/orocommerce/orocommerce/tree/1.0.0/src/Oro/Bundle/WebsiteBundle/Resolver/WebsiteUrlResolver.php "Oro\Bundle\WebsiteBundle\Resolver\WebsiteUrlResolver").
