<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\ImportExport\Strategy\EventListener;

use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\ValidatorInterface;

use Doctrine\Common\Persistence\ManagerRegistry;

use Oro\Bundle\ImportExportBundle\Converter\ConfigurableTableDataConverter;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\CustomerBundle\Entity\Repository\CustomerUserRoleRepository;
use Oro\Bundle\CustomerBundle\ImportExport\Strategy\EventListener\ImportCustomerUserListener;
use Oro\Bundle\EntityBundle\Helper\FieldHelper;
use Oro\Bundle\ImportExportBundle\Context\Context;
use Oro\Bundle\ImportExportBundle\Event\StrategyEvent;
use Oro\Bundle\ImportExportBundle\Strategy\Import\ImportStrategyHelper;
use Oro\Bundle\WebsiteBundle\Entity\Repository\WebsiteRepository;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\SecurityBundle\SecurityFacade;

class ImportCustomerUserListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ManagerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registry;

    /**
     * @var CustomerUserManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerUserManager;

    /**
     * @var TranslatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $translation;

    /**
     * @var ImportStrategyHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $strategyHelper;

    protected function setUp()
    {
        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->customerUserManager = $this->createMock(CustomerUserManager::class);
        $this->translation = $this->createMock(TranslatorInterface::class);

        $validatorInterface = $this->createMock(ValidatorInterface::class);
        $fieldHelper = $this->createMock(FieldHelper::class);
        $configurableDataConverter = $this->createMock(ConfigurableTableDataConverter::class);
        $securityFacade = $this->createMock(SecurityFacade::class);

        $this->strategyHelper = new ImportStrategyHelper(
            $this->registry,
            $validatorInterface,
            $this->translation,
            $fieldHelper,
            $configurableDataConverter,
            $securityFacade
        );
    }

    /**
     * @dataProvider dataProvider
     * @param Website|null $website
     * @param Website|null $customerUserRole
     * @param bool $websiteViolation
     * @param bool $roleViolation
     */
    public function testOnProcessAfterEventAddEntity(
        $website,
        $customerUserRole,
        $websiteViolation,
        $roleViolation
    ) {
        $websiteRepository = $this->createMock(WebsiteRepository::class);
        $websiteRepository->method('getDefaultWebsite')
            ->willReturn($website);

        $customerUserRoleRepository = $this->createMock(CustomerUserRoleRepository::class);
        $customerUserRoleRepository->method('getDefaultCustomerUserRoleByWebsite')
            ->willReturn($customerUserRole);

        $this->registry->method('getRepository')
            ->willReturnMap([
                [Website::class, null, $websiteRepository],
                [CustomerUserRole::class, null, $customerUserRoleRepository]
            ]);

        $customerUser = new CustomerUser();

        /** @var StrategyEvent|\PHPUnit_Framework_MockObject_MockObject $event */
        $event = $this->createMock(StrategyEvent::class);

        $event->method('getEntity')
            ->willReturn($customerUser);

        $context = new Context([]);
        $context->setValue('read_offset', 0);

        $event->method('getContext')
            ->willReturn($context);

        $password = 'password';
        $this->customerUserManager->method('generatePassword')
            ->willReturn($password);

        $this->customerUserManager->method('updatePassword')
            ->willReturnCallback(function ($customerUser) use ($password) {
                $customerUser->setPassword($password);
            });

        $this->translation->method('trans')
            ->willReturnMap([
                [
                    'oro.customer.customeruser.import.message.default_website_does_not_exist',
                    [],
                    null,
                    null,
                    'Default website doesn\'t exists'
                ],
                [
                    'oro.customer.customeruser.import.message.default_website_role_does_not_exist',
                    ['%website%' => (string) $website],
                    null,
                    null,
                    'Default role for website WebsiteTest doesn\'t exists',
                ],
                [
                    'oro.importexport.import.error %number%',
                    ['%number%' => 0],
                    null,
                    null,
                    'Error in row #0.'
                ]
            ]);

        $listener = new ImportCustomerUserListener(
            $this->registry,
            $this->customerUserManager,
            $this->translation,
            $this->strategyHelper
        );

        $listener->onProcessAfter($event);

        $this->assertEntityValid($customerUser, $context, $websiteViolation, $roleViolation);
    }

    public function testOnProcessAfterEventUpdateEntity()
    {
        $websiteBefore = $this->createMock(Website::class);
        $websiteBefore->method('__toString')
            ->willReturn('WebsiteBefore');

        $websiteAfter = $this->createMock(Website::class);
        $websiteAfter->method('__toString')
            ->willReturn('WebsiteAfter');

        $websiteRepository = $this->createMock(WebsiteRepository::class);
        $websiteRepository->method('getDefaultWebsite')
            ->willReturn($websiteAfter);


        $roleNameBefore = 'ROLE_FRONTEND_TEST_BEFORE';
        $customerUserRoleBefore = $this->getMockBuilder(CustomerUserRole::class)
            ->setConstructorArgs([$roleNameBefore])
            ->getMock();

        $customerUserRoleBefore->method('getRole')
            ->willReturn($roleNameBefore);

        $roleNameAfter = 'ROLE_FRONTEND_TEST_AFTER';
        $customerUserRoleAfter = $this->getMockBuilder(CustomerUserRole::class)
            ->setConstructorArgs([$roleNameAfter])
            ->getMock();

        $customerUserRoleRepository = $this->createMock(CustomerUserRoleRepository::class);
        $customerUserRoleRepository->method('getDefaultCustomerUserRoleByWebsite')
            ->willReturn($customerUserRoleAfter);

        $this->registry->method('getRepository')
            ->willReturnMap([
                [Website::class, null, $websiteRepository],
                [CustomerUserRole::class, null, $customerUserRoleRepository]
            ]);

        $customerUser = new CustomerUser();
        $customerUser->setWebsite($websiteBefore);
        $customerUser->addRole($customerUserRoleBefore);
        $passwordBefore = 'password_before';
        $passwordAfter = 'password_after';
        $customerUser->setPassword($passwordBefore);

        /** @var StrategyEvent|\PHPUnit_Framework_MockObject_MockObject $event */
        $event = $this->createMock(StrategyEvent::class);

        $event->method('getEntity')
            ->willReturn($customerUser);

        $context = new Context([]);

        $event->method('getContext')
            ->willReturn($context);

        $this->customerUserManager->method('updatePassword')
            ->willReturnCallback(function ($customerUser) use ($passwordAfter) {
                $customerUser->setPassword($passwordAfter);
            });

        $listener = new ImportCustomerUserListener(
            $this->registry,
            $this->customerUserManager,
            $this->translation,
            $this->strategyHelper
        );

        $listener->onProcessAfter($event);

        $this->assertEquals((string) $websiteBefore, (string) $customerUser->getWebsite());
        $this->assertEquals($roleNameBefore, $customerUser->getRole($roleNameBefore)->getRole());
        $this->assertEquals($passwordBefore, $customerUser->getPassword());
    }

    /**
     * @param CustomerUser $customerUser
     * @param Context $context
     * @param $websiteViolation
     * @param $roleViolation
     */
    protected function assertEntityValid(
        CustomerUser $customerUser,
        Context $context,
        $websiteViolation,
        $roleViolation
    ) {
        $this->assertEquals('password', $customerUser->getPassword());
        if (!$websiteViolation && !$roleViolation) {
            $this->assertEquals('WebsiteTest', (string) $customerUser->getWebsite());
            $this->assertEquals(1, count($customerUser->getRoles()));
            $this->assertEquals('ROLE_FRONTEND_TEST', $customerUser->getRole('ROLE_FRONTEND_TEST')->getRole());
            $this->assertEquals(0, $context->getErrorEntriesCount());
        } elseif (!$websiteViolation) {
            $this->assertEquals('WebsiteTest', (string) $customerUser->getWebsite());
            $this->assertEquals(0, count($customerUser->getRoles()));
            $this->assertNull($customerUser->getRole('ROLE_FRONTEND_TEST'));
            $this->assertEquals(1, $context->getErrorEntriesCount());
            $this->assertEquals(
                ['Error in row #0. Default role for website WebsiteTest doesn\'t exists'],
                $context->getErrors()
            );
        } else {
            $this->assertNull($customerUser->getWebsite());
            $this->assertEquals(0, count($customerUser->getRoles()));
            $this->assertNull($customerUser->getRole('ROLE_FRONTEND_TEST'));
            $this->assertEquals(2, $context->getErrorEntriesCount());
            $this->assertEquals(
                [
                    'Error in row #0. Default website doesn\'t exists',
                    'Error in row #0. Default role for website WebsiteTest doesn\'t exists'
                ],
                $context->getErrors()
            );
        }
    }

    /**
     * @return array
     */
    public function dataProvider()
    {
        $website = $this->createMock(Website::class);
        $website->method('__toString')
            ->willReturn('WebsiteTest');

        $roleName = 'ROLE_FRONTEND_TEST';
        $customerUserRole = $this->getMockBuilder(CustomerUserRole::class)
            ->setConstructorArgs([$roleName])
            ->getMock();

        $customerUserRole->method('getRole')
            ->willReturn($roleName);

        return [
            'null all entities' => [null, null, true, true],
            'null website entity' => [null, $customerUserRole, true, true],
            'not null website entity' => [$website, null, false, true],
            'not null all entities' => [$website, $customerUserRole, false, false]
        ];
    }
}
