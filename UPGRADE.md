This file includes only the most important items that should be addressed before attempting to upgrade or during the upgrade of a vanilla Oro application.

Please refer to [CHANGELOG.md](CHANGELOG.md) for a list of significant changes in the code that may affect the upgrade of some customizations.

## FROM 2.5.0 to 2.6.0
* Changed minimum required php version to 7.1

## FROM 2.0.0 to 2.1.0
* Changed minimum required `php` version to **7.0**.
* Updated dependency to [fxpio/composer-asset-plugin](https://github.com/fxpio/composer-asset-plugin) composer plugin to version **1.3**.
* Composer updated to version **1.4**.
```
    composer self-update
    composer global require "fxp/composer-asset-plugin"
```
* For upgrade from **1.0** use the command:
```bash
php bin/console oro:platform:update --env=prod --force
```

