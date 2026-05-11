<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Model;

use Oro\Bundle\CustomerBundle\Model\EmailHolder;
use Oro\Bundle\EmailBundle\Model\EmailHolderInterface;
use PHPUnit\Framework\TestCase;

class EmailHolderTest extends TestCase
{
    public function testGetEmail(): void
    {
        $emailHolder = new EmailHolder('user@example.com');

        self::assertInstanceOf(EmailHolderInterface::class, $emailHolder);
        self::assertSame('user@example.com', $emailHolder->getEmail());
    }
}
