<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Extension;

use Oro\Bundle\CustomerBundle\Form\Extension\PreferredLocalizationCustomerUserExtension;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerUserType;
use Oro\Bundle\CustomerBundle\Form\Type\EnabledLocalizationSelectType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormBuilderInterface;

class PreferredLocalizationCustomerUserExtensionTest extends TestCase
{
    private EventSubscriberInterface&MockObject $eventSubscriber;
    private PreferredLocalizationCustomerUserExtension $extension;

    #[\Override]
    protected function setUp(): void
    {
        $this->eventSubscriber = $this->createMock(EventSubscriberInterface::class);
        $this->extension = new PreferredLocalizationCustomerUserExtension($this->eventSubscriber);
    }

    public function testGetExtendedTypes(): void
    {
        self::assertEquals([CustomerUserType::class], PreferredLocalizationCustomerUserExtension::getExtendedTypes());
    }

    public function testBuildForm(): void
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
