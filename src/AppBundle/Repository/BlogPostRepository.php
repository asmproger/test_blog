<?php
/**
 * Created by PhpStorm.
 * User: sovkutsan
 * Date: 2/16/18
 * Time: 11:50 AM
 */

namespace AppBundle\Repository;

use AppBundle\Entity\BlogPost;

class BlogPostRepository extends \Doctrine\ORM\EntityRepository
{
    // create new blogpost item from array with valid key=>value params
    public function setFromArray(array $params)
    {
        if (empty($params)) {
            return false;
        }
        $post = new BlogPost();
        $hasDate = false;
        foreach ($params as $k => $v) {
            $k = mb_strtolower($k);
            $method = 'set' . ucfirst($k);

            // invalid data? no problem
            if (!method_exists($post, $method)) {
                continue;
                //throw new \Exception('Invalid data');
            }
            $post->$method($v);
            if ($k == 'created_date') {
                $hasDate = true;
            }
        }
        $post->setCreatedDate(new \DateTime());
        $post->setEnabled(1);
        $post->setHref('http://test_blog.local');
        return $post;
    }
}