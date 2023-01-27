<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\Entity\Stub;

use Oro\Bundle\CatalogBundle\Entity\Category;
use Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate;

class MenuUpdateStub extends MenuUpdate
{
    /**
     * @var mixed
     */
    protected $image;

    /** @var Category|null */
    protected $category;

    public function __construct(?int $id = null)
    {
        if ($id !== null) {
            $this->id = $id;
        }
        
        parent::__construct();
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

    /**
     * @return Category|null
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param Category|null $category
     * @return MenuUpdateStub
     */
    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }
}
