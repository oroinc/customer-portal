<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Controller;

use Doctrine\Persistence\ObjectRepository;
use Oro\Bundle\ConfigBundle\Tests\Functional\Traits\ConfigManagerAwareTestTrait;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;

trait EmailMessageAssertionTrait
{
    use ConfigManagerAwareTestTrait;

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

        self::assertNotNull($user);

        self::assertEquals($email, key($welcomeMessage->getTo()));
        self::assertEquals(
            self::getConfigManager(null)->get('oro_notification.email_notification_sender_email'),
            key($welcomeMessage->getFrom())
        );
        self::assertStringContainsString($user->getFirstName(), $welcomeMessage->getSubject());
        self::assertStringContainsString($user->getLastName(), $welcomeMessage->getSubject());
        self::assertStringContainsString($email, $welcomeMessage->getBody());

        $applicationUrl = self::getConfigManager(null)->get('oro_ui.application_url');
        self::assertStringContainsString($applicationUrl, $welcomeMessage->getBody());

        $resetUrl = $this->getUrl(
            'oro_customer_frontend_customer_user_password_reset',
            [
                'token' => $user->getConfirmationToken(),
            ]
        );
        self::assertStringContainsString(htmlentities($resetUrl), $welcomeMessage->getBody());
    }
}
