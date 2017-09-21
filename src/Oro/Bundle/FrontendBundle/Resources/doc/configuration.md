# Configuration

## Table of contents

- [Debug routes](#debug-routes)

## Debug routes

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
php app/console fos:js-routing:dump
```
