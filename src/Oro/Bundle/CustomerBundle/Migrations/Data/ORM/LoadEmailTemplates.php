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
     *
     * @var array
     */
    protected $emailsUpdateConfig = [
        'customer_user_welcome_email' => [
            'd970bd18538742a4702e70df6f14444d',
            '6f2554689920e2d47ac6ea044fdd8e43',
            '61e82b3d8c7180e362738a98e266f037', // 1.5
            'f9019a7656b1bc2a8094d2aea749de87', // 5.1.19.0
        ],
        'customer_user_welcome_email_registered_by_admin' => [
            'e583b8b7cdea31f8f0ce0a4000b956b9',
            'e2a34aa359ce8d958abc7c3eddd7bc93', // 1.5
            '88c40b11db82246fea232924fcbc21c4', // 5.1.19.0
        ],
        'customer_user_confirmation_email' => ['47e012b40cec188ad88dfb7e3379446d'],
        'customer_user_reset_password' => [
            '4c987be76cdffc3ade87c9fca27a60be',
            '02c65afdfb3e2c61c0c31cd2ff096d0d',
            '471f26ac189cf131b28e4e966c6c0f79', // 1.5
            'f64550cb6e0f68509007198ac9b96111', // 5.1.19.0
        ],
        'customer_user_force_reset_password' => [
            'beb25a213aa466f95ae48d710478fa13',
            '5c9bd2ae22ac6ed6d154fbd62df5cd77', // 1.5
            '7a01bfcfff6913d740ba13951df4ad24', // 5.1.19.0
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function getVersion()
    {
        return '5.1.19.0';
    }

    /**
     * {@inheritdoc}
     */
    protected function findExistingTemplate(ObjectManager $manager, array $template)
    {
        if (empty($template['params']['name'])) {
            return null;
        }

        return $manager->getRepository('OroEmailBundle:EmailTemplate')->findOneBy([
            'name' => $template['params']['name'],
            'entityName' => $template['params']['entityName'],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function updateExistingTemplate(EmailTemplate $emailTemplate, array $template)
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
     * Return path to email templates
     *
     * @return string
     */
    public function getEmailsDir()
    {
        return $this->container
            ->get('kernel')
            ->locateResource('@OroCustomerBundle/Migrations/Data/ORM/data/emails/customer-user');
    }
}
