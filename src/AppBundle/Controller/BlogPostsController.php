<?php

namespace AppBundle\Controller;

use AppBundle\Entity\BlogPost;
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
use Symfony\Component\HttpFoundation\Response;

use \FOS\RestBundle\View\View as RestView;



class BlogPostsController extends FOSRestController
{
    /**
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

        /*if($page && $ipp) {
            $p = $this->get('knp_paginator');
            $items = $p->paginate( $query, $page, 10);
        } else {
            $items = $query->getResult();
        }*/

        $items = $query->getResult();
        $view = $this->view(['count' => count($items)], 200);
        return $this->handleView($view);
    }
    /**
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

        if($page && $ipp) {
            $p = $this->get('knp_paginator');
            $items = $p->paginate( $query, $page, 10);
        } else {
            $items = $query->getResult();
        }

        //$items = $query->getResult();

        $p = $this->get('knp_paginator');
        $paginator = $p->paginate( $query, $page, $ipp);
        $view = $this->view($paginator, 200);
        $view->setTemplate('api/blog_post_all.html.twig');
        $view->setTemplateVar('paginator');

        return $this->handleView($view);
    }

    /**
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