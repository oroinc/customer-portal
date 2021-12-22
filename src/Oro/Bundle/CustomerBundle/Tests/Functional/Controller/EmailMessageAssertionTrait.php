<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Controller;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\ConfigBundle\Tests\Functional\Traits\ConfigManagerAwareTestTrait;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Symfony\Component\Mime\Email as SymfonyEmail;
use Symfony\Component\Mime\RawMessage;

/**
 * Helps checking that welcome message is correct.
 */
trait EmailMessageAssertionTrait
{
    use ConfigManagerAwareTestTrait;

    protected function assertWelcomeMessage(string $email, RawMessage $welcomeMessage): void
    {
        self::assertInstanceOf(SymfonyEmail::class, $welcomeMessage);

        /** @var EntityRepository $customerUserRepo */
        $customerUserRepo = self::getContainer()->get('doctrine')->getRepository(CustomerUser::class);

        /** @var CustomerUser $user */
        $user = $customerUserRepo->findOneBy(['email' => $email]);

        self::assertNotNull($user);

        self::assertEmailAddressContains($welcomeMessage, 'to', $email);
        self::assertEmailAddressContains(
            $welcomeMessage,
            'from',
            self::getConfigManager(null)->get(
                'oro_notification.email_notification_sender_email'
            )
        );
        self::assertStringStartsWith('Welcome:', $welcomeMessage->getSubject());
        self::assertStringContainsString($user->getFirstName(), $welcomeMessage->getSubject());
        self::assertStringContainsString($user->getLastName(), $welcomeMessage->getSubject());
        self::assertEmailHtmlBodyContains($welcomeMessage, $email);

        $applicationUrl = self::getConfigManager(null)->get('oro_ui.application_url');
        self::assertEmailHtmlBodyContains($welcomeMessage, $applicationUrl);

        $resetUrl = $this->getUrl(
            'oro_customer_frontend_customer_user_password_reset',
            [
                'token' => $user->getConfirmationToken(),
            ]
        );
        self::assertEmailHtmlBodyContains($welcomeMessage, htmlentities($resetUrl));
    }
}
