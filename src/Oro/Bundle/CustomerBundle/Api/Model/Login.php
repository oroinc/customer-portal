<?php

namespace Oro\Bundle\CustomerBundle\Api\Model;

/**
 * The model for frontend API resource to retrieve API access key by customer user email and password.
 */
class Login
{
    private ?string $email = null;
    private ?string $password = null;
    private ?string $apiKey = null;

    /**
     * Gets the email.
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * Sets the email.
     */
    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    /**
     * Gets the password.
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * Sets the password.
     */
    public function setPassword(?string $password): void
    {
        $this->password = $password;
    }

    /**
     * Gets the API access key that should be used for subsequent API requests.
     */
    public function getApiKey(): ?string
    {
        return $this->apiKey;
    }

    /**
     * Sets the API access key belongs to the customer user with the given email and password.
     */
    public function setApiKey(?string $apiKey): void
    {
        $this->apiKey = $apiKey;
    }
}
