<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Datagrid;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\CustomerBundle\Datagrid\CurrentCustomerUserViewList;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\DataGridBundle\Extension\GridViews\View;
use Oro\Bundle\FilterBundle\Form\Type\Filter\TextFilterType;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SecurityBundle\Authentication\Token\ImpersonationToken;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\UserBundle\Entity\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

final class CurrentCustomerUserViewListTest extends TestCase
{
    private TranslatorInterface&MockObject $translator;
    private TokenAccessorInterface&MockObject $tokenAccessor;
    private CurrentCustomerUserViewList $viewList;

    #[\Override]
    protected function setUp(): void
    {
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->tokenAccessor = $this->createMock(TokenAccessorInterface::class);

        $this->viewList = new CurrentCustomerUserViewList($this->translator, $this->tokenAccessor);
    }

    public function testGetListWhenNoToken(): void
    {
        $this->translator->expects(self::never())
            ->method('trans');

        $this->tokenAccessor->expects(self::once())
            ->method('getToken')
            ->willReturn(null);

        self::assertEquals(new ArrayCollection([]), $this->viewList->getList());
    }

    public function testGetListWhenNoCustomerUser(): void
    {
        $this->translator->expects(self::never())
            ->method('trans');

        $token = new ImpersonationToken(new User(), new Organization());
        $this->tokenAccessor->expects(self::once())
            ->method('getToken')
            ->willReturn($token);

        self::assertEquals(new ArrayCollection([]), $this->viewList->getList());
    }

    public function testGetList(): void
    {
        $labelKey = 'oro.customer.customeruser.entity_label';
        $this->translator->expects(self::once())
            ->method('trans')
            ->with($labelKey)
            ->willReturn('Customer User');

        $user = new CustomerUser();
        $user->setFirstName('Amanda');
        $user->setLastName('Cole');
        $token = new ImpersonationToken($user, new Organization());

        $this->tokenAccessor->expects(self::once())
            ->method('getToken')
            ->willReturn($token);

        $view = new View(
            'oro_customer.customerUserName',
            [
                'customerUserName' => [
                    'type'  => TextFilterType::TYPE_CONTAINS,
                    'value' => \trim(\sprintf('%s %s', $user->getFirstName(), $user->getLastName())),
                ]
            ]
        );
        $view->setLabel('Customer User');
        $view->setDefault(true);

        self::assertEquals(new ArrayCollection([$view]), $this->viewList->getList());
    }
}
