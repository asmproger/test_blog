<?php

namespace AppBundle\Controller;

use AppBundle\Entity\BlogPost;
use AppBundle\Entity\Image;
use AppBundle\Utils\CustomMethods;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\UserBundle\Form\Type\ResettingFormType;
use Knp\Component\Pager\Paginator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\QueryBuilder;

use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use Symfony\Component\HttpFoundation\Response;

use \FOS\RestBundle\View\View as RestView;
use Symfony\Component\Serializer\SerializerInterface;

class BlogPostsController extends FOSRestController
{
    /**
     * create new blogpost item
     * @POST("/blog", name="blog_post")
     */
    public function postBlogAction(Request $request)
    {
        $data = $request->request->all();
        $em = $this->getDoctrine()->getManager();

        $image = $em->getRepository(Image::class)->find($data['_image_id']);

        $post = $em->getRepository(BlogPost::class)->setFromArray($data);

        $post->setImage($image);

        $em->persist($post);
        $em->flush();

        return new JsonResponse([], 200);
    }

    /**
     * return enabled blogposts count in db
     * @param int $page
     * @param int $ipp
     * @return \Symfony\Component\HttpFoundation\Response
     * @return Response
     * @Get("/blogs-count")
     */
    public function getBlogsCountAction($page = 0, $ipp = 10)
    {
        /**
         * @var QueryBuilder $builder
         */
        $builder = $this->getDoctrine()->getManager()->getRepository(BlogPost::class)->createQueryBuilder('bp');
        $builder
            ->where('bp.enabled = 1')
            ->orderBy('bp.id', 'DESC');
        $query = $builder->getQuery();

        $items = $query->getResult();
        $view = $this->view(['count' => count($items)], 200);
        return $this->handleView($view);
    }

    /**
     * return all enabled blogposts from db
     * @param int $page
     * @param int $ipp
     * @return \Symfony\Component\HttpFoundation\Response
     * @return Response
     * @Get("/blogs/{page}/{ipp}")
     */
    public function getBlogAllAction($page = 0, $ipp = 10)
    {
        /**
         * @var QueryBuilder $builder
         */
        $builder = $this->getDoctrine()->getManager()->getRepository(BlogPost::class)->createQueryBuilder('bp');
        $builder
            ->where('bp.enabled = 1')
            ->orderBy('bp.id', 'DESC');
        $query = $builder->getQuery();

        if ($page && $ipp) {
            $p = $this->get('knp_paginator');
            $items = $p->paginate($query, $page, 10);
        } else {
            $items = $query->getResult();
        }

        //$items = $query->getResult();

        $p = $this->get('knp_paginator');
        $paginator = $p->paginate($query, $page, $ipp);
        $view = $this->view($paginator, 200);
        $view->setTemplate('api/blog_post_all.html.twig');
        $view->setTemplateVar('paginator');

        return $this->handleView($view);
    }

    /**
     * return one blogpost from db
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    //* @throws \Exception
    public function getBlogAction($id)
    {
        $item = $this->getDoctrine()->getRepository(BlogPost::class)->find($id);
        if (!$item instanceof BlogPost) {
            throw new \Exception('Post not found');
        }
        $view = $this->view($item, 200);
        $view->setTemplate('api/blog_post.html.twig');
        $view->setTemplateVar('item');
        return $this->handleView($view);
    }
}