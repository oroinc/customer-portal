<?php

namespace Oro\Bundle\CustomerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\UserBundle\Security\UserApiKeyInterface;

/**
 * The entity that represents API access keys for customer users.
 *
 * @ORM\Table(name="oro_customer_user_api")
 * @ORM\Entity()
 */
class CustomerUserApi implements UserApiKeyInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var CustomerUser
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\CustomerBundle\Entity\CustomerUser", inversedBy="apiKeys", fetch="LAZY")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    protected $user;

    /**
     * @var string
     *
     * @ORM\Column(name="api_key", type="crypted_string", unique=true, length=255, nullable=false)
     */
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
