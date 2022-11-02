<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Form\Type\FrontendOwnerSelectType;
use Oro\Bundle\EntityConfigBundle\Config\Config;
use Oro\Bundle\EntityConfigBundle\Config\Id\EntityConfigId;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\TranslationBundle\Form\Type\Select2TranslatableEntityType;
use Oro\Component\Testing\Unit\EntityTrait;
use Oro\Component\Testing\Unit\FormIntegrationTestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FrontendOwnerSelectTypeTest extends FormIntegrationTestCase
{
    use EntityTrait;

    /** @var ManagerRegistry|\PHPUnit\Framework\MockObject\MockObject */
    private $registry;

    /** @var ConfigProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $configProvider;

    /** @var FrontendOwnerSelectType */
    private $formType;

    protected function setUp(): void
    {
        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->configProvider = $this->createMock(ConfigProvider::class);

        $this->formType = new FrontendOwnerSelectType($this->registry, $this->configProvider);
    }

    public function testGetParent()
    {
        self::assertEquals(Select2TranslatableEntityType::class, $this->formType->getParent());
    }

    public function testConfigureOptions()
    {
        $dataObject = new CustomerUser();
        $config = new Config(
            new EntityConfigId('ownership'),
            [
                'frontend_owner_type' => 'FRONTEND_CUSTOMER',
                'frontend_owner_field_name' => 'customer'
            ]
        );

        $resolver = new OptionsResolver();
        $resolver->setDefined(['acl_options']);

        $this->configProvider->expects(self::exactly(2))
            ->method('getConfig')
            ->with(CustomerUser::class)
            ->willReturn($config);

        $queryBuilder = $this->createMock(QueryBuilder::class);
        $customerUserRepository = $this->createMock(EntityRepository::class);
        $customerUserRepository->expects(self::once())
            ->method('createQueryBuilder')
            ->with('owner')
            ->willReturn($queryBuilder);

        $this->registry->expects(self::once())
            ->method('getRepository')
            ->with(Customer::class)
            ->willReturn($customerUserRepository);

        $em = $this->createMock(EntityManager::class);
        $this->registry->expects(self::once())
            ->method('getManagerForClass')
            ->willReturn($em);
        $em->expects(self::once())
            ->method('contains')
            ->with($dataObject)
            ->willReturn(false);

        $this->formType->configureOptions($resolver);
        $resolved = $resolver->resolve(['targetObject' => $dataObject, 'acl_options' => null, 'query_builder' => null]);

        self::assertEquals($dataObject, $resolved['targetObject']);
        self::assertEquals($queryBuilder, $resolved['query_builder']);
        self::assertEquals(Customer::class, $resolved['class']);
        self::assertEquals(
            [
                'disable'    => false,
                'permission' => 'CREATE',
                'options'    => [
                    'aclDisable'                      => true,
                    'availableOwnerEnable'            => true,
                    'availableOwnerTargetEntityClass' => CustomerUser::class,
                ],
            ],
            $resolved['acl_options']
        );
    }

    public function testConfigureOptionsWithExistingEntity()
    {
        $existingOwner = $this->getEntity(Customer::class, ['id' => 13]);
        $dataObject = new CustomerUser();
        $dataObject->setCustomer($existingOwner);
        $config = new Config(
            new EntityConfigId('ownership'),
            [
                'frontend_owner_type' => 'FRONTEND_CUSTOMER',
                'frontend_owner_field_name' => 'customer'
            ]
        );

        $resolver = new OptionsResolver();
        $resolver->setDefined(['acl_options']);

        $this->configProvider->expects(self::exactly(2))
            ->method('getConfig')
            ->with(CustomerUser::class)
            ->willReturn($config);

        $em = $this->createMock(EntityManager::class);
        $this->registry->expects(self::once())
            ->method('getManagerForClass')
            ->willReturn($em);
        $em->expects(self::once())
            ->method('contains')
            ->with($dataObject)
            ->willReturn(true);

        $this->formType->configureOptions($resolver);
        $resolved = $resolver->resolve(['targetObject' => $dataObject, 'acl_options' => null]);

        self::assertEquals(
            [
                'disable'    => false,
                'permission' => 'ASSIGN',
                'options'    => [
                    'aclDisable'                      => true,
                    'availableOwnerEnable'            => true,
                    'availableOwnerTargetEntityClass' => CustomerUser::class,
                    'availableOwnerCurrentOwner'      => 13
                ],
            ],
            $resolved['acl_options']
        );
    }
}
