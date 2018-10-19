<?php

namespace Oro\Bundle\CustomerBundle\Form\Type;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Util\ClassUtils;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\EntityConfigBundle\Config\ConfigInterface;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\SecurityBundle\AccessRule\AclAccessRule;
use Oro\Bundle\SecurityBundle\AccessRule\AvailableOwnerAccessRule;
use Oro\Bundle\TranslationBundle\Form\Type\Select2TranslatableEntityType;
use Oro\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for selecting entity owner.
 */
class FrontendOwnerSelectType extends AbstractType
{
    const NAME = 'oro_customer_frontend_owner_select';

    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @var ConfigProvider
     */
    protected $configProvider;

    /**
     * @param ManagerRegistry $registry
     * @param ConfigProvider $configProvider
     */
    public function __construct(ManagerRegistry $registry, ConfigProvider $configProvider)
    {
        $this->registry = $registry;
        $this->configProvider = $configProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'choice_label' => function ($owner) {
                    if ($owner instanceof CustomerUser) {
                        return $owner->getFullName();
                    }

                    return (string)$owner;
                },
                'class' => null,
            ]
        );

        $resolver->setDefined('targetObject');
        $resolver->setDefined('query_builder');
        $resolver->setDefined('class');

        $resolver->setNormalizer('acl_options', function (Options $options) {
            $data = $options['targetObject'];
            $class = ClassUtils::getClass($data);
            $aclOptions = [
                AclAccessRule::DISABLE_RULE => true,
                AvailableOwnerAccessRule::ENABLE_RULE => true,
                AvailableOwnerAccessRule::TARGET_ENTITY_CLASS => $class
            ];

            $permission = 'CREATE';

            $em = $this->registry->getManagerForClass($class);
            $isObjectNew = !$em->contains($data);
            if (!$isObjectNew) {
                $permission = 'ASSIGN';
                $config = $this->configProvider->getConfig($class);
                $ownerFieldName = $config->get('frontend_owner_field_name');
                $propertyAccessor = new PropertyAccessor();
                $currentOwner = $propertyAccessor->getValue($data, $ownerFieldName);
                if ($currentOwner) {
                    $aclOptions[AvailableOwnerAccessRule::CURRENT_OWNER] = $currentOwner->getId();
                }
            }

            return [
                'disable' => false,
                'permission' => $permission,
                'options' => $aclOptions
            ];
        });

        $resolver->setNormalizer('query_builder', function (Options $options) {
            $class = ClassUtils::getClass($options['targetObject']);
            $ownerClass = $this->getOwnerClass($this->configProvider->getConfig($class));

            return $this->registry->getRepository($ownerClass)->createQueryBuilder('owner');
        });

        $resolver->setNormalizer('class', function (Options $options) {
            $data = $options['targetObject'];
            $class = ClassUtils::getClass($data);
            $config = $this->configProvider->getConfig($class);
            return $this->getOwnerClass($config);
        });
    }

    /**
     * @param ConfigInterface $config
     * @return string
     */
    private function getOwnerClass(ConfigInterface $config)
    {
        $ownerType = $config->get('frontend_owner_type');
        $ownerClass = ($ownerType == 'FRONTEND_CUSTOMER') ? Customer::class : CustomerUser::class;
        return $ownerClass;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return Select2TranslatableEntityType::class;
    }
}
