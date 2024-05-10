<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Entity;

use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserLoginAttempt;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserData;
use Oro\Bundle\SecurityBundle\Tools\UUIDGenerator;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class CustomerUserLoginAttemptTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->initClient();
        $this->loadFixtures([LoadCustomerUserData::class]);
    }

    /**
     * @dataProvider userAgentDataProvider
     */
    public function testUserAgentLengthInDb(string $userAgent): void
    {
        $entityManager = $this->getEntityManager();

        /** @var Customer $customer */
        $customer = $this->getReference(LoadCustomerUserData::EMAIL);
        $uuid = UUIDGenerator::v4();

        $customerUserLoginAttempt = new CustomerUserLoginAttempt();
        $customerUserLoginAttempt->setId($uuid);
        $customerUserLoginAttempt->setUser($customer);
        $customerUserLoginAttempt->setSource(1);
        $customerUserLoginAttempt->setAttemptAt(new \DateTime('now'));
        $customerUserLoginAttempt->setIp('127.0.0.1');
        $customerUserLoginAttempt->setUserAgent($userAgent);
        $customerUserLoginAttempt->setSuccess(1);
        $customerUserLoginAttempt->setUsername('userName');
        $customerUserLoginAttempt->setContext([]);

        $entityManager->persist($customerUserLoginAttempt);
        $entityManager->flush();

        $savedCustomerLoginAttempt = $entityManager->getRepository(CustomerUserLoginAttempt::class)
            ->findOneBy(['id' => $uuid]);

        self::assertNotNull($savedCustomerLoginAttempt);
    }

    public function userAgentDataProvider(): array
    {
        return [
            [
                "Mozilla\\/5.0 (iPhone; CPU iPhone OS 17_3_1 like Mac OS X) AppleWebKit\\/605.1.15 (KHTML, like Gecko) "
                ."Mobile\\/21D61 [FBAN\\/FBIOS;FBAV\\/452.0.0.39.110;FBBV\\/569146793;FBDV\\/iPhone13,3;FBMD\\/iPhone;"
                ."FBSN\\/iOS;FBSV\\/17.3.1;FBSS\\/3;FBID\\/phone;FBLC\\/nl_NL;FBOP\\/5;FBRV\\/571609390]"
            ],
            [
                "Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0"
            ],
            [
                "Mozilla/5.0"
            ]
        ];
    }

    private function getEntityManager(): EntityManagerInterface
    {
        return $this->getContainer()->get('doctrine')->getManagerForClass(CustomerUserLoginAttempt::class);
    }
}
