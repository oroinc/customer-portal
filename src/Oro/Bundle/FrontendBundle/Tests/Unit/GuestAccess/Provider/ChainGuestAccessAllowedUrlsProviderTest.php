<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\GuestAccess\Provider;

use Oro\Bundle\FrontendBundle\GuestAccess\Provider\ChainGuestAccessAllowedUrlsProvider;
use Oro\Bundle\FrontendBundle\GuestAccess\Provider\GuestAccessAllowedUrlsProviderInterface;

class ChainGuestAccessAllowedUrlsProviderTest extends \PHPUnit\Framework\TestCase
{
    public function testGetAllowedUrlsPatternsWithoutRegisteredProviders(): void
    {
        $chainProvider = new ChainGuestAccessAllowedUrlsProvider([]);

        $this->assertEmpty($chainProvider->getAllowedUrlsPatterns());
    }

    public function testGetAllowedUrlsPatterns(): void
    {
        $providerAPatterns = ['^/pattern1$', '^/pattern2$', '^/pattern3$'];
        $providerA = $this->getProvider($providerAPatterns);

        $providerBPatterns = ['^/pattern4$', '^/pattern5$'];
        $providerB = $this->getProvider($providerBPatterns);

        $chainProvider = new ChainGuestAccessAllowedUrlsProvider([$providerA, $providerB]);

        $this->assertEquals(
            array_merge($providerAPatterns, $providerBPatterns),
            $chainProvider->getAllowedUrlsPatterns()
        );
    }

    private function getProvider(array $patterns): GuestAccessAllowedUrlsProviderInterface
    {
        $provider = $this->createMock(GuestAccessAllowedUrlsProviderInterface::class);
        $provider->expects($this->once())
            ->method('getAllowedUrlsPatterns')
            ->willReturn($patterns);

        return $provider;
    }
}
