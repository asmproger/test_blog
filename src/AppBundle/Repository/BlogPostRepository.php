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

            if (!method_exists($post, $method)) {
                throw new \Exception('Invalid data');
            }
            $post->$method($v);
            if ($k == 'created_date') {
                $hasDate = true;
            }
        }
        $post->setCreatedDate(new \DateTime());
        $post->setEnabled(1);
        return $post;
    }
}