<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Entity;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserApi;
use Oro\Component\PhpUtils\ReflectionUtil;

class CustomerUserApiTest extends \PHPUnit\Framework\TestCase
{
    public function testId()
    {
        $entity = new CustomerUserApi();
        self::assertNull($entity->getId());

        $id = 1;
        $idProperty = ReflectionUtil::getProperty(new \ReflectionClass($entity), 'id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($entity, $id);
        self::assertEquals($id, $entity->getId());
    }

    public function testApiKey()
    {
        $entity = new CustomerUserApi();
        self::assertNull($entity->getApiKey());

        $apiKey = 'test';
        $entity->setApiKey($apiKey);
        self::assertEquals($apiKey, $entity->getApiKey());
    }

    public function testUser()
    {
        $entity = new CustomerUserApi();
        self::assertNull($entity->getUser());

        $user = new CustomerUser();
        $entity->setUser($user);
        self::assertSame($user, $entity->getUser());
    }

    public function testGenerateKey()
    {
        $entity = new CustomerUserApi();
        self::assertNotEmpty($entity->generateKey());
    }

    public function testIsEnabled()
    {
        $entity = new CustomerUserApi();
        self::assertTrue($entity->isEnabled());
    }
}
