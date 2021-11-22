<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Layout\DataProvider;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Layout\DataProvider\AddressProvider;
use Oro\Component\Testing\Unit\EntityTrait;
use Symfony\Component\HttpKernel\Fragment\FragmentHandler;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AddressProviderTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    /** @var UrlGeneratorInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $router;

    /** @var FragmentHandler|\PHPUnit\Framework\MockObject\MockObject */
    protected $fragmentHandler;

    /** @var ConfigManager|\PHPUnit\Framework\MockObject\MockObject */
    protected $configManager;

    /** @var AddressProvider */
    protected $provider;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->router = $this->createMock(UrlGeneratorInterface::class);
        $this->fragmentHandler = $this->createMock(FragmentHandler::class);
        $this->configManager = $this->createMock(ConfigManager::class);

        $this->provider = new AddressProvider($this->router, $this->fragmentHandler, $this->configManager);
    }

    public function testGetComponentOptions()
    {
        $this->provider->setEntityClass(Customer::class);
        $this->provider->setListRouteName('oro_api_customer_frontend_get_customer_addresses');
        $this->provider->setCreateRouteName('oro_customer_frontend_customer_address_create');
        $this->provider->setUpdateRouteName('oro_customer_frontend_customer_address_update');
        $this->provider->setDeleteRouteName('oro_api_customer_frontend_delete_customer_address');

        /** @var Customer $entity */
        $entity = $this->getEntity(Customer::class, ['id' => 40]);

        $this->router->expects($this->exactly(2))
            ->method('generate')
            ->willReturnMap([
                [
                    'oro_api_customer_frontend_get_customer_addresses',
                    ['entityId' => $entity->getId()],
                    UrlGeneratorInterface::ABSOLUTE_PATH,
                    '/address/list/test/url'
                ],
                [
                    'oro_customer_frontend_customer_address_create',
                    ['entityId' => $entity->getId()],
                    UrlGeneratorInterface::ABSOLUTE_PATH,
                    '/address/create/test/url'
                ]
            ]);

        $this->fragmentHandler->expects($this->once())
            ->method('render')
            ->with('/address/list/test/url')
            ->willReturn(['data']);

        $this->configManager->expects($this->once())
            ->method('get')
            ->with('oro_customer.maps_enabled')
            ->willReturn(true);

        $data = $this->provider->getComponentOptions($entity);

        $this->assertEquals(
            [
                'entityId' => 40,
                'addressListUrl' => '/address/list/test/url',
                'addressCreateUrl' => '/address/create/test/url',
                'addressUpdateRouteName' => 'oro_customer_frontend_customer_address_update',
                'currentAddresses' => ['data'],
                'addressDeleteRouteName' => 'oro_api_customer_frontend_delete_customer_address',
                'showMap' => true,
            ],
            $data
        );
    }

    public function testGetComponentOptionsForDefaultAddresses()
    {
        $this->provider->setEntityClass(Customer::class);
        $this->provider->setListRouteName('oro_api_customer_frontend_get_customer_addresses', true);
        $this->provider->setCreateRouteName('oro_customer_frontend_customer_address_create');
        $this->provider->setUpdateRouteName('oro_customer_frontend_customer_address_update');
        $this->provider->setDeleteRouteName('oro_api_customer_frontend_delete_customer_address');

        /** @var Customer $entity */
        $entity = $this->getEntity(Customer::class, ['id' => 40]);

        $this->router->expects($this->exactly(2))
            ->method('generate')
            ->withConsecutive(
                [
                    'oro_api_customer_frontend_get_customer_addresses',
                    ['entityId' => $entity->getId(), 'default_only' => true],
                ],
                [
                    'oro_customer_frontend_customer_address_create',
                    ['entityId' => $entity->getId()],
                ]
            )
            ->willReturnOnConsecutiveCalls('/address/list/test/url?default_only=true', '/address/create/test/url');

        $this->fragmentHandler->expects($this->once())
            ->method('render')
            ->with('/address/list/test/url?default_only=true')
            ->willReturn(['data']);

        $this->configManager->expects($this->once())
            ->method('get')
            ->with('oro_customer.maps_enabled')
            ->willReturn(true);

        $data = $this->provider->getComponentOptions($entity);

        $this->assertEquals(
            [
                'entityId' => 40,
                'addressListUrl' => '/address/list/test/url?default_only=true',
                'addressCreateUrl' => '/address/create/test/url',
                'addressUpdateRouteName' => 'oro_customer_frontend_customer_address_update',
                'currentAddresses' => ['data'],
                'addressDeleteRouteName' => 'oro_api_customer_frontend_delete_customer_address',
                'showMap' => true,
            ],
            $data
        );
    }

    public function testGetComponentOptionsWithoutRouteName()
    {
        $this->expectException(\UnexpectedValueException::class);
        /** @var Customer $entity */
        $entity = $this->getEntity(Customer::class);

        $this->provider->setListRouteName('');
        $this->provider->getComponentOptions($entity);
    }

    public function testGetComponentOptionsWithWrongEntityClass()
    {
        $this->expectException(\UnexpectedValueException::class);
        /** @var Customer $entity */
        $entity = $this->getEntity(Customer::class);

        $this->provider->setEntityClass(CustomerUser::class);
        $this->provider->getComponentOptions($entity);
    }

    public function testGetComponentOptionsWithMapsDisabled()
    {
        $this->provider->setEntityClass(Customer::class);
        $this->provider->setListRouteName('oro_api_customer_frontend_get_customer_addresses');
        $this->provider->setCreateRouteName('oro_customer_frontend_customer_address_create');
        $this->provider->setUpdateRouteName('oro_customer_frontend_customer_address_update');
        $this->provider->setDeleteRouteName('oro_api_customer_frontend_delete_customer_address');

        /** @var Customer $entity */
        $entity = $this->getEntity(Customer::class, ['id' => 40]);

        $this->router->expects($this->exactly(2))
            ->method('generate')
            ->willReturnMap([
                [
                    'oro_api_customer_frontend_get_customer_addresses',
                    ['entityId' => $entity->getId()],
                    UrlGeneratorInterface::ABSOLUTE_PATH,
                    '/address/list/test/url'
                ],
                [
                    'oro_customer_frontend_customer_address_create',
                    ['entityId' => $entity->getId()],
                    UrlGeneratorInterface::ABSOLUTE_PATH,
                    '/address/create/test/url'
                ]
            ]);

        $this->fragmentHandler->expects($this->once())
            ->method('render')
            ->with('/address/list/test/url')
            ->willReturn(['data']);

        $this->configManager->expects($this->once())
            ->method('get')
            ->with('oro_customer.maps_enabled')
            ->willReturn(false);

        $data = $this->provider->getComponentOptions($entity);

        $this->assertEquals(
            [
                'entityId' => 40,
                'addressListUrl' => '/address/list/test/url',
                'addressCreateUrl' => '/address/create/test/url',
                'addressUpdateRouteName' => 'oro_customer_frontend_customer_address_update',
                'currentAddresses' => ['data'],
                'addressDeleteRouteName' => 'oro_api_customer_frontend_delete_customer_address',
                'showMap' => false,
            ],
            $data
        );
    }
}
