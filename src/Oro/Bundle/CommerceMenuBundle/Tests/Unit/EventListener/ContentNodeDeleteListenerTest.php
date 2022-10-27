<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\EventListener;

use Oro\Bundle\CommerceMenuBundle\EventListener\ContentNodeDeleteListener;
use Symfony\Component\Cache\Adapter\AbstractAdapter;

class ContentNodeDeleteListenerTest extends \PHPUnit\Framework\TestCase
{
    /** @var AbstractAdapter|\PHPUnit\Framework\MockObject\MockObject */
    private $cacheProvider;

    /** @var ContentNodeDeleteListener */
    private $listener;

    protected function setUp(): void
    {
        $this->cacheProvider = $this->createMock(AbstractAdapter::class);
        $this->listener = new ContentNodeDeleteListener($this->cacheProvider);
    }

    public function testPostRemove(): void
    {
        $this->cacheProvider->expects($this->once())
            ->method('clear');

        $this->listener->postRemove();
    }
}
