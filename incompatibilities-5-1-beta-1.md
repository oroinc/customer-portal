- [CommerceMenuBundle](#commercemenubundle)
- [CustomerBundle](#customerbundle)
- [FrontendBundle](#frontendbundle)
- [FrontendImportExportBundle](#frontendimportexportbundle)
- [WebsiteBundle](#websitebundle)

CommerceMenuBundle
------------------
* The `CustomerGroupMenuController::getMenu($menuName, $context)`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/5.0.0-alpha.2/src/Oro/Bundle/CommerceMenuBundle/Controller/CustomerGroupMenuController.php#L144 "Oro\Bundle\CommerceMenuBundle\Controller\CustomerGroupMenuController")</sup> method was changed to `CustomerGroupMenuController::getMenu($menuName, $context)`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/5.1.0-beta.1/src/Oro/Bundle/CommerceMenuBundle/Controller/CustomerGroupMenuController.php#L145 "Oro\Bundle\CommerceMenuBundle\Controller\CustomerGroupMenuController")</sup>
* The `CustomerMenuController::getMenu($menuName, $context)`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/5.0.0-alpha.2/src/Oro/Bundle/CommerceMenuBundle/Controller/CustomerMenuController.php#L138 "Oro\Bundle\CommerceMenuBundle\Controller\CustomerMenuController")</sup> method was changed to `CustomerMenuController::getMenu($menuName, $context)`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/5.1.0-beta.1/src/Oro/Bundle/CommerceMenuBundle/Controller/CustomerMenuController.php#L139 "Oro\Bundle\CommerceMenuBundle\Controller\CustomerMenuController")</sup>

CustomerBundle
--------------
* The following methods in class `CustomerGroupHandler`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/5.1.0-beta.1/src/Oro/Bundle/CustomerBundle/Form/Handler/CustomerGroupHandler.php#L27 "Oro\Bundle\CustomerBundle\Form\Handler\CustomerGroupHandler")</sup> were changed:
  > - `__construct(FormInterface $form, Request $request, ObjectManager $manager, EventDispatcherInterface $dispatcher)`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/5.0.0-alpha.2/src/Oro/Bundle/CustomerBundle/Form/Handler/CustomerGroupHandler.php#L35 "Oro\Bundle\CustomerBundle\Form\Handler\CustomerGroupHandler")</sup>
  > - `__construct(ObjectManager $manager, EventDispatcherInterface $dispatcher)`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/5.1.0-beta.1/src/Oro/Bundle/CustomerBundle/Form/Handler/CustomerGroupHandler.php#L27 "Oro\Bundle\CustomerBundle\Form\Handler\CustomerGroupHandler")</sup>

  > - `process(CustomerGroup $entity)`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/5.0.0-alpha.2/src/Oro/Bundle/CustomerBundle/Form/Handler/CustomerGroupHandler.php#L53 "Oro\Bundle\CustomerBundle\Form\Handler\CustomerGroupHandler")</sup>
  > - `process($entity, FormInterface $form, Request $request)`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/5.1.0-beta.1/src/Oro/Bundle/CustomerBundle/Form/Handler/CustomerGroupHandler.php#L36 "Oro\Bundle\CustomerBundle\Form\Handler\CustomerGroupHandler")</sup>

  > - `onSuccess(CustomerGroup $entity, $append, $remove)`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/5.0.0-alpha.2/src/Oro/Bundle/CustomerBundle/Form/Handler/CustomerGroupHandler.php#L81 "Oro\Bundle\CustomerBundle\Form\Handler\CustomerGroupHandler")</sup>
  > - `onSuccess(CustomerGroup $entity, FormInterface $form, $append, $remove)`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/5.1.0-beta.1/src/Oro/Bundle/CustomerBundle/Form/Handler/CustomerGroupHandler.php#L65 "Oro\Bundle\CustomerBundle\Form\Handler\CustomerGroupHandler")</sup>

* The following methods in class `CustomerUserHandler`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/5.1.0-beta.1/src/Oro/Bundle/CustomerBundle/Form/Handler/CustomerUserHandler.php#L27 "Oro\Bundle\CustomerBundle\Form\Handler\CustomerUserHandler")</sup> were changed:
  > - `__construct(FormInterface $form, Request $request, CustomerUserManager $userManager, TokenAccessorInterface $tokenAccessor, TranslatorInterface $translator, LoggerInterface $logger)`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/5.0.0-alpha.2/src/Oro/Bundle/CustomerBundle/Form/Handler/CustomerUserHandler.php#L40 "Oro\Bundle\CustomerBundle\Form\Handler\CustomerUserHandler")</sup>
  > - `__construct(CustomerUserManager $userManager, TokenAccessorInterface $tokenAccessor, TranslatorInterface $translator, LoggerInterface $logger)`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/5.1.0-beta.1/src/Oro/Bundle/CustomerBundle/Form/Handler/CustomerUserHandler.php#L27 "Oro\Bundle\CustomerBundle\Form\Handler\CustomerUserHandler")</sup>

  > - `process(CustomerUser $customerUser)`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/5.0.0-alpha.2/src/Oro/Bundle/CustomerBundle/Form/Handler/CustomerUserHandler.php#L63 "Oro\Bundle\CustomerBundle\Form\Handler\CustomerUserHandler")</sup>
  > - `process($customerUser, FormInterface $form, Request $request)`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/5.1.0-beta.1/src/Oro/Bundle/CustomerBundle/Form/Handler/CustomerUserHandler.php#L43 "Oro\Bundle\CustomerBundle\Form\Handler\CustomerUserHandler")</sup>

* The `CustomerUserController::getRolesAction(Request $request, $customerUserId, $customerId)`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/5.0.0-alpha.2/src/Oro/Bundle/CustomerBundle/Controller/CustomerUserController.php#L103 "Oro\Bundle\CustomerBundle\Controller\CustomerUserController")</sup> method was changed to `CustomerUserController::getRolesAction(Request $request, $customerUserId, $customerId)`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/5.1.0-beta.1/src/Oro/Bundle/CustomerBundle/Controller/CustomerUserController.php#L90 "Oro\Bundle\CustomerBundle\Controller\CustomerUserController")</sup>
* The following properties in class `CustomerGroupHandler`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/5.0.0-alpha.2/src/Oro/Bundle/CustomerBundle/Form/Handler/CustomerGroupHandler.php#L24 "Oro\Bundle\CustomerBundle\Form\Handler\CustomerGroupHandler")</sup> were removed:
   - `$form`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/5.0.0-alpha.2/src/Oro/Bundle/CustomerBundle/Form/Handler/CustomerGroupHandler.php#L24 "Oro\Bundle\CustomerBundle\Form\Handler\CustomerGroupHandler::$form")</sup>
   - `$request`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/5.0.0-alpha.2/src/Oro/Bundle/CustomerBundle/Form/Handler/CustomerGroupHandler.php#L27 "Oro\Bundle\CustomerBundle\Form\Handler\CustomerGroupHandler::$request")</sup>
* The following properties in class `CustomerUserHandler`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/5.0.0-alpha.2/src/Oro/Bundle/CustomerBundle/Form/Handler/CustomerUserHandler.php#L23 "Oro\Bundle\CustomerBundle\Form\Handler\CustomerUserHandler")</sup> were removed:
   - `$form`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/5.0.0-alpha.2/src/Oro/Bundle/CustomerBundle/Form/Handler/CustomerUserHandler.php#L23 "Oro\Bundle\CustomerBundle\Form\Handler\CustomerUserHandler::$form")</sup>
   - `$request`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/5.0.0-alpha.2/src/Oro/Bundle/CustomerBundle/Form/Handler/CustomerUserHandler.php#L26 "Oro\Bundle\CustomerBundle\Form\Handler\CustomerUserHandler::$request")</sup>
* The `CustomerUser::getClass`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/5.0.0-alpha.2/src/Oro/Bundle/CustomerBundle/Entity/CustomerUser.php#L1169 "Oro\Bundle\CustomerBundle\Entity\CustomerUser::getClass")</sup> method was removed.

FrontendBundle
--------------
* The `DefaultFrontendPreferredLocalizationProvider::__construct($userLocalizationManager, $frontendHelper)`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/5.0.0-alpha.2/src/Oro/Bundle/FrontendBundle/Provider/DefaultFrontendPreferredLocalizationProvider.php#L30 "Oro\Bundle\FrontendBundle\Provider\DefaultFrontendPreferredLocalizationProvider")</sup> method was changed to `DefaultFrontendPreferredLocalizationProvider::__construct($localizationProvider, $frontendHelper)`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/5.1.0-beta.1/src/Oro/Bundle/FrontendBundle/Provider/DefaultFrontendPreferredLocalizationProvider.php#L20 "Oro\Bundle\FrontendBundle\Provider\DefaultFrontendPreferredLocalizationProvider")</sup>
* The `LocaleSettings::__construct(LocaleSettings $inner, FrontendHelper $frontendHelper, UserLocalizationManagerInterface $localizationManager, LayoutContextHolder $layoutContextHolder, ThemeManager $themeManager)`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/5.0.0-alpha.2/src/Oro/Bundle/FrontendBundle/Model/LocaleSettings.php#L41 "Oro\Bundle\FrontendBundle\Model\LocaleSettings")</sup> method was changed to `LocaleSettings::__construct(LocaleSettings $inner, FrontendHelper $frontendHelper, LocalizationProviderInterface $localizationProvider, LayoutContextHolder $layoutContextHolder, ThemeManager $themeManager)`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/5.1.0-beta.1/src/Oro/Bundle/FrontendBundle/Model/LocaleSettings.php#L26 "Oro\Bundle\FrontendBundle\Model\LocaleSettings")</sup>
* The `LocaleSettings::$localizationManager`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/5.0.0-alpha.2/src/Oro/Bundle/FrontendBundle/Model/LocaleSettings.php#L29 "Oro\Bundle\FrontendBundle\Model\LocaleSettings::$localizationManager")</sup> property was removed.

FrontendImportExportBundle
--------------------------
* The `Topics`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/5.0.0-alpha.2/src/Oro/Bundle/FrontendImportExportBundle/Async/Topics.php#L8 "Oro\Bundle\FrontendImportExportBundle\Async\Topics")</sup> class was removed.

WebsiteBundle
-------------
* The `FrontendFallbackStrategy`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/5.0.0-alpha.2/src/Oro/Bundle/WebsiteBundle/Translation/Strategy/FrontendFallbackStrategy.php#L8 "Oro\Bundle\WebsiteBundle\Translation\Strategy\FrontendFallbackStrategy")</sup> class was removed.
* The following methods in class `WebsiteScopedDataType`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/5.0.0-alpha.2/src/Oro/Bundle/WebsiteBundle/Form/Type/WebsiteScopedDataType.php#L50 "Oro\Bundle\WebsiteBundle\Form\Type\WebsiteScopedDataType")</sup> were removed:
   - `getName`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/5.0.0-alpha.2/src/Oro/Bundle/WebsiteBundle/Form/Type/WebsiteScopedDataType.php#L50 "Oro\Bundle\WebsiteBundle\Form\Type\WebsiteScopedDataType::getName")</sup>
   - `setWebsiteClass`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/5.0.0-alpha.2/src/Oro/Bundle/WebsiteBundle/Form/Type/WebsiteScopedDataType.php#L205 "Oro\Bundle\WebsiteBundle\Form\Type\WebsiteScopedDataType::setWebsiteClass")</sup>
* The following properties in class `WebsiteScopedDataType`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/5.0.0-alpha.2/src/Oro/Bundle/WebsiteBundle/Form/Type/WebsiteScopedDataType.php#L30 "Oro\Bundle\WebsiteBundle\Form\Type\WebsiteScopedDataType")</sup> were removed:
   - `$websites`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/5.0.0-alpha.2/src/Oro/Bundle/WebsiteBundle/Form/Type/WebsiteScopedDataType.php#L30 "Oro\Bundle\WebsiteBundle\Form\Type\WebsiteScopedDataType::$websites")</sup>
   - `$websiteCLass`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/5.0.0-alpha.2/src/Oro/Bundle/WebsiteBundle/Form/Type/WebsiteScopedDataType.php#L35 "Oro\Bundle\WebsiteBundle\Form\Type\WebsiteScopedDataType::$websiteCLass")</sup>
   - `$registry`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/5.0.0-alpha.2/src/Oro/Bundle/WebsiteBundle/Form/Type/WebsiteScopedDataType.php#L40 "Oro\Bundle\WebsiteBundle\Form\Type\WebsiteScopedDataType::$registry")</sup>
