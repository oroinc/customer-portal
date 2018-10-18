<?php

namespace Oro\Bundle\CustomerBundle\Validator\Constraints;

use Oro\Bundle\CustomerBundle\Entity\CustomerUserManager;
use Oro\Bundle\CustomerBundle\Entity\Repository\CustomerUserRepository;
use Oro\Bundle\DataGridBundle\Tools\DatagridRouteHelper;
use Oro\Bundle\FilterBundle\Form\Type\Filter\TextFilterType;
use Oro\Bundle\FilterBundle\Grid\Extension\AbstractFilterExtension;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Validates a case insensitive option which can be edited in system configuration.
 * Cannot be enabled for database with user which have duplications by email in lowercase.
 */
class EmailCaseInsensitiveOptionValidator extends ConstraintValidator
{
    private const LIMIT = 10;

    /** @var CustomerUserManager */
    private $userManager;

    /** @var TranslatorInterface */
    private $translator;

    /** @var DatagridRouteHelper */
    private $datagridRouteHelper;

    /**
     * @param CustomerUserManager $userManager
     * @param TranslatorInterface $translator
     * @param DatagridRouteHelper $datagridRouteHelper
     */
    public function __construct(
        CustomerUserManager $userManager,
        TranslatorInterface $translator,
        DatagridRouteHelper $datagridRouteHelper
    ) {
        $this->userManager = $userManager;
        $this->translator = $translator;
        $this->datagridRouteHelper = $datagridRouteHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof EmailCaseInsensitiveOptionConstraint) {
            throw new UnexpectedTypeException($constraint, EmailCaseInsensitiveOptionConstraint::class);
        }

        if ($value) {
            $this->checkDuplicatedEmails($constraint, $value);
        }
    }

    /**
     * @param EmailCaseInsensitiveOptionConstraint $constraint
     * @param bool $value
     */
    private function checkDuplicatedEmails(EmailCaseInsensitiveOptionConstraint $constraint, $value)
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

    /**
     * @param array $emails
     * @return string
     */
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

    /**
     * @return CustomerUserRepository
     */
    private function getRepository()
    {
        return $this->userManager->getRepository();
    }
}
