<?php

namespace Oro\Bundle\CustomerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\CustomerBundle\Model\ExtendCustomerUserSettings;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\LocaleBundle\Entity\Localization;
use Oro\Bundle\WebsiteBundle\Entity\Website;

/**
 * @Config()
 * @ORM\Entity
 * @ORM\Table(
 *    name="oro_customer_user_settings",
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="unique_cus_user_website", columns={"customer_user_id", "website_id"})
 *    }
 * )
 */
class CustomerUserSettings extends ExtendCustomerUserSettings
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var CustomerUser
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\CustomerBundle\Entity\CustomerUser", inversedBy="settings")
     * @ORM\JoinColumn(name="customer_user_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    protected $customerUser;

    /**
     * @var Website
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\WebsiteBundle\Entity\Website")
     * @ORM\JoinColumn(name="website_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    protected $website;

    /**
     * @var string
     *
     * @ORM\Column(name="currency", type="string", length=3, nullable=true)
     */
    protected $currency;

    /**
     * @var Localization
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\LocaleBundle\Entity\Localization")
     * @ORM\JoinColumn(name="localization_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    protected $localization;

    /**
     * @param Website $website
     */
    public function __construct(Website $website)
    {
        parent::__construct();
        $this->website = $website;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return CustomerUser
     */
    public function getCustomerUser()
    {
        return $this->customerUser;
    }

    /**
     * @param CustomerUser $customerUser
     * @return $this
     */
    public function setCustomerUser(CustomerUser $customerUser)
    {
        $this->customerUser = $customerUser;

        return $this;
    }

    /**
     * @return Website
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     * @return $this
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * @return Localization
     */
    public function getLocalization()
    {
        return $this->localization;
    }

    /**
     * @param Localization $localization
     * @return $this
     */
    public function setLocalization(Localization $localization = null)
    {
        $this->localization = $localization;

        return $this;
    }
}
