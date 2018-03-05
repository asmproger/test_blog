<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Settings class. Store key-value pairs in db
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SettingRepository")
 * @ORM\Table(name="setting")
 * @UniqueEntity("skey")
 */
class Setting
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    private $skey;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $value;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set id
     *
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Get key
     *
     * @return string
     */
    public function getSkey()
    {
        return $this->skey;
    }

    /**
     * Set key
     *
     * @param string $skey
     */
    public function setSkey($skey)
    {
        $this->skey = $skey;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set value
     *
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * Is this new empty setting?
     * @return bool
     */
    public function isNew() {
        return empty($this->getSkey()) && empty($this->getValue());
    }
}

