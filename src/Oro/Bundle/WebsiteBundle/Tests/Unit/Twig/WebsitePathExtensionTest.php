<?php

namespace Oro\Bundle\WebsiteBundle\Tests\Unit\Twig;

use Oro\Bundle\WebsiteBundle\Resolver\WebsiteUrlResolver;
use Oro\Bundle\WebsiteBundle\Twig\WebsitePathExtension;
use Oro\Component\Testing\Unit\EntityTrait;
use Oro\Component\Testing\Unit\TwigExtensionTestCaseTrait;

class WebsitePathExtensionTest extends \PHPUnit\Framework\TestCase
{
    use TwigExtensionTestCaseTrait;
    use EntityTrait;

    /** @var WebsiteUrlResolver|\PHPUnit\Framework\MockObject\MockObject */
    protected $websiteUrlResolver;

    /** @var WebsitePathExtension */
    protected $extension;

    protected function setUp(): void
    {
        $this->websiteUrlResolver = $this->getMockBuilder(WebsiteUrlResolver::class)
            ->disableOriginalConstructor()
            ->getMock();

        $container = self::getContainerBuilder()
            ->add('oro_website.resolver.website_url_resolver', $this->websiteUrlResolver)
            ->getContainer($this);

        $this->extension = new WebsitePathExtension($container);
    }

    public function testGetName()
    {
        $this->assertEquals(WebsitePathExtension::NAME, $this->extension->getName());
    }
}
