<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Functional\Controller;

use Oro\Bundle\CommerceMenuBundle\Tests\Functional\DataFixtures\MenuUserAgentConditionData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class CustomerMenuOnHomePageControllerTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->initClient([]);
        $this->loadFixtures([MenuUserAgentConditionData::class]);
    }

    /**
     * @dataProvider userAgentDataProvider
     */
    public function testUserAgentConditions(string $userAgentValue, bool $contains)
    {
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('oro_frontend_root'),
            [],
            [],
            ['HTTP_USER_AGENT' => $userAgentValue]
        );
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        if ($contains) {
            self::assertStringContainsString('global_menu_update.2_1.title', $crawler->html());
        } else {
            self::assertStringNotContainsString('global_menu_update.2_1.title', $crawler->html());
        }
    }

    public function userAgentDataProvider(): array
    {
        return [
            'check visible menu' => [
                'userAgentvalue' => 'Mozilla/5.0 (X11; Linux x86_64) Chrome/57.0.2987.98 Safari/537.36',
                'contains' => true
            ],
            'check unvisible menu' => [
                'userAgentvalue' => 'Mozilla/3.0 (X11; Linux x86_64) Chrome/57.0.2987.98 Safari/530.36',
                'contains' => false
            ],
        ];
    }
}
