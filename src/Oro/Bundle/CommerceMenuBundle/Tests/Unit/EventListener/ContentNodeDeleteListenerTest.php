<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\EventListener;

use Doctrine\Common\Cache\CacheProvider;
use Oro\Bundle\CommerceMenuBundle\EventListener\ContentNodeDeleteListener;

class ContentNodeDeleteListenerTest extends \PHPUnit\Framework\TestCase
{
    /** @var CacheProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $cacheProvider;

    /** @var ContentNodeDeleteListener */
    private $listener;

    protected function setUp(): void
    {
        $this->cacheProvider = $this->createMock(CacheProvider::class);
        $this->listener = new ContentNodeDeleteListener($this->cacheProvider);
    }

    public function testPostRemove(): void
    {
        $this->cacheProvider->expects($this->once())
            ->method('deleteAll');

        $this->listener->postRemove();
    }
}
