# Frontend Access

* [Close website for non-authenticated visitors](#close-website-for-non-authenticated-visitors)
* [Frontend Datagrids](#frontend-datagrids)

## Close website for non-authenticated visitors

In order to prevent non-registered customers from accessing the storefront,
a possibility to disable website access by such users was added.

To change access, navigate to `Configuration -> Commerce -> Guests -> Website Access` and set the `Enable Guest Access` option.

When access is disabled, all non-authenticated visitors will be redirected to the login page.
`Oro\Bundle\FrontendBundle\EventListener\GuestAccessRequestListener` creates a redirect response; to get a better understanding of how it makes the decision, take a look at the description inside the class. 

Few system URLs are still available, even if access for non-authenticated visitors is restricted.
A list of patterns of those URLs can be found in `Oro\Bundle\FrontendBundle\GuestAccess\Provider\GuestAccessAllowedUrlsProvider`.
To add a pattern to the list of allowed URL patterns you can call `addAllowedUrlPattern` of `oro_frontend.guest_access.provider.guest_access_urls_provider` service or create your own list by decorating the this service with a class implements `Oro\Bundle\FrontendBundle\GuestAccess\Provider\GuestAccessAllowedUrlsProviderInterface`.

```yaml
    acme_frontend.guest_access.provider.guest_access_urls_provider:
        class: Acme\Bundle\MyFrontendBundle\GuestAccess\Provider\MyGuestAccessAllowedUrlsProvider
        decorates: oro_frontend.guest_access.provider.guest_access_urls_provider
```

## Frontend Datagrids

In order to prevent displaying the management console datagrids on the storefront, `frontend` option was added
to datagrid configuration. By default it is suggested that all datagrids are intended to be used on the management
console. To allow a datagrid to be visible on the storefront the `frontend` option should be set to `true`.

```yaml
    acme_frontend.frontend_customers_users:
        options:
            frontend: true
```
