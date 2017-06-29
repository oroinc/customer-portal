#Oro Frontend Access

* [Close website for non-authenticated visitors](#close_website_for_non-authenticated_visitors)

## Close website for non-authenticated visitors

In order to prevent non-registered customers from accessing the store frontend,
a possibility to disable website access by such users was added.

To change access, navigate to `Configuration -> Commerce -> Guests -> Website Access` and set the `Enable Guest Access` option.

When access is disabled, all non-authenticated visitors will be redirected to the login page.
`Oro\Bundle\FrontendBundle\EventListener\GuestAccessRequestListener` creates a redirect response; to get a better understanding of how it makes the decision, take a look at the description inside the class. 

Few system URLs are still available, even if access for non-authenticated visitors is restricted.
A list of patterns of those URLs can be found in `Oro\Bundle\FrontendBundle\GuestAccess\Provider\GuestAccessAllowedUrlsProvider`.
To create your own list of allowed URL patterns, you can decorate the `oro_frontend.guest_access.provider.guest_access_urls_provider` service and implement `Oro\Bundle\FrontendBundle\GuestAccess\Provider\GuestAccessAllowedUrlsProviderInterface`.

```yaml
    acme_frontend.guest_access.provider.guest_access_urls_provider:
        class: Acme\Bundle\MyFrontendBundle\GuestAccess\Provider\MyGuestAccessAllowedUrlsProvider
        decorates: oro_frontend.guest_access.provider.guest_access_urls_provider
```
