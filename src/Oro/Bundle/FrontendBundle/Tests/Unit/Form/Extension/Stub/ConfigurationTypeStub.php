<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Form\Extension\Stub;

use Oro\Bundle\InstallerBundle\Form\Type\ConfigurationType;
use Symfony\Component\Form\FormBuilderInterface;

class ConfigurationTypeStub extends ConfigurationType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    }
}
