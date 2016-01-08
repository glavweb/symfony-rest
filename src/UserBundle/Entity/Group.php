<?php

namespace UserBundle\Entity;

use Sonata\UserBundle\Entity\BaseGroup as BaseGroup;

/**
 * Class Group
 * @package UserBundle\Entity
 */
class Group extends BaseGroup
{
    /**
     * @var integer $id
     */
    protected $id;

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