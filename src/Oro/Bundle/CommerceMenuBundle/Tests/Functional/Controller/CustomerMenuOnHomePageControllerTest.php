<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Functional\Controller;

use Oro\Bundle\CommerceMenuBundle\Tests\Functional\DataFixtures\MenuUserAgentConditionData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class CustomerMenuOnHomePageControllerTest extends WebTestCase
{
    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $this->initClient([]);

        $this->loadFixtures([MenuUserAgentConditionData::class]);
    }

    /**
     * @dataProvider userAgentDataProvider
     *
     * @param string  $userAgentValue
     * @param boolean $contains
     */
    public function testUserAgentConditions($userAgentValue, $contains)
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
            static::assertStringContainsString('global_menu_update.2_1.title', $crawler->html());
        } else {
            static::assertStringNotContainsString('global_menu_update.2_1.title', $crawler->html());
        }
    }

    /**
     * @return array
     */
    public function userAgentDataProvider()
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
