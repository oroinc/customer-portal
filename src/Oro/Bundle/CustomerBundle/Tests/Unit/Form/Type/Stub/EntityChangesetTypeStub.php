<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type\Stub;

use Oro\Bundle\FormBundle\Form\Type\EntityChangesetType;
use Symfony\Component\Form\FormBuilderInterface;

class EntityChangesetTypeStub extends EntityChangesetType
{
    public function __construct()
    {
    }

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    }
}
