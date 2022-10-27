<?php

namespace Oro\Bundle\CustomerBundle\Validator\Constraints;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\Repository\CustomerUserRepository;
use Oro\Bundle\DataGridBundle\Tools\DatagridRouteHelper;
use Oro\Bundle\FilterBundle\Form\Type\Filter\TextFilterType;
use Oro\Bundle\FilterBundle\Grid\Extension\AbstractFilterExtension;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Validates a case insensitive option which can be edited in system configuration.
 * Cannot be enabled for database with user which have duplications by email in lowercase.
 */
class EmailCaseInsensitiveOptionValidator extends ConstraintValidator
{
    private const LIMIT = 10;

    private ManagerRegistry $doctrine;
    private TranslatorInterface $translator;
    private DatagridRouteHelper $datagridRouteHelper;

    public function __construct(
        ManagerRegistry $doctrine,
        TranslatorInterface $translator,
        DatagridRouteHelper $datagridRouteHelper
    ) {
        $this->doctrine = $doctrine;
        $this->translator = $translator;
        $this->datagridRouteHelper = $datagridRouteHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof EmailCaseInsensitiveOption) {
            throw new UnexpectedTypeException($constraint, EmailCaseInsensitiveOption::class);
        }

        if ($value) {
            $this->checkDuplicatedEmails($constraint, $value);
        }
    }

    private function checkDuplicatedEmails(EmailCaseInsensitiveOption $constraint, mixed $value): void
    {
        $emails = $this->getRepository()->findLowercaseDuplicatedEmails(self::LIMIT);
        if (!$emails) {
            return;
        }

        $clickHere = sprintf(
            '<a href="%s">%s</a>',
            $this->buildLink($emails),
            $this->translator->trans($constraint->clickHere, [], 'validators')
        );

        $this->context->buildViolation($constraint->message)
            ->setParameters(['%click_here%' => $clickHere])
            ->setInvalidValue($value)
            ->addViolation();
    }

    private function buildLink(array $emails): string
    {
        return $this->datagridRouteHelper->generate(
            'oro_customer_customer_user_index',
            'customer-customer-user-grid',
            [
                AbstractFilterExtension::MINIFIED_FILTER_PARAM => [
                    'email' => [
                        'type' => TextFilterType::TYPE_IN,
                        'value' => implode(',', $emails),
                    ]
                ]
            ]
        );
    }

    private function getRepository(): CustomerUserRepository
    {
        return $this->doctrine->getRepository(CustomerUser::class);
    }
}
