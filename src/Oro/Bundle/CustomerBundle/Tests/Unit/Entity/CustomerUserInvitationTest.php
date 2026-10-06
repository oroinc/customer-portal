<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Entity;

use Oro\Bundle\CustomerBundle\Entity\CustomerUserInvitation;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\CustomerBundle\Tests\Unit\Entity\Stub\CustomerUserInvitationStub;
use Oro\Bundle\EntityExtendBundle\Tests\Unit\Fixtures\TestEnumValue;
use PHPUnit\Framework\TestCase;

class CustomerUserInvitationTest extends TestCase
{
    public function testEmailIsNormalized(): void
    {
        $invitation = new CustomerUserInvitationStub();

        self::assertEquals($invitation, $invitation->setEmail('  User@Example.COM  '));
        self::assertEquals('User@Example.COM', $invitation->getEmail());
        self::assertEquals('user@example.com', $invitation->getEmailLowercase());

        $invitation->setEmail(null);
        self::assertNull($invitation->getEmail());
        self::assertNull($invitation->getEmailLowercase());
    }

    public function testRolesCanBeManaged(): void
    {
        $firstRole = new CustomerUserRole('ROLE_FIRST');
        $secondRole = new CustomerUserRole('ROLE_SECOND');
        $invitation = new CustomerUserInvitationStub();

        self::assertEquals($invitation, $invitation->addRole($firstRole));
        $invitation->addRole($firstRole);
        self::assertCount(1, $invitation->getRoles());

        self::assertEquals($invitation, $invitation->setRoles([$firstRole, $secondRole]));
        self::assertEquals([$firstRole, $secondRole], $invitation->getRoles()->toArray());

        self::assertEquals($invitation, $invitation->removeRole($firstRole));
        self::assertEquals([$secondRole], array_values($invitation->getRoles()->toArray()));
    }

    public function testPendingStatus(): void
    {
        $invitation = new CustomerUserInvitationStub();

        self::assertFalse($invitation->isPending());

        $invitation->setStatus($this->createStatus(CustomerUserInvitation::STATUS_PENDING));
        self::assertTrue($invitation->isPending());

        $invitation->setStatus($this->createStatus(CustomerUserInvitation::STATUS_ACCEPTED));
        self::assertFalse($invitation->isPending());
    }

    public function testExpiration(): void
    {
        $now = new \DateTimeImmutable('2026-09-16 12:00:00', new \DateTimeZone('UTC'));
        $invitation = new CustomerUserInvitationStub();

        self::assertFalse($invitation->isExpired($now));

        $invitation->setExpiresAt($now->modify('+1 second'));
        self::assertFalse($invitation->isExpired($now));

        $invitation->setExpiresAt($now);
        self::assertTrue($invitation->isExpired($now));

        $invitation->setExpiresAt($now->modify('-1 second'));
        self::assertTrue($invitation->isExpired($now));
    }

    public function testLifecycleCallbacks(): void
    {
        $invitation = new CustomerUserInvitationStub();
        $invitation->prePersist();

        self::assertInstanceOf(\DateTimeInterface::class, $invitation->getCreatedAt());
        self::assertEquals($invitation->getCreatedAt(), $invitation->getUpdatedAt());

        $createdAt = $invitation->getCreatedAt();
        $invitation->setUpdatedAt(new \DateTime('2000-01-01 00:00:00', new \DateTimeZone('UTC')));
        $invitation->preUpdate();

        self::assertEquals($createdAt, $invitation->getCreatedAt());
        self::assertGreaterThan(
            new \DateTime('2000-01-01 00:00:00', new \DateTimeZone('UTC')),
            $invitation->getUpdatedAt()
        );
    }

    private function createStatus(string $status): TestEnumValue
    {
        return new TestEnumValue(CustomerUserInvitation::INTERNAL_STATUS_CODE, ucfirst($status), $status);
    }
}
