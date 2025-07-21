<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\EventListener;

use Oro\Bundle\CommerceMenuBundle\EventListener\ContentNodeDeleteListener;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\AbstractAdapter;

class ContentNodeDeleteListenerTest extends TestCase
{
    private AbstractAdapter&MockObject $cacheProvider;
    private ContentNodeDeleteListener $listener;

    #[\Override]
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
