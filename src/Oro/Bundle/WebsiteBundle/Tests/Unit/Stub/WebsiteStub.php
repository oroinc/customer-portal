<?php

namespace Oro\Bundle\WebsiteBundle\Tests\Unit\Stub;

use Oro\Bundle\WebsiteBundle\Entity\Website;

class WebsiteStub extends Website
{
    public function __construct(int $id)
    {
        $this->id = $id;
    }
}
