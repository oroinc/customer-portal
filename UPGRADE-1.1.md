UPGRADE FROM 1.0 to 1.1
=======================

####General
- Changed minimum required php version to 7.0
- Updated dependency to [fxpio/composer-asset-plugin](https://github.com/fxpio/composer-asset-plugin) composer plugin to version 1.3.
- Composer updated to version 1.4.

```
    composer self-update
    composer global require "fxp/composer-asset-plugin"
```


Oro Customer Portal Bundles
---------------------------

FrontendBundle
--------------
- Class `Oro\Bundle\FrontendBundle\Provider\TranslationPackagesProviderExtension` removed
- Updated service definition for `oro_frontend.extension.transtation_packages_provider`
    - changed class to `Oro\Bundle\FrontendBundle\Provider\TranslationPackagesProviderExtension`
    - changed publicity to `false`
