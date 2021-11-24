<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Extension;

use Oro\Bundle\CustomerBundle\Form\Extension\PreferredLocalizationCustomerUserExtension;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerUserType;
use Oro\Bundle\CustomerBundle\Form\Type\EnabledLocalizationSelectType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormBuilderInterface;

class PreferredLocalizationCustomerUserExtensionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var EventSubscriberInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $eventSubscriber;

    /**
     * @var PreferredLocalizationCustomerUserExtension
     */
    private $extension;

    protected function setUp(): void
    {
        $this->eventSubscriber = $this->createMock(EventSubscriberInterface::class);
        $this->extension = new PreferredLocalizationCustomerUserExtension($this->eventSubscriber);
    }

    public function testGetExtendedTypes()
    {
        self::assertEquals([CustomerUserType::class], PreferredLocalizationCustomerUserExtension::getExtendedTypes());
    }

    public function testBuildForm()
    {
        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->expects($this->once())
            ->method('addEventSubscriber')
            ->with($this->eventSubscriber);
        $builder->expects($this->once())
            ->method('add')
            ->with(
                PreferredLocalizationCustomerUserExtension::PREFERRED_LOCALIZATION_FIELD,
                EnabledLocalizationSelectType::class,
                [
                    'label' => 'oro.customer.customeruser.preferred_localization.label',
                    'required' => false,
                    'mapped' => false,
                    'configs' => [
                        'component' => 'autocomplete-enabledlocalization',
                        'placeholder' => 'oro.customer.customeruser.preferred_localization.placeholder',
                    ],
                ]
            );

        $this->extension->buildForm($builder, []);
    }
}
