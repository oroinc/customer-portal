<?php

namespace Oro\Bundle\CustomerBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\UserBundle\Security\UserApiKeyInterface;

/**
 * The entity that represents API access keys for customer users.
 */
#[ORM\Entity]
#[ORM\Table(name: 'oro_customer_user_api')]
class CustomerUserApi implements UserApiKeyInterface
{
    #[ORM\Id]
    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected ?int $id = null;

    #[ORM\ManyToOne(targetEntity: CustomerUser::class, fetch: 'LAZY', inversedBy: 'apiKeys')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected ?CustomerUser $user = null;

    /**
     * @var string
     */
    #[ORM\Column(name: 'api_key', type: 'crypted_string', length: 255, unique: true, nullable: false)]
    protected $apiKey;

    /**
     * Gets unique identifier of this entity.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Indicates whether this API key is enabled.
     *
     * @return bool
     */
    public function isEnabled()
    {
        return true;
    }

    /**
     * Gets API key.
     *
     * @return string
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * Sets API key.
     *
     * @param string $apiKey
     *
     * @return CustomerUserApi
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    /**
     * Gets the customer user this API key belongs to.
     *
     * @return CustomerUser
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Sets the customer user this API key belongs to.
     *
     * @param CustomerUser $user
     *
     * @return CustomerUserApi
     */
    public function setUser(CustomerUser $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Gets an organization this API key belongs to.
     *
     * @return Organization
     */
    public function getOrganization()
    {
        return $this->getUser()->getOrganization();
    }

    /**
     * Generates random API key.
     *
     * @return string
     */
    public function generateKey()
    {
        return bin2hex(random_bytes(20));
    }
}
