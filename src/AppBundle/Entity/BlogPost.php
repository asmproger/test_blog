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
 * Model for blog posts
 * @ORM\Entity(repositoryClass="AppBundle\Repository\BlogPostRepository")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(name="blog_posts")
 */
class BlogPost
{
    const FILE_PATH = '/var/www/blog/web/uploads/images/';
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $title;

    /**
     * @ORM\Column(type="text")
     */
    private $label;

    /**
     * @return mixed
     */
    public function getLabel()
    {
        return $this->label;
    }

    public function getUrl()
    {
        return $this->label;
    }

    /**
     * @param mixed $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * @ORM\Column(nullable=true)
     */
    private $short;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $body;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $pic;

    /**
     * @return mixed
     */
    public function getPic()
    {
        return $this->pic;
    }

    /**
     * @param mixed $pic
     */
    public function setPic($pic)
    {
        $this->pic = $pic;
    }

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $modified_date;


    /**
     * @ORM\Column(type="datetime", options={"default":"CURRENT_TIMESTAMP"})
     */
    private $created_date;


    /**
     * @ORM\Column(name="`enabled`", type="boolean", options={"default": true})
     */
    private $enabled;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $views_count;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $likes_count;

    /**
     * @return mixed
     */
    public function getHref()
    {
        return $this->href;
    }

    /**
     * @param mixed $href
     */
    public function setHref($href)
    {
        $this->href = $href;
    }

    /**
     * @ORM\Column()
     */
    private $href;

    /**
     * @return mixed
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param mixed $enabled
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }

    /**
     * @return mixed
     */
    public function getViewsCount()
    {
        return $this->views_count;
    }

    /**
     * @param mixed $views_count
     */
    public function setViewsCount($views_count)
    {
        $this->views_count = $views_count;
    }

    /**
     * @return mixed
     */
    public function getLikesCount()
    {
        return $this->likes_count;
    }

    /**
     * @param mixed $likes_count
     */
    public function setLikesCount($likes_count)
    {
        $this->likes_count = $likes_count;
    }

    /**
     * @return mixed
     */
    public function getCreatedDate()
    {
        return $this->created_date;
    }

    /**
     * @param mixed $created_date
     */
    public function setCreatedDate($created_date)
    {
        $this->created_date = $created_date;
    }


    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getShort()
    {
        return $this->short;
    }

    /**
     * @param mixed $short
     */
    public function setShort($short)
    {
        $this->short = $short;
    }

    /**
     * @return mixed
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param mixed $body
     */
    public function setBody($body)
    {
        $this->body = $body;
    }

    /**
     * @return mixed
     */
    public function getModifiedDate()
    {
        return $this->modified_date;
    }

    /**
     * @param mixed $modified_date
     */
    public function setModifiedDate($modified_date)
    {
        $this->modified_date = $modified_date;
    }


    private $file;

    public function setFile(UploadedFile $file)
    {
        $this->file = $file;
    }

    public function getFile()
    {
        return $this->file;
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function generateLabel()
    {
        $label = mb_strtolower($this->getTitle());
        $label = str_replace([' ', '-', '+', '=', '!', '?'], '_', $label);
        $this->setLabel($label);
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function uploadPic()
    {
        if (null === $this->getFile()) {
            return;
        }

        $newName = md5(time()) . '.' . $this->getFile()->guessExtension();

        $this->file->move(
            self::FILE_PATH, $newName
        );

        $this->pic = $newName;
        $this->file = null;
    }

    public function setUpdated()
    {
        $this->setModifiedDate(new \DateTime());
    }
}