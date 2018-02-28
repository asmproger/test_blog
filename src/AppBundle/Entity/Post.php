<?php
/**
 * Created by PhpStorm.
 * User: sovkutsan
 * Date: 2/28/18
 * Time: 10:53 AM
 */
namespace AppBundle\Entity;
/**
 * @ORM\Entity()
 */
class Post {
    private $title;
    private $text;

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
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param mixed $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }
}