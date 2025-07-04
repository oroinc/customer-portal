<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Datagrid\Extension;

use Oro\Bundle\CustomerBundle\Datagrid\Extension\GridViewsExtensionComposite;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerVisitor;
use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\Common\MetadataObject;
use Oro\Bundle\DataGridBundle\Datagrid\ParameterBag;
use Oro\Bundle\DataGridBundle\Extension\GridViews\GridViewsExtension;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\UserBundle\Entity\AbstractUser;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class GridViewsExtensionCompositeTest extends \PHPUnit\Framework\TestCase
{
    /** @var GridViewsExtension|\PHPUnit\Framework\MockObject\MockObject */
    private $defaultGridViewsExtension;

    /** @var GridViewsExtension|\PHPUnit\Framework\MockObject\MockObject */
    private $frontendGridViewsExtension;

    /** @var TokenAccessorInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $tokenAccessor;

    /** @var GridViewsExtensionComposite */
    private $extension;

    #[\Override]
    protected function setUp(): void
    {
        $this->defaultGridViewsExtension = $this->createMock(GridViewsExtension::class);
        $this->frontendGridViewsExtension = $this->createMock(GridViewsExtension::class);
        $this->tokenAccessor = $this->createMock(TokenAccessorInterface::class);

        $this->extension = new GridViewsExtensionComposite(
            $this->defaultGridViewsExtension,
            $this->frontendGridViewsExtension,
            $this->tokenAccessor
        );
    }

    /**
     * @dataProvider dataProvider
     */
    public function testIsApplicable(string|AbstractUser $user, ?TokenInterface $token, bool $isFrontend)
    {
        $config = DatagridConfiguration::create([]);

        $anonymousToken = (bool) $token;
        $this->tokenAccessor->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $this->tokenAccessor->expects($this->exactly((int) !$anonymousToken))
            ->method('getUser')
            ->willReturn($user);

        $this->defaultGridViewsExtension->expects($this->exactly((int) !$isFrontend))
            ->method('isApplicable')
            ->with($config)
            ->willReturn(false);
        $this->frontendGridViewsExtension->expects($this->exactly((int) $isFrontend))
            ->method('isApplicable')
            ->with($config)
            ->willReturn(true);

        $this->assertEquals($isFrontend, $this->extension->isApplicable($config));
    }

    /**
     * @dataProvider dataProvider
     */
    public function testGetPriority(string|AbstractUser $user, ?TokenInterface $token, bool $isFrontend)
    {
        $anonymousToken = (bool) $token;
        $this->tokenAccessor->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $this->tokenAccessor->expects($this->exactly((int) !$anonymousToken))
            ->method('getUser')
            ->willReturn($user);
        $this->defaultGridViewsExtension->expects($this->exactly((int) !$isFrontend))
            ->method('getPriority')
            ->willReturn(20);
        $this->frontendGridViewsExtension->expects($this->exactly((int) $isFrontend))
            ->method('getPriority')
            ->willReturn(10);

        $this->assertEquals($isFrontend ? 10 : 20, $this->extension->getPriority());
    }

    /**
     * @dataProvider dataProvider
     */
    public function testVisitMetadata(string|AbstractUser $user, ?TokenInterface $token, bool $isFrontend)
    {
        $config = DatagridConfiguration::create([]);
        $data = MetadataObject::create([]);

        $anonymousToken = (bool) $token;
        $this->tokenAccessor->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $this->tokenAccessor->expects($this->exactly((int) !$anonymousToken))
            ->method('getUser')
            ->willReturn($user);
        $this->defaultGridViewsExtension->expects($this->exactly((int) !$isFrontend))
            ->method('visitMetadata')
            ->willReturnCallback(function (DatagridConfiguration $config, MetadataObject $data) {
                $data->offsetSet('test', 'defaultGridViewsExtension');
            });
        $this->frontendGridViewsExtension->expects($this->exactly((int) $isFrontend))
            ->method('visitMetadata')
            ->willReturnCallback(function (DatagridConfiguration $config, MetadataObject $data) {
                $data->offsetSet('test', 'frontendGridViewsExtension');
            });

        $this->extension->visitMetadata($config, $data);

        if ($isFrontend) {
            $this->assertEquals('frontendGridViewsExtension', $data->offsetGet('test'));
        } else {
            $this->assertEquals('defaultGridViewsExtension', $data->offsetGet('test'));
        }
    }

    /**
     * @dataProvider dataProvider
     */
    public function testSetParameters(string|AbstractUser $user, ?TokenInterface $token, bool $isFrontend)
    {
        $params = new ParameterBag();

        $anonymousToken = (bool) $token;
        $this->tokenAccessor->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $this->tokenAccessor->expects($this->exactly((int) !$anonymousToken))
            ->method('getUser')
            ->willReturn($user);
        $this->defaultGridViewsExtension->expects($this->exactly((int) !$isFrontend))
            ->method('setParameters')
            ->willReturnCallback(function (ParameterBag $parameters) {
                $parameters->set('test', 'defaultGridViewsExtension');
            });
        $this->frontendGridViewsExtension->expects($this->exactly((int) $isFrontend))
            ->method('setParameters')
            ->willReturnCallback(function (ParameterBag $parameters) {
                $parameters->set('test', 'frontendGridViewsExtension');
            });

        $this->extension->setParameters($params);

        if ($isFrontend) {
            $this->assertEquals('frontendGridViewsExtension', $params->get('test'));
        } else {
            $this->assertEquals('defaultGridViewsExtension', $params->get('test'));
        }
    }

    public function dataProvider(): array
    {
        return [
            'anonymous' => [
                'user' => 'anonymous',
                'token' => new AnonymousCustomerUserToken(new CustomerVisitor()),
                'isFrontend' => true
            ],
            'instance of User' => [
                'user' => new User(),
                'token' => null,
                'isFrontend' => false
            ],
            'instance of CustomerUser' => [
                'user' => new CustomerUser(),
                'token' => null,
                'isFrontend' => true
            ]
        ];
    }
}
