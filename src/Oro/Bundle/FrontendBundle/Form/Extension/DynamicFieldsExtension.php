<?php

namespace Oro\Bundle\FrontendBundle\Form\Extension;

use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Config\Id\FieldConfigId;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Event\PreSetDataEvent;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;

/**
 * Removed extended fields from a form based on the configuration for an entity/field.
 */
class DynamicFieldsExtension extends AbstractTypeExtension
{
    /** @var FrontendHelper */
    private $frontendHelper;

    /** @var ConfigManager */
    private $configManager;

    public function __construct(FrontendHelper $frontendHelper, ConfigManager $configManager)
    {
        $this->frontendHelper = $frontendHelper;
        $this->configManager = $configManager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($this->frontendHelper->isFrontendRequest()) {
            $builder->addEventListener(
                FormEvents::PRE_SET_DATA,
                function (PreSetDataEvent $event) {
                    $form = $event->getForm();
                    $className = $form->getConfig()->getOption('data_class');

                    $frontendConfigProvider = $this->configManager->getProvider('frontend');
                    $extendConfigProvider = $this->configManager->getProvider('extend');

                    foreach ($frontendConfigProvider->getConfigs($className) as $formConfig) {
                        $fieldConfigId = $formConfig->getId();
                        if (!$fieldConfigId instanceof FieldConfigId || $formConfig->is('is_editable')) {
                            continue;
                        }

                        $fieldName = $fieldConfigId->getFieldName();
                        $extendConfig = $extendConfigProvider->getConfig($className, $fieldName);

                        if ($extendConfig->is('owner', ExtendScope::OWNER_CUSTOM) && $form->has($fieldName)) {
                            $form->remove($fieldName);
                        }
                    }
                },
                -256
            );
        }
    }

    public static function getExtendedTypes(): array
    {
        return [FormType::class];
    }
}
