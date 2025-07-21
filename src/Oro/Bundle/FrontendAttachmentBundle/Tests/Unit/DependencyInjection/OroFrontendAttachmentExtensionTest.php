<?php

namespace Oro\Bundle\FrontendAttachmentBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\FrontendAttachmentBundle\DependencyInjection\OroFrontendAttachmentExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OroFrontendAttachmentExtensionTest extends TestCase
{
    public function testLoad(): void
    {
        $container = new ContainerBuilder();

        $extension = new OroFrontendAttachmentExtension();
        $extension->load([], $container);

        self::assertNotEmpty($container->getDefinitions());
    }
}
