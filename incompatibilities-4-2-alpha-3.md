- [CustomerBundle](#customerbundle)
- [FrontendBundle](#frontendbundle)
- [WebsiteBundle](#websitebundle)

CustomerBundle
--------------
* The `CustomerAssignHelper::__construct(ManagerRegistry $registry)`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/4.2.0-alpha.2/src/Oro/Bundle/CustomerBundle/Handler/CustomerAssignHelper.php#L29 "Oro\Bundle\CustomerBundle\Handler\CustomerAssignHelper")</sup> method was changed to `CustomerAssignHelper::__construct(DoctrineHelper $doctrineHelper)`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/4.2.0-alpha.3/src/Oro/Bundle/CustomerBundle/Handler/CustomerAssignHelper.php#L27 "Oro\Bundle\CustomerBundle\Handler\CustomerAssignHelper")</sup>
* The `AclPermissionController::aclAccessLevelsAction($oid, $permission = null)`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/4.2.0-alpha.2/src/Oro/Bundle/CustomerBundle/Controller/AclPermissionController.php#L26 "Oro\Bundle\CustomerBundle\Controller\AclPermissionController")</sup> method was changed to `AclPermissionController::aclAccessLevelsAction(string $oid, string $permission = null)`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/4.2.0-alpha.3/src/Oro/Bundle/CustomerBundle/Controller/AclPermissionController.php#L64 "Oro\Bundle\CustomerBundle\Controller\AclPermissionController")</sup>
* The `CustomerAssignHelper::$registry`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/4.2.0-alpha.2/src/Oro/Bundle/CustomerBundle/Handler/CustomerAssignHelper.php#L17 "Oro\Bundle\CustomerBundle\Handler\CustomerAssignHelper::$registry")</sup> property was removed.

FrontendBundle
--------------
* The following classes were removed:
   - `RuleEditorTextTypeTest`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/4.2.0-alpha.2/src/Oro/Bundle/FrontendBundle/Form/Type/RuleEditorTextTypeTest.php#L10 "Oro\Bundle\FrontendBundle\Form\Type\RuleEditorTextTypeTest")</sup>
   - `RuleEditorTextareaTypeTest`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/4.2.0-alpha.2/src/Oro/Bundle/FrontendBundle/Form/Type/RuleEditorTextareaTypeTest.php#L10 "Oro\Bundle\FrontendBundle\Form\Type\RuleEditorTextareaTypeTest")</sup>

WebsiteBundle
-------------
* The `RedirectListener::__construct(WebsiteManager $websiteManager, WebsiteUrlResolver $websiteUrlResolver, FrontendHelper $frontendHelper)`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/4.2.0-alpha.2/src/Oro/Bundle/WebsiteBundle/EventListener/RedirectListener.php#L33 "Oro\Bundle\WebsiteBundle\EventListener\RedirectListener")</sup> method was changed to `RedirectListener::__construct(WebsiteManager $websiteManager, WebsiteUrlResolver $websiteUrlResolver, FrontendHelper $frontendHelper, MediaCacheRequestHelper $mediaCacheRequestHelper)`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/4.2.0-alpha.3/src/Oro/Bundle/WebsiteBundle/EventListener/RedirectListener.php#L38 "Oro\Bundle\WebsiteBundle\EventListener\RedirectListener")</sup>

