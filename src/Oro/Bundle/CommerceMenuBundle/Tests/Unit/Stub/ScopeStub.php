<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\Stub;

use Oro\Bundle\ScopeBundle\Entity\Scope;
use Oro\Bundle\WebsiteBundle\Entity\Website;

class ScopeStub extends Scope
{
    /** @var Website|null */
    private $website;

    /**
     * @param Website|null $website
     *
     * @return $this
     */
    public function setWebsite($website)
    {
        $this->website = $website;

        return $this;
    }

    /**
     * @return Website|null
     */
    public function getWebsite()
    {
        return $this->website;
    }
}
