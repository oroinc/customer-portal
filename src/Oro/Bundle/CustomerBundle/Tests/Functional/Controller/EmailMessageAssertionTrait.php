<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Controller;

use Doctrine\Persistence\ObjectRepository;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;

trait EmailMessageAssertionTrait
{
    /**
     * @param string $email
     * @param \Swift_Message $welcomeMessage
     */
    protected function assertWelcomeMessage($email, \Swift_Message $welcomeMessage)
    {
        /** @var ObjectRepository $customerUserRepo */
        $customerUserRepo = $this->getContainer()->get('doctrine')
            ->getManagerForClass(CustomerUser::class)
            ->getRepository(CustomerUser::class);

        /** @var CustomerUser $user */
        $user = $customerUserRepo->findOneBy(['email' => $email]);

        $this->assertNotNull($user);

        $this->assertEquals($email, key($welcomeMessage->getTo()));
        $this->assertEquals(
            $this->getContainer()->get('oro_config.manager')->get('oro_notification.email_notification_sender_email'),
            key($welcomeMessage->getFrom())
        );
        static::assertStringContainsString($user->getFirstName(), $welcomeMessage->getSubject());
        static::assertStringContainsString($user->getLastName(), $welcomeMessage->getSubject());
        static::assertStringContainsString($email, $welcomeMessage->getBody());

        $applicationUrl = $this->getContainer()->get('oro_config.manager')->get('oro_ui.application_url');
        static::assertStringContainsString($applicationUrl, $welcomeMessage->getBody());

        $resetUrl = $this->getUrl(
            'oro_customer_frontend_customer_user_password_reset',
            [
                'token' => $user->getConfirmationToken(),
            ]
        );
        static::assertStringContainsString(htmlentities($resetUrl), $welcomeMessage->getBody());
    }
}
