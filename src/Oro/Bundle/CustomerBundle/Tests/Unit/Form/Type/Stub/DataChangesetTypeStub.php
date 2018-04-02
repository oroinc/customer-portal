<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type\Stub;

use Oro\Bundle\FormBundle\Form\Type\DataChangesetType;
use Symfony\Component\Form\FormBuilderInterface;

class DataChangesetTypeStub extends DataChangesetType
{
    /** {@inheritdoc} */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    }
}
