CommerceMenuBundle
------------------
* The `ConditionExtension`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/2.1.0/src/Oro/Bundle/CommerceMenuBundle/Menu/Condition/ConditionExtension.php#L11 "Oro\Bundle\CommerceMenuBundle\Menu\Condition\ConditionExtension")</sup> class was removed.

CustomerBundle
--------------
* The following classes were removed:
   - `FrontendCustomerUserRoleOptionsProvider`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/2.1.0/src/Oro/Bundle/CustomerBundle/Layout/DataProvider/FrontendCustomerUserRoleOptionsProvider.php#L12 "Oro\Bundle\CustomerBundle\Layout\DataProvider\FrontendCustomerUserRoleOptionsProvider")</sup>
   - `DiscriminatorMapListener`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/2.1.0/src/Oro/Bundle/CustomerBundle/Audit/DiscriminatorMapListener.php#L8 "Oro\Bundle\CustomerBundle\Audit\DiscriminatorMapListener")</sup>

WebsiteBundle
-------------
* The following methods in class `WebsiteUrlResolver`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/2.2.0/src/Oro/Bundle/WebsiteBundle/Resolver/WebsiteUrlResolver.php#L29 "Oro\Bundle\WebsiteBundle\Resolver\WebsiteUrlResolver")</sup> were changed:
  > - `getWebsiteUrl(Website $website = null)`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/2.1.0/src/Oro/Bundle/WebsiteBundle/Resolver/WebsiteUrlResolver.php#L29 "Oro\Bundle\WebsiteBundle\Resolver\WebsiteUrlResolver")</sup>
  > - `getWebsiteUrl(WebsiteInterface $website = null)`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/2.2.0/src/Oro/Bundle/WebsiteBundle/Resolver/WebsiteUrlResolver.php#L29 "Oro\Bundle\WebsiteBundle\Resolver\WebsiteUrlResolver")</sup>

  > - `getWebsiteSecureUrl(Website $website = null)`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/2.1.0/src/Oro/Bundle/WebsiteBundle/Resolver/WebsiteUrlResolver.php#L38 "Oro\Bundle\WebsiteBundle\Resolver\WebsiteUrlResolver")</sup>
  > - `getWebsiteSecureUrl(WebsiteInterface $website = null)`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/2.2.0/src/Oro/Bundle/WebsiteBundle/Resolver/WebsiteUrlResolver.php#L38 "Oro\Bundle\WebsiteBundle\Resolver\WebsiteUrlResolver")</sup>

  > - `getWebsitePath($route, array $routeParams, Website $website = null)`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/2.1.0/src/Oro/Bundle/WebsiteBundle/Resolver/WebsiteUrlResolver.php#L60 "Oro\Bundle\WebsiteBundle\Resolver\WebsiteUrlResolver")</sup>
  > - `getWebsitePath($route, array $routeParams, WebsiteInterface $website = null)`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/2.2.0/src/Oro/Bundle/WebsiteBundle/Resolver/WebsiteUrlResolver.php#L60 "Oro\Bundle\WebsiteBundle\Resolver\WebsiteUrlResolver")</sup>

  > - `getWebsiteSecurePath($route, array $routeParams, Website $website = null)`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/2.1.0/src/Oro/Bundle/WebsiteBundle/Resolver/WebsiteUrlResolver.php#L73 "Oro\Bundle\WebsiteBundle\Resolver\WebsiteUrlResolver")</sup>
  > - `getWebsiteSecurePath($route, array $routeParams, WebsiteInterface $website = null)`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/2.2.0/src/Oro/Bundle/WebsiteBundle/Resolver/WebsiteUrlResolver.php#L73 "Oro\Bundle\WebsiteBundle\Resolver\WebsiteUrlResolver")</sup>

* The following methods in class `WebsiteUrlResolver`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/2.2.0/src/Oro/Bundle/WebsiteBundle/Resolver/WebsiteUrlResolver.php#L85 "Oro\Bundle\WebsiteBundle\Resolver\WebsiteUrlResolver")</sup> were changed:
  > - `getWebsiteScopeConfigValue($configKey, Website $website = null)`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/2.1.0/src/Oro/Bundle/WebsiteBundle/Resolver/WebsiteUrlResolver.php#L85 "Oro\Bundle\WebsiteBundle\Resolver\WebsiteUrlResolver")</sup>
  > - `getWebsiteScopeConfigValue($configKey, WebsiteInterface $website = null)`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/2.2.0/src/Oro/Bundle/WebsiteBundle/Resolver/WebsiteUrlResolver.php#L85 "Oro\Bundle\WebsiteBundle\Resolver\WebsiteUrlResolver")</sup>

  > - `getDefaultConfigValue($configKey, Website $website = null)`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/2.1.0/src/Oro/Bundle/WebsiteBundle/Resolver/WebsiteUrlResolver.php#L100 "Oro\Bundle\WebsiteBundle\Resolver\WebsiteUrlResolver")</sup>
  > - `getDefaultConfigValue($configKey, WebsiteInterface $website = null)`<sup>[[?]](https://github.com/oroinc/customer-portal/tree/2.2.0/src/Oro/Bundle/WebsiteBundle/Resolver/WebsiteUrlResolver.php#L100 "Oro\Bundle\WebsiteBundle\Resolver\WebsiteUrlResolver")</sup>


