<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Provider;

use Oro\Bundle\FrontendBundle\Provider\EmailTemplateSystemVariablesProvider;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Oro\Bundle\WebsiteBundle\Resolver\WebsiteUrlResolver;
use Oro\Bundle\WebsiteBundle\Tests\Unit\Stub\WebsiteStub;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class EmailTemplateSystemVariablesProviderTest extends TestCase
{
    private WebsiteManager|MockObject $websiteManager;

    private WebsiteUrlResolver|MockObject $websiteUrlResolver;

    private EmailTemplateSystemVariablesProvider $provider;

    #[\Override]
    protected function setUp(): void
    {
        $this->websiteManager = $this->createMock(WebsiteManager::class);
        $this->websiteUrlResolver = $this->createMock(WebsiteUrlResolver::class);
        $translator = $this->createMock(TranslatorInterface::class);
        $translator
            ->method('trans')
            ->willReturnCallback(static fn (string $key) => $key . '.translated');

        $this->provider = new EmailTemplateSystemVariablesProvider(
            $this->websiteManager,
            $this->websiteUrlResolver,
            $translator
        );
    }

    public function testGetVariableDefinitions(): void
    {
        self::assertEquals(
            [
                'websiteURL' => [
                    'type' => 'string',
                    'label' => 'oro_frontend.emailtemplate.website_url.translated',
                ],
                'websiteName' => [
                    'type' => 'string',
                    'label' => 'oro_frontend.emailtemplate.website_name.translated',
                ],
                'organizationName' => [
                    'type' => 'string',
                    'label' => 'oro.email.emailtemplate.organization_name.translated',
                ],
            ],
            $this->provider->getVariableDefinitions()
        );
    }

    public function testGetVariableValuesWhenNoWebsite(): void
    {
        $this->websiteManager
            ->expects(self::once())
            ->method('getCurrentWebsite')
            ->willReturn(null);

        $websiteUrl = 'https://example.com/';
        $this->websiteUrlResolver
            ->expects(self::once())
            ->method('getWebsiteSecurePath')
            ->with('oro_frontend_root', [], null)
            ->willReturn($websiteUrl);

        self::assertEquals(
            [
                'websiteURL' => $websiteUrl,
                'websiteName' => '',
                'organizationName' => '',
            ],
            $this->provider->getVariableValues()
        );
    }

    public function testGetVariableValuesWhenNoOrganization(): void
    {
        $website = (new WebsiteStub(42))
            ->setName('Sample website');
        $this->websiteManager
            ->expects(self::once())
            ->method('getCurrentWebsite')
            ->willReturn($website);

        $websiteUrl = 'https://example.com/';
        $this->websiteUrlResolver
            ->expects(self::once())
            ->method('getWebsiteSecurePath')
            ->with('oro_frontend_root', [], $website)
            ->willReturn($websiteUrl);

        self::assertEquals(
            [
                'websiteURL' => $websiteUrl,
                'websiteName' => $website->getName(),
                'organizationName' => '',
            ],
            $this->provider->getVariableValues()
        );
    }

    public function testGetVariableValues(): void
    {
        $organization = (new Organization())
            ->setName('Sample organization');
        $website = (new WebsiteStub(42))
            ->setName('Sample website')
            ->setOrganization($organization);
        $this->websiteManager
            ->expects(self::once())
            ->method('getCurrentWebsite')
            ->willReturn($website);

        $websiteUrl = 'https://example.com/';
        $this->websiteUrlResolver
            ->expects(self::once())
            ->method('getWebsiteSecurePath')
            ->with('oro_frontend_root', [], $website)
            ->willReturn($websiteUrl);

        self::assertEquals(
            [
                'websiteURL' => $websiteUrl,
                'websiteName' => $website->getName(),
                'organizationName' => $organization->getName(),
            ],
            $this->provider->getVariableValues()
        );
    }
}
