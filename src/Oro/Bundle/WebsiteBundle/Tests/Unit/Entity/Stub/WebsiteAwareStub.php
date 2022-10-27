<?php

namespace Oro\Bundle\WebsiteBundle\Tests\Unit\Entity\Stub;

use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Entity\WebsiteAwareInterface;

class WebsiteAwareStub implements WebsiteAwareInterface
{
    /**
     * @var Website
     */
    private $website;

    public function __construct(Website $website = null)
    {
        $this->website = $website;
    }

    /**
     * {@inheritdoc}
     */
    public function setWebsite(Website $website)
    {
        $this->website = $website;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getWebsite()
    {
        return $this->website;
    }
}
