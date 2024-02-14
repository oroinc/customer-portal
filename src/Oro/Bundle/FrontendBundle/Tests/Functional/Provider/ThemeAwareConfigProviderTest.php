<?php

namespace Oro\Bundle\FrontendBundle\Tests\Functional\Provider;

use Oro\Bundle\ConfigBundle\Tests\Functional\Traits\ConfigManagerAwareTestTrait;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @dbIsolationPerTest
 */
class ThemeAwareConfigProviderTest extends WebTestCase
{
    use ConfigManagerAwareTestTrait;

    public function testBackendGridValue(): void
    {
        $this->initClient([], self::generateBasicAuthHeader());
        $response = $this->client->requestGrid(['gridName' => 'test-theme-aware-grid'], [], true);
        $this->assertSame($response->getStatusCode(), 200);
        $decoded = \json_decode($response->getContent(), true);
        $this->assertIsArray($decoded);
        $this->assertEquals('be-grid-update-label', $decoded['metadata']['rowActions']['update']['label']);
    }

    public function testFrontendDefaultThemeGridValue(): void
    {
        $this->initClient();
        $response = $this->client->requestFrontendGrid(
            ['gridName' => 'test-theme-aware-grid'],
            [],
            true,
        );
        $this->assertSame($response->getStatusCode(), 200);
        $decoded = \json_decode($response->getContent(), true);
        $this->assertIsArray($decoded);
        $this->assertEquals(
            'fe-default-theme-grid-update-label',
            $decoded['metadata']['rowActions']['update']['label']
        );
    }
}
