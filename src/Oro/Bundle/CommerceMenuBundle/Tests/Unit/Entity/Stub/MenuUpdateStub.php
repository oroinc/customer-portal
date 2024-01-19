<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\Entity\Stub;

use Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate;

class MenuUpdateStub extends MenuUpdate
{
    /**
     * @var mixed
     */
    protected $image;

    public function setId($id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param $image
     * @return MenuUpdateStub
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }
}
