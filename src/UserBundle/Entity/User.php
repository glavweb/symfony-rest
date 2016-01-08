<?php

namespace UserBundle\Entity;

use Sonata\UserBundle\Entity\BaseUser as BaseUser;
use Sonata\UserBundle\Model\HasOwnerInterface;

/**
 * Class User
 * @package UserBundle\Entity
 */
class User extends BaseUser implements HasOwnerInterface
{
    /**
     * @var integer $id
     */
    protected $id;

    /**
     * Return array of owner fields
     *
     * @return array
     */
    public static function getOwnerFields()
    {
        return array(
            'id'
        );
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->projects = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer $id
     */
    public function getId()
    {
        return $this->id;
    }
}
