<?php
/**
 * Created by PhpStorm.
 * User: sovkutsan
 * Date: 2/15/18
 * Time: 12:27 PM
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(name="blog_page")
 */
class Page
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $label;

    /**
     * Get label
     * @return mixed
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl() {
        return $this->label;
    }

    /**
     * Set label
     * @param mixed $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * @var string
     * @ORM\Column(nullable=true)
     */
    private $short;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $body;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $pic;

    /**
     * Get pic
     *
     * @return mixed
     */
    public function getPic()
    {
        return $this->pic;
    }

    /**
     * Set pic
     *
     * @param mixed $pic
     */
    public function setPic($pic)
    {
        $this->pic = $pic;
    }

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $modified_date;

    /**
     * Get id
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set id
     *
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Get title
     *
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set title
     *
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Get short
     *
     * @return mixed
     */
    public function getShort()
    {
        return $this->short;
    }

    /**
     * Set short
     *
     * @param mixed $short
     */
    public function setShort($short)
    {
        $this->short = $short;
    }

    /**
     * Get body
     *
     * @return mixed
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Set body
     *
     * @param mixed $body
     */
    public function setBody($body)
    {
        $this->body = $body;
    }

    /**
     * Get modified date
     *
     * @return mixed
     */
    public function getModifiedDate()
    {
        return $this->modified_date;
    }

    /**
     * Set modified date
     *
     * @param mixed $modified_date
     */
    public function setModifiedDate($modified_date)
    {
        $this->modified_date = $modified_date;
    }

    /**
     * @var UploadedFile
     */
    private $file;

    /**
     * Set file
     *
     * @param UploadedFile $file
     */
    public function setFile(UploadedFile $file)
    {
        $this->file = $file;
    }

    /**
     * Get file
     * @return UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Generate label
     *
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function generateLabel()
    {
        $label = mb_strtolower($this->getTitle());
        $label = str_replace([' ', '-', '+', '=', '!', '?' ], '_', $label);
        $this->setLabel($label);
    }

    /**
     * Set updated
     */
    public function setUpdated()
    {
        $this->setModifiedDate(new \DateTime());
    }
}