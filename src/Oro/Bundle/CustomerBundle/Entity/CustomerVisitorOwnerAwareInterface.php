<?php

namespace Oro\Bundle\CustomerBundle\Entity;

interface CustomerVisitorOwnerAwareInterface
{
    /**
     * @return \Oro\Bundle\CustomerBundle\Entity\CustomerVisitor
     */
    public function getVisitor();
}
