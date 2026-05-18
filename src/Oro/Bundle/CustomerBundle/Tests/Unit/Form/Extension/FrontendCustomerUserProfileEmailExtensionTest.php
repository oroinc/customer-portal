<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Extension;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserManager;
use Oro\Bundle\CustomerBundle\Form\Extension\FrontendCustomerUserProfileEmailExtension;
use Oro\Bundle\CustomerBundle\Form\Type\FrontendCustomerUserProfileEmailType;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureChecker;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Contracts\Translation\TranslatorInterface;

class FrontendCustomerUserProfileEmailExtensionTest extends TestCase
{
    private FeatureChecker&MockObject $featureChecker;
    private CustomerUserManager&MockObject $customerUserManager;
    private TranslatorInterface&MockObject $translator;
    private FrontendCustomerUserProfileEmailExtension $extension;

    #[\Override]
    protected function setUp(): void
    {
        $this->featureChecker = $this->createMock(FeatureChecker::class);
        $this->customerUserManager = $this->createMock(CustomerUserManager::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->extension = new FrontendCustomerUserProfileEmailExtension(
            $this->featureChecker,
            $this->customerUserManager,
            $this->translator
        );
    }

    public function testGetExtendedTypes(): void
    {
        self::assertEquals(
            [FrontendCustomerUserProfileEmailType::class],
            FrontendCustomerUserProfileEmailExtension::getExtendedTypes()
        );
    }

    public function testBuildFormWhenFeatureDisabled(): void
    {
        $builder = $this->createMock(FormBuilderInterface::class);

        $this->featureChecker->expects(self::once())
            ->method('isFeatureEnabled')
            ->with('customer_user_email_change_verification_enabled')
            ->willReturn(false);
        $builder->expects(self::never())
            ->method('remove');
        $builder->expects(self::never())
            ->method('addEventListener');
        $builder->expects(self::never())
            ->method('add');

        $this->extension->buildForm($builder, []);
    }

    public function testBuildFormWhenFeatureEnabled(): void
    {
        $builder = $this->createMock(FormBuilderInterface::class);

        $this->featureChecker->expects(self::once())
            ->method('isFeatureEnabled')
            ->with('customer_user_email_change_verification_enabled')
            ->willReturn(true);
        $builder->expects(self::once())
            ->method('remove')
            ->with('email');
        $builder->expects(self::once())
            ->method('add')
            ->with(
                'newEmailAddress',
                EmailType::class,
                self::callback(function (array $options) {
                    self::assertTrue($options['required']);
                    self::assertSame('oro.customer.customeruser.email.label_short', $options['label']);
                    self::assertCount(1, $options['constraints']);
                    self::assertInstanceOf(Email::class, $options['constraints'][0]);

                    return true;
                })
            );
        $builder->expects(self::once())
            ->method('addEventListener')
            ->with(FormEvents::SUBMIT, [$this->extension, 'onSubmit']);

        $this->extension->buildForm($builder, []);
    }

    public function testOnSubmitWhenCustomerUserDoesNotExist(): void
    {
        $customerUser = new CustomerUser();
        $newEmailForm = $this->createMock(FormInterface::class);
        $form = $this->createMock(FormInterface::class);
        $event = new FormEvent($form, $customerUser);

        $form->expects(self::once())
            ->method('get')
            ->with('newEmailAddress')
            ->willReturn($newEmailForm);
        $newEmailForm->expects(self::once())
            ->method('getData')
            ->willReturn('new@example.com');
        $newEmailForm->expects(self::never())
            ->method('addError');
        $this->customerUserManager->expects(self::once())
            ->method('findUserByEmail')
            ->with('new@example.com')
            ->willReturn(null);
        $this->translator->expects(self::never())
            ->method('trans');

        $this->extension->onSubmit($event);

        self::assertSame('new@example.com', $customerUser->getNewEmail());
    }

    public function testOnSubmitWhenCustomerUserExists(): void
    {
        $customerUser = new CustomerUser();
        $existingCustomerUser = new CustomerUser();

        $newEmailForm = $this->createMock(FormInterface::class);
        $form = $this->createMock(FormInterface::class);
        $event = new FormEvent($form, $customerUser);

        $form->expects(self::once())
            ->method('get')
            ->with('newEmailAddress')
            ->willReturn($newEmailForm);
        $newEmailForm->expects(self::once())
            ->method('getData')
            ->willReturn('new@example.com');
        $this->customerUserManager->expects(self::once())
            ->method('findUserByEmail')
            ->with('new@example.com')
            ->willReturn($existingCustomerUser);
        $this->translator->expects(self::once())
            ->method('trans')
            ->with('oro.customer.message.user_customer_exists')
            ->willReturn('Customer user already exists');
        $newEmailForm->expects(self::once())
            ->method('addError')
            ->with(self::callback(function (FormError $error) {
                return 'Customer user already exists' === $error->getMessage();
            }));

        $this->extension->onSubmit($event);

        self::assertNull($customerUser->getNewEmail());
    }
}
