<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Controller;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Oro\Bundle\CustomerBundle\Entity\Repository\CustomerUserRoleRepository;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

abstract class AbstractUserControllerTest extends WebTestCase
{
    /**
     * @return string
     */
    abstract protected function getEmail();

    /**
     * @return array
     */
    public function createDataProvider()
    {
        return [
            'simple create' => [
                'email' => $this->getEmail(),
                'password' => '123456',
                'isPasswordGenerate' => false,
                'isSendEmail' => false,
                'emailsCount' => 0
            ],
            'create with email and without password generator' => [
                'email' => 'second@example.com',
                'password' => '123456',
                'isPasswordGenerate' => false,
                'isSendEmail' => true,
                'emailsCount' => 1
            ],
            'create with email and password generator' => [
                'email' => 'third@example.com',
                'password' => '',
                'isPasswordGenerate' => true,
                'isSendEmail' => true,
                'emailsCount' => 1
            ]
        ];
    }

    /**
     * @param string $email
     * @param \Swift_Message $message
     */
    protected function assertMessage($email, \Swift_Message $message)
    {
        /** @var \Oro\Bundle\CustomerBundle\Entity\CustomerUser $user */
        $user = $this->getUserRepository()->findOneBy(['email' => $email]);

        $this->assertNotNull($user);

        $this->assertInstanceOf('\Swift_Message', $message);

        $this->assertEquals($email, key($message->getTo()));
        $this->assertEquals(
            $this->getContainer()->get('oro_config.manager')->get('oro_notification.email_notification_sender_email'),
            key($message->getFrom())
        );

        $this->assertContains($email, $message->getSubject());
        $this->assertContains($email, $message->getBody());
    }

    /**
     * @return ObjectManager
     */
    protected function getObjectManager()
    {
        return $this->getContainer()->get('doctrine')->getManager();
    }

    /**
     * @return ObjectRepository
     */
    protected function getUserRepository()
    {
        return $this->getObjectManager()->getRepository('OroCustomerBundle:CustomerUser');
    }

    /**
     * @return CustomerUserRoleRepository
     */
    protected function getUserRoleRepository()
    {
        return $this->getObjectManager()->getRepository('OroCustomerBundle:CustomerUserRole');
    }

    /**
     * @return ObjectRepository
     */
    protected function getCustomerRepository()
    {
        return $this->getObjectManager()->getRepository('OroCustomerBundle:Customer');
    }
}
