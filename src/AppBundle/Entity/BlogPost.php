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
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Exclude;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\Groups;

use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\JoinColumn;
use JMS\Serializer\Annotation\VirtualProperty;

/**
 * Model for blog posts. VirtualProperty allows us get pic from Image object in json object
 * @ORM\Entity(repositoryClass="AppBundle\Repository\BlogPostRepository")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(name="blog_posts")
 * @ExclusionPolicy("all")
 *
 * @VirtualProperty(
 *     "pic",
 *     exp="object.getPic()"
 * )
 */
class BlogPost
{

    public function __construct()
    {
        $this->created_date = new \DateTime();
    }

    private $_image_token;
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Expose
     * @Groups({"blog_post"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=false)
     * @Groups({"blog_post"})
     * @Expose
     * @Assert\NotBlank()
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     * @Groups({"blog_post"})
     * @Expose()
     */
    private $label;

    /**
     * Get label
     *
     * @return mixed
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Get url (alias to getLabel)
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->label;
    }

    /**
     * Set label
     *
     * @param mixed $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * @var string
     *
     * @ORM\Column(nullable=true)
     * @Assert\Length(max=200, maxMessage="Maximum length exeeded (200)")
     * @Groups({"blog_post"})
     * @Expose()
     */
    private $short;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"blog_post"})
     * @Expose
     * @Assert\NotBlank()
     */
    private $body;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"blog_post"})
     * @Exclude()
     */
    private $pic;

    /**
     * Foreign key?
     *
     * @OneToOne(targetEntity="Image")
     * @JoinColumn(name="image_id", referencedColumnName="id")
     */
    private $image;

    /**
     * Get image
     *
     * @return mixed
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Get image name
     *
     * @return mixed
     */
    public function getImageName()
    {
        /**
         * @var Image $image
         */
        $image = $this->getImage();
        return $image->getPath();
    }

    /**
     * Set image
     *
     * @param mixed $image
     */
    public function setImage($image)
    {
        $this->image = $image;
    }

    /**
     * Get pic
     *
     * @return mixed
     */
    public function getPic()
    {
        $image = $this->getImage();
        //die(gettype($image));
        return !empty($image) ? $image->getPath() : $this->pic;
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
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", options={"default":"CURRENT_TIMESTAMP"})
     */
    private $created_date;


    /**
     * @var boolean
     *
     * @ORM\Column(name="`enabled`", type="boolean", options={"default": true}, nullable=true)
     * @Expose()
     */
    private $enabled;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $views_count;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $likes_count;

    /**
     * Get href
     *
     * @return mixed
     */
    public function getHref()
    {
        return $this->href;
    }

    /**
     * Set href
     *
     * @param mixed $href
     */
    public function setHref($href)
    {
        $this->href = $href;
    }

    /**
     * @var string
     *
     * @ORM\Column(nullable=true)
     * @Assert\Url(message="Url is not valid!", protocols={"http", "https"})
     * @Expose()
     */
    private $href;

    /**
     * Get enabled
     *
     * @return mixed
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * Set enabled
     *
     * @param mixed $enabled
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }

    /**
     * Get views count
     *
     * @return mixed
     */
    public function getViewsCount()
    {
        return $this->views_count;
    }

    /**
     * Set views count
     *
     * @param mixed $views_count
     */
    public function setViewsCount($views_count)
    {
        $this->views_count = $views_count;
    }

    /**
     * Get likes count
     *
     * @return mixed
     */
    public function getLikesCount()
    {
        return $this->likes_count;
    }

    /**
     * Set likes count
     *
     * @param mixed $likes_count
     */
    public function setLikesCount($likes_count)
    {
        $this->likes_count = $likes_count;
    }

    /**
     * Get created date
     *
     * @return mixed
     */
    public function getCreatedDate()
    {
        return $this->created_date;
    }

    /**
     * Set created date
     *
     * @param mixed $created_date
     */
    public function setCreatedDate($created_date)
    {
        $this->created_date = $created_date;
    }


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
        return strlen($this->short) ? $this->short : substr($this->body, 0, 200);
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
     *
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
        $label = str_replace([' ', '-', '+', '=', '!', '?'], '_', $label);
        $this->setLabel($label);
    }

    /**
     * Set updated
     */
    public function setUpdated()
    {
        $this->setModifiedDate(new \DateTime());
    }

    /**
     * Get image token
     *
     * @return mixed
     */
    public function getImageToken()
    {
        return $this->_image_token;
    }

    /**
     * Set image token
     *
     * @param mixed $image_token
     */
    public function setImageToken($image_token)
    {
        $this->_image_token = $image_token;
    }

    /**
     * Set entity fields from array
     *
     * @param array $data
     */
    public function setFromArray($data = [])
    {

        foreach ($data as $k => $v) {
            if (strpos($k, '_') === 0 && strpos($k, 'image') !== false) {
                continue;
            }
            $method = 'set' . ucfirst($k);
            if (method_exists($this, $method)) {
                if ($k != 'id') {
                    $this->$method($v);
                }
            }
        }

    }
}