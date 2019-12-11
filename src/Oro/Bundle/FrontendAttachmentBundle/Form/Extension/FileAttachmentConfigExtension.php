<?php

namespace Oro\Bundle\FrontendAttachmentBundle\Form\Extension;

use Oro\Bundle\EntityConfigBundle\Entity\FieldConfigModel;
use Oro\Bundle\EntityConfigBundle\Form\Type\ConfigType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Show or hide `file_applications` choice depends on `acl_protected` value
 */
class FileAttachmentConfigExtension extends AbstractTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $configModel = $options['config_model'];
        if ($configModel instanceof FieldConfigModel) {
            $data = $builder->getData();
            $aclProtected = $data['attachment']['acl_protected'] ?? true;
            if (!$aclProtected) {
                $builder->get('attachment')->remove('file_applications');
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes(): iterable
    {
        return [ConfigType::class];
    }
}
