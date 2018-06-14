- [CustomerBundle](#customerbundle)
- [FrontendBundle](#frontendbundle)
- [WebsiteBundle](#websitebundle)

CustomerBundle
--------------
* The `FrontendCustomerUserHandler::__construct(FormInterface $form, Request $request, CustomerUserManager $userManager)`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/3.0.0-beta/src/Oro/Bundle/CustomerBundle/Form/Handler/FrontendCustomerUserHandler.php#L27 "Oro\Bundle\CustomerBundle\Form\Handler\FrontendCustomerUserHandler")</sup> method was changed to `FrontendCustomerUserHandler::__construct(EventDispatcherInterface $eventDispatcher, DoctrineHelper $doctrineHelper, RequestStack $requestStack, CustomerUserManager $userManager)`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/3.0.0-rc/src/Oro/Bundle/CustomerBundle/Form/Handler/FrontendCustomerUserHandler.php#L34 "Oro\Bundle\CustomerBundle\Form\Handler\FrontendCustomerUserHandler")</sup>
* The `CustomerVisitor::setCustomerUser(CustomerUser $customerUser)`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/3.0.0-beta/src/Oro/Bundle/CustomerBundle/Entity/CustomerVisitor.php#L121 "Oro\Bundle\CustomerBundle\Entity\CustomerVisitor")</sup> method was changed to `CustomerVisitor::setCustomerUser(CustomerUser $customerUser = null)`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/3.0.0-rc/src/Oro/Bundle/CustomerBundle/Entity/CustomerVisitor.php#L122 "Oro\Bundle\CustomerBundle\Entity\CustomerVisitor")</sup>
* The following properties in class `FrontendCustomerUserHandler`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/3.0.0-beta/src/Oro/Bundle/CustomerBundle/Form/Handler/FrontendCustomerUserHandler.php#L14 "Oro\Bundle\CustomerBundle\Form\Handler\FrontendCustomerUserHandler")</sup> were removed:
   - `$form`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/3.0.0-beta/src/Oro/Bundle/CustomerBundle/Form/Handler/FrontendCustomerUserHandler.php#L14 "Oro\Bundle\CustomerBundle\Form\Handler\FrontendCustomerUserHandler::$form")</sup>
   - `$request`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/3.0.0-beta/src/Oro/Bundle/CustomerBundle/Form/Handler/FrontendCustomerUserHandler.php#L17 "Oro\Bundle\CustomerBundle\Form\Handler\FrontendCustomerUserHandler::$request")</sup>
* The `CustomerGroup::$customers`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/3.0.0-beta/src/Oro/Bundle/CustomerBundle/Entity/CustomerGroup.php#L104 "Oro\Bundle\CustomerBundle\Entity\CustomerGroup::$customers")</sup> property was removed.
* The following methods in class `CustomerGroup`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/3.0.0-beta/src/Oro/Bundle/CustomerBundle/Entity/CustomerGroup.php#L154 "Oro\Bundle\CustomerBundle\Entity\CustomerGroup")</sup> were removed:
   - `addCustomer`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/3.0.0-beta/src/Oro/Bundle/CustomerBundle/Entity/CustomerGroup.php#L154 "Oro\Bundle\CustomerBundle\Entity\CustomerGroup::addCustomer")</sup>
   - `removeCustomer`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/3.0.0-beta/src/Oro/Bundle/CustomerBundle/Entity/CustomerGroup.php#L169 "Oro\Bundle\CustomerBundle\Entity\CustomerGroup::removeCustomer")</sup>
   - `getCustomers`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/3.0.0-beta/src/Oro/Bundle/CustomerBundle/Entity/CustomerGroup.php#L181 "Oro\Bundle\CustomerBundle\Entity\CustomerGroup::getCustomers")</sup>

FrontendBundle
--------------
* The following classes were removed:
   - `WebType`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/3.0.0-beta/src/Oro/Bundle/FrontendBundle/Form/Type/Configuration/WebType.php#L9 "Oro\Bundle\FrontendBundle\Form\Type\Configuration\WebType")</sup>
   - `ConfigurationTypeExtension`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/3.0.0-beta/src/Oro/Bundle/FrontendBundle/Form/Extension/ConfigurationTypeExtension.php#L10 "Oro\Bundle\FrontendBundle\Form\Extension\ConfigurationTypeExtension")</sup>
* The `RuleEditorTextTypeTest::testGetName`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/3.0.0-beta/src/Oro/Bundle/FrontendBundle/Form/Type/RuleEditorTextTypeTest.php#L30 "Oro\Bundle\FrontendBundle\Form\Type\RuleEditorTextTypeTest::testGetName")</sup> method was removed.
* The `RuleEditorTextareaTypeTest::testGetName`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/3.0.0-beta/src/Oro/Bundle/FrontendBundle/Form/Type/RuleEditorTextareaTypeTest.php#L30 "Oro\Bundle\FrontendBundle\Form\Type\RuleEditorTextareaTypeTest::testGetName")</sup> method was removed.

WebsiteBundle
-------------
* The `WebsiteProviderInterface::getWebsiteChoices`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/3.0.0-rc/src/Oro/Bundle/WebsiteBundle/Provider/WebsiteProviderInterface.php#L22 "Oro\Bundle\WebsiteBundle\Provider\WebsiteProviderInterface::getWebsiteChoices")</sup> interface method was added.

