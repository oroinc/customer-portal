<?php

namespace Oro\Bundle\FrontendBundle\Tests\Functional\Controller;

use Oro\Bundle\ActionBundle\Configuration\ConfigurationProvider;
use Oro\Bundle\ActionBundle\Model\OperationDefinition;
use Oro\Bundle\ActionBundle\Tests\Functional\DataFixtures\LoadTestEntityData;
use Oro\Bundle\FrontendBundle\Provider\FrontendCurrentApplicationProvider;
use Oro\Bundle\FrontendBundle\Tests\Functional\FrontendActionTestCase;
use Oro\Bundle\TestFrameworkBundle\Entity\TestActivity;
use Oro\Bundle\TestFrameworkBundle\Provider\PhpArrayConfigCacheModifier;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\HttpFoundation\Response;

class OperationControllerTest extends FrontendActionTestCase
{
    private const STOREFRONT_OPERATION = 'oro_frontend_test_operation';

    private ConfigurationProvider $configProvider;
    private PhpArrayConfigCacheModifier $configModifier;
    private TestActivity $entity;
    private User $admin;

    #[\Override]
    protected function setUp(): void
    {
        $this->initClient();

        $this->configProvider = self::getContainer()->get('oro_action.tests.configuration.provider');
        $this->configModifier = new PhpArrayConfigCacheModifier($this->configProvider);

        $this->loadFixtures([LoadTestEntityData::class]);
        $this->entity = $this->getReference(LoadTestEntityData::TEST_ENTITY_1);

        $admin = self::getContainer()
            ->get('doctrine')
            ->getRepository(User::class)
            ->findOneBy(['username' => 'admin']);
        self::assertNotNull($admin);
        $this->admin = $admin;

        $this->addStorefrontOperation();
    }

    #[\Override]
    protected function tearDown(): void
    {
        $this->configModifier->resetCache();
    }

    public function testFormDoesNotExposeOperationWithoutApplications(): void
    {
        $this->client->request(
            'GET',
            $this->getUrl(
                'oro_frontend_action_widget_form',
                [
                    'operationName' => 'user_disable',
                    'entityClass' => User::class,
                    'entityId' => $this->admin->getId(),
                    '_wid' => 'test-uuid',
                ]
            )
        );

        self::assertHtmlResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_NOT_FOUND);
    }

    public function testExecuteDoesNotExposeOperationWithoutApplications(): void
    {
        $this->client->request(
            'POST',
            $this->getUrl(
                'oro_frontend_action_operation_execute',
                [
                    'operationName' => 'user_disable',
                    'entityClass' => User::class,
                    'entityId' => $this->admin->getId(),
                ]
            ),
            [],
            [],
            ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']
        );

        $response = $this->client->getResponse();
        self::assertJsonResponseStatusCodeEquals($response, Response::HTTP_NOT_FOUND);
        self::assertEquals(
            [
                'message' => 'Operation with name "user_disable" not found',
                'success' => false,
            ],
            self::getJsonResponseContent($response, Response::HTTP_NOT_FOUND)
        );
    }

    public function testFormAllowsOperationWithCommerceApplication(): void
    {
        $crawler = $this->assertOperationForm(
            self::STOREFRONT_OPERATION,
            $this->entity->getId(),
            TestActivity::class
        );

        self::assertCount(1, $crawler->selectButton('Submit'));
    }

    public function testExecuteAllowsOperationWithCommerceApplication(): void
    {
        $this->client->request(
            'POST',
            $this->getUrl(
                'oro_frontend_action_operation_execute',
                [
                    'operationName' => self::STOREFRONT_OPERATION,
                    'entityClass' => TestActivity::class,
                    'entityId' => $this->entity->getId(),
                ]
            ),
            $this->getOperationExecuteParams(
                self::STOREFRONT_OPERATION,
                $this->entity->getId(),
                TestActivity::class
            ),
            [],
            ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']
        );

        $response = $this->client->getResponse();
        self::assertJsonResponseStatusCodeEquals($response, Response::HTTP_OK);
        self::assertTrue(self::getJsonResponseContent($response, Response::HTTP_OK)['success']);
    }

    private function addStorefrontOperation(): void
    {
        $config = $this->configProvider->getConfiguration();
        $config['operations'][self::STOREFRONT_OPERATION] = [
            'label' => 'oro.action.test.label',
            'enabled' => true,
            'order' => 10,
            'applications' => [FrontendCurrentApplicationProvider::COMMERCE_APPLICATION],
            'frontend_options' => [],
            'entities' => [TestActivity::class],
            'routes' => [],
            'datagrids' => [],
            OperationDefinition::ACTIONS => [],
        ];
        $this->configModifier->updateCache($config);
    }
}
