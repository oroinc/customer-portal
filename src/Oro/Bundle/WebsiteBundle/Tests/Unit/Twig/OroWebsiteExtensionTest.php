<?php

namespace Oro\Bundle\WebsiteBundle\Tests\Unit\Twig;

use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Oro\Bundle\WebsiteBundle\Twig\OroWebsiteExtension;
use Oro\Component\Testing\Unit\TwigExtensionTestCaseTrait;

class OroWebsiteExtensionTest extends \PHPUnit\Framework\TestCase
{
    use TwigExtensionTestCaseTrait;

    /** @var OroWebsiteExtension */
    protected $extension;

    /** @var WebsiteManager|\PHPUnit\Framework\MockObject\MockObject */
    protected $websiteManager;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $this->websiteManager = $this->createMock(WebsiteManager::class);

        $container = self::getContainerBuilder()
            ->add('oro_website.manager', $this->websiteManager)
            ->getContainer($this);

        $this->extension = new OroWebsiteExtension($container);
    }

    public function testGetName()
    {
        $this->assertEquals(OroWebsiteExtension::NAME, $this->extension->getName());
    }
}
