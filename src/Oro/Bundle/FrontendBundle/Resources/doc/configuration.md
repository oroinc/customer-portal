# Configuration

## Table of contents

- [Frontend Session](#frontend-session)
- [Debug Routes](#debug-routes)

## Frontend Session

This bundle provides a possibility to configure different session cookie parameters, such as name, path and lifetime,
for storefront and management console. Use `session` section for this:

```yml
oro_frontend:
    session:
        name: OROSFID
        cookie_lifetime: 900
```

The full list of storefront session options is

* `name`
* `cookie_lifetime`
* `cookie_path`
* `gc_maxlifetime`
* `gc_probability`
* `gc_divisor`

See [Symfony Framework Configuration Reference](https://symfony.com/doc/current/reference/configuration/framework.html#session)
for detailed information about each option.

## Debug Routes

Debug routes allows to turn off on fly routes generation, it can
slightly boost performance on slow hardware configurations and also makes app more
stable on Windows. If `kernel.debug` is set to `false` value of debug routes
is ignored. To turn off routes generation set option `debug_routes`
to `false` in config.yml file:

```yml
oro_frontend:
    debug_routes: false
```

If you turned off routes generation you must do it manually by executing following command:

```bash
php bin/console fos:js-routing:dump
```
