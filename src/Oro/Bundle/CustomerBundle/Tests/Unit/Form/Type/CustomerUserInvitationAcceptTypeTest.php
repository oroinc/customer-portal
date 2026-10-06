<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerUserInvitationAcceptType;
use Oro\Bundle\CustomerBundle\Form\Type\FrontendCustomerUserRegistrationType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraint;

class CustomerUserInvitationAcceptTypeTest extends TestCase
{
    private CustomerUserInvitationAcceptType $formType;

    #[\Override]
    protected function setUp(): void
    {
        $this->formType = new CustomerUserInvitationAcceptType();
    }

    public function testBuildForm(): void
    {
        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->expects(self::once())
            ->method('has')
            ->with('companyName')
            ->willReturn(true);
        $builder->expects(self::once())
            ->method('remove')
            ->with('companyName')
            ->willReturn($builder);
        $builder->expects(self::once())
            ->method('add')
            ->with('email', EmailType::class, [
                'required' => true,
                'label' => 'oro.customer.customeruser.email.label_short',
                'disabled' => true,
                'attr' => ['placeholder' => 'oro.customer.customeruser.placeholder.email']
            ])
            ->willReturn($builder);

        $this->formType->buildForm($builder, []);
    }

    public function testBuildFormWithoutCompanyName(): void
    {
        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->expects(self::once())
            ->method('has')
            ->with('companyName')
            ->willReturn(false);
        $builder->expects(self::never())
            ->method('remove');
        $builder->expects(self::once())
            ->method('add')
            ->willReturn($builder);

        $this->formType->buildForm($builder, []);
    }

    public function testConfigureOptions(): void
    {
        $resolver = new OptionsResolver();
        $this->formType->configureOptions($resolver);

        $options = $resolver->resolve();
        self::assertEquals(CustomerUser::class, $options['data_class']);
        self::assertEquals('customer_user_invitation_accept', $options['csrf_token_id']);
        self::assertIsCallable($options['validation_groups']);
        self::assertEquals(CustomerUserInvitationAcceptType::NAME, $this->formType->getBlockPrefix());
        self::assertEquals(FrontendCustomerUserRegistrationType::class, $this->formType->getParent());
    }

    /**
     * @dataProvider validationGroupsDataProvider
     */
    public function testValidationGroups(bool $hasPassword, array $expectedValidationGroups): void
    {
        $resolver = new OptionsResolver();
        $this->formType->configureOptions($resolver);
        $options = $resolver->resolve();

        $form = $this->createMock(FormInterface::class);
        $form->expects(self::once())
            ->method('has')
            ->with('plainPassword')
            ->willReturn($hasPassword);

        self::assertEquals($expectedValidationGroups, $options['validation_groups']($form));
    }

    public static function validationGroupsDataProvider(): array
    {
        return [
            'password enabled' => [true, [Constraint::DEFAULT_GROUP, 'create']],
            'password disabled' => [false, [Constraint::DEFAULT_GROUP]],
        ];
    }

    public function testFinishViewMovesEmailFieldToTheBeginning(): void
    {
        $view = new FormView();
        $view->children = [
            'firstName' => new FormView($view),
            'lastName' => new FormView($view),
            'email' => new FormView($view),
            'plainPassword' => new FormView($view),
        ];

        $this->formType->finishView($view, $this->createMock(FormInterface::class), []);

        self::assertEquals(
            ['email', 'firstName', 'lastName', 'plainPassword'],
            array_keys($view->children)
        );
    }
}
