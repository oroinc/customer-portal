<?php

namespace Oro\Bundle\FrontendBundle\CacheWarmer;

use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

/**
 * Change namespace in all loaded migrations, fixtures and entity config data
 * It can't be done in migrations because cache warmup requires existing entities in entity config, see BAP-11101
 */
class UpdateNamespacesWarmer implements CacheWarmerInterface
{
    /**
     * @var ClassMigration
     */
    private $classMigration;

    public function __construct(ClassMigration $classMigration)
    {
        $this->classMigration = $classMigration;
    }

    #[\Override]
    public function warmUp(string $cacheDir, ?string $buildDir = null): array
    {
        $this->classMigration->migrate();
        return [];
    }

    #[\Override]
    public function isOptional(): bool
    {
        return false;
    }
}
