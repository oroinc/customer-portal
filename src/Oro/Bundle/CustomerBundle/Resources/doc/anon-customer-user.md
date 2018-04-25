# Anonymous customer user functionality

Anonymous customer user functionality consists of the following sections:
* [The AnonymousCustomerUserToken](#the-anonymouscustomerusertoken)
* [The CustomerVisitor entity](#the-customervisitor-entity)
* [The Listener](#the-listener)
* [The Authentication Provider](#the-authentication-provider)
* [The AnonymousCustomerUserFactory](#the-anonymouscustomeruserfactory)
* [Configuration](#configuration)
* [Guest Customer User](#guest-customer-user)
* [Ownership](#ownership)
* [Configuring features and permissions](#configuring-features-and-permissions)

## The AnonymousCustomerUserToken
The token is [`Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken`](../../Security/Token/AnonymousCustomerUserToken.php) class which 
extends from `AnonymousToken`. It is tied with `CustomerVisitor` entity class which persisted anonymous customer user data for later use. Besides it, token  
stores info taken from cookie: `visitor_id` and `session_id`. If user belongs to Organization it can have `organizationContext` thanks to 
`OrganizationContextTokenTrait`. To provide compatibility with Symfony security system in case of first token initialization it filled with `Anonymous Customer User` string:

```php
$token = new AnonymousCustomerUserToken(
    'Anonymous Customer User',
    [self::ANONYMOUS_CUSTOMER_USER_ROLE]
);
```
 The `authenticate` method of [AnonymousCustomerUserAuthenticationProvider](#the-authentication-provider) sets in the token other data return it:
```php
return new AnonymousCustomerUserToken(
    $token->getUser(),
    $token->getRoles(),
    $visitor,
    $organization
);
```

## The CustomerVisitor entity
The class [Oro\Bundle\CustomerBundle\Entity\CustomerVisitor](../../Entity/CustomerVisitor.php) with properties:
* id
* lastVisit - tracks guest last visit datetime
* sessionId - unique indentifier
* customerUser - one-to-one relation to `CustomerUser` entity. Used to retrieve customer info from token. 
For such cases we using term [Guest Customer User](#guest-customer-user), because it is not "true" user.

Session id property generated through Doctrine `PrePersist` Lifecycle Event:
```php
$this->sessionId = bin2hex(random_bytes(10));
```

## The Listener
The class [Oro\Bundle\CustomerBundle\Security\Firewall\AnonymousCustomerUserAuthenticationListener](../../Security/Firewall/AnonymousCustomerUserAuthenticationListener.php)
 listens requests on the firewall and calls [Oro\Bundle\CustomerBundle\Security\AnonymousCustomerUserAuthenticationProvider](../../Security/AnonymousCustomerUserAuthenticationProvider.php) using `handle` method.

The listener checks the Token and if it is instance of `AnonymousCustomerUserToken`, sets to it visitor Id and session Id taken from `customer_visitor` cookie.
In authentication of `AnonymousCustomerUserToken` object is successful we update cookie using lifetime parameter `oro_customer.customer_visitor_cookie_lifetime_days`. By default this param is 30 days, and it accessible through `System/Configuration/Commerce/Customer/Customer User` section (global/organization level):
```php
const COOKIE_ATTR_NAME = '_security_customer_visitor_cookie';
const COOKIE_NAME = 'customer_visitor';

$cookieLifetime = $this->configManager->get('oro_customer.customer_visitor_cookie_lifetime_days');

$cookieLifetime = $cookieLifetime * Configuration::SECONDS_IN_DAY;

$request->attributes->set(
    self::COOKIE_ATTR_NAME,
    new Cookie(
        self::COOKIE_NAME,
        base64_encode(json_encode([$visitor->getId(), $visitor->getSessionId()])),
        time() + $cookieLifetime
    )
);
```

The [Oro\Bundle\CustomerBundle\Security\Listener\CustomerVisitorCookieResponseListener](../../Security/Listener/CustomerVisitorCookieResponseListener.php) listens `kernel.response` events. If the request have an `_security_customer_visitor_cookie` attribute, it sets cookie with it.

## The Authentication Provider
The class [Oro\Bundle\CustomerBundle\Security\AnonymousCustomerUserAuthenticationProvider](../../Secrity/AnonymousCustomerUserAuthenticationProvider.php) `authenticate` method will do verification of the `AnonymousCustomerUserToken`. Class [Oro\Bundle\CustomerBundle\Entity\CustomerVisitorManager](../../Entity/CustomerVisitorManager.php) finds `CustomerVisitor` entity using key fields `visitor_id` and `session_id` and creates or updates `CustomerVisitor` entity if it was created earlier. As a result - created `AnonymousCustomerUserToken` object which populated with user, roles, organization data and holds `CustomerVisitor` object. 

## The AnonymousCustomerUserFactory

The [Oro\Bundle\CustomerBundle\DependencyInjection\Security\AnonymousCustomerUserFactory](../../DependencyInjection/Security/AnonymousCustomerUserFactory.php) class tie [listener](../../Security/Firewall/AnonymousCustomerUserAuthenticationListener.php) and [provider](../../Secrity/AnonymousCustomerUserAuthenticationProvider.php). 
Also it defines `update_latency` configuration option. It helps to prevent too many requests to the database for update `lastVisit` datetime of `AnonymousCustomerUser` entity. Expressed in seconds, it's default value set in DI container:
``` yml
oro_customer.anonymous_customer_user.update_latency: 600 # 10 minutes in seconds
```

## Firewall configuration
In order to activate anonymous customer user functionality for some routes or apply it to
existing one you should define it in `security` section with property `anonymous_customer_user: true`:
``` yml
security:
    firewalls:
        frontend:
            anonymous_customer_user: true
```
In this example we enable guest functionality for Front Store part of application. 

## Guest Customer User
Guest Customer User is a customer user with such DB properties:
* `confirmed` = `false`
* `enabled` = `false`
* `is_guest` = `true`

The class [Oro\Bundle\CustomerBundle\Entity\GuestCustomerUserManager](../../Entity/GuestCustomerUserManager.php) have a logic of creation `Guest Customer User`.

 It's used for creating some business products under Anonymous Customer like RFQ or Order in the Front Store.
For example when we creating some mentioned product, we can tie it with Guest Customer info taken from `AnonymousCustomerUserToken` token:
```php
// $request is a some Request object 
$token = $this->tokenAccessor->getToken();

if ($token instanceof AnonymousCustomerUserToken) {
    $visitor = $token->getVisitor();
    $user = $visitor->getCustomerUser();
    if ($user === null) {
        $user = $this->guestCustomerUserManager
            ->generateGuestCustomerUser(
                [
                    'email' => $request->getEmail(),
                    'first_name' => $request->getFirstName(),
                    'last_name' => $request->getLastName(),
                    ...
                ]
            );
        $visitor->setCustomerUser($user);
    }
    $request->setCustomerUser($user);
}
```

## Ownership
When we use guest functionality for some business product usually we should specify his owner. Thanks to [Oro\Bundle\CustomerBundle\Entity\CustomerVisitorOwnerAwareInterface](../../Entity/CustomerVisitorOwnerAwareInterface.php)  and [Oro\Bundle\CustomerBundle\Owner\AnonymousOwnershipDecisionMaker](../../Owner/AnonymousOwnershipDecisionMaker.php) it is to easy do this but with following conditions: 
* entity should implement `CustomerVisitorOwnerAwareInterface`
* token should be instance of `AnonymousCustomerUserToken`
* entity should contain `CustomerVisitor` and it should be equals current visitor in session

## Configuring features and permissions
When we implement guest functionality for some product it should be tied with feature and added to system configuration (global, organization and website levels). By default, it should be disabled:
```php
//.../DependencyInjection/Configuration.php
'guest_product_toggle' => ['type' => 'boolean','value' => false],
'guest_product_owner' => ['type' => 'string', 'value' => null]
```

```yml
#...Resources/config/oro/system_configuration.yml
system_configuration:
    groups:
        guest_product_section:
            title: some.title
        guest_product_owner_section:
            title: some.title
    fields:
        guest_product:
            data_type: boolean
            type: oro_config_checkbox
            options:
                label: some.title
                tooltip: some.tooltip
        guest_product_owner:
            ui_only: true
            data_type: string
            type: Oro\Bundle\UserBundle\Form\Type\UserSelectType
            options:
                label: some.title
                tooltip: some.tooltip
                required: true
    tree:
        system_configuration:
            commerce:
                children:
                    sales:
                        children:
                            guest_product_section:
                                children:
                                    - guest_product
                            guest_product_owner_section:
                                children:
                                    - guest_product_owner

```

```yml
#...Resources/config/oro/features.yml
features:
    guest_product_feature:
        label: some.label
        description: some.description
        toggle: guest_product_toggle
```

Next we should activate feature togle voter in DI configuration:  

```yml
oro_bundle.voter.guest_product:
    parent: oro_customer.voter.anonymous_customer_user
    calls:
        - [ setFeatureName, ['guest_product_feature'] ]
    tags:
        - { name: oro_featuretogle.voter }

oro_bundle.voter.guest_customer_user:
    parent: oro_customer.voter.customer_user
    calls:
        - [ setFeatureName, ['guest_product_feature'] ]
    tags:
        - { name: oro_featuretogle.voter }
```
Sometimes it should be necessary to open for guests some business entity or action using ACL configuration.
So, if we want to enable for Anonymous Customer User Role this by default:
```yml
#.../Migrations/Data/ORM/data/frontend_roles.yml
ANONYMOUS:
    permissions:
        entity|Oro\Bundle\SomeBundle\Entity\Some: [VIEW_BASIC, CREATE_BASIC, EDIT_BASIC, DELETE_BASIC]
        action|some_action: [EXECUTE]
```
After installation of application predefined role `Non-Authenticated Visitors` will have enabled mentioned permissions/capabilities.
