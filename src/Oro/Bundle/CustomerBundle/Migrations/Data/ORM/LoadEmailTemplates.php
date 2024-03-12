<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Data\ORM;

use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\EmailBundle\Entity\EmailTemplate;
use Oro\Bundle\EmailBundle\Migrations\Data\ORM\AbstractEmailFixture;
use Oro\Bundle\MigrationBundle\Fixture\VersionedFixtureInterface;

/**
 * Load email templates for Customer User entity.
 * Load new templates if not present, update existing as configured by emailsUpdateConfig.
 */
class LoadEmailTemplates extends AbstractEmailFixture implements VersionedFixtureInterface
{
    /**
     * To update template without overriding customized content add it's name as key and add expected previous
     * content MD5 to array of hashes.
     * To force update replace content hashes array with true.
     *
     * [
     *     <template_name> => [<MD5_of_previous_version_allowed_to_update>],
     *     <template_name_2> => true
     * ]
     */
    private array $emailsUpdateConfig = [
        'customer_user_welcome_email' => ['d970bd18538742a4702e70df6f14444d', '6f2554689920e2d47ac6ea044fdd8e43'],
        'customer_user_welcome_email_registered_by_admin' => ['e583b8b7cdea31f8f0ce0a4000b956b9'],
        'customer_user_confirmation_email' => ['47e012b40cec188ad88dfb7e3379446d'],
        'customer_user_reset_password' => ['4c987be76cdffc3ade87c9fca27a60be'],
    ];

    /**
     * {@inheritDoc}
     */
    public function getVersion(): string
    {
        return '1.3';
    }

    /**
     * {@inheritDoc}
     */
    protected function findExistingTemplate(ObjectManager $manager, array $template): ?EmailTemplate
    {
        if (empty($template['params']['name'])) {
            return null;
        }

        return $manager->getRepository(EmailTemplate::class)->findOneBy([
            'name' => $template['params']['name'],
            'entityName' => $template['params']['entityName']
        ]);
    }

    /**
     * {@inheritDoc}
     */
    protected function updateExistingTemplate(EmailTemplate $emailTemplate, array $template): void
    {
        foreach ($this->emailsUpdateConfig as $templateName => $contentHashes) {
            if ($emailTemplate->getName() === $templateName
                && ($contentHashes === true || \in_array(md5($emailTemplate->getContent()), $contentHashes, true))
            ) {
                parent::updateExistingTemplate($emailTemplate, $template);
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getEmailsDir(): string
    {
        return $this->container
            ->get('kernel')
            ->locateResource('@OroCustomerBundle/Migrations/Data/ORM/data/emails/customer-user');
    }
}
