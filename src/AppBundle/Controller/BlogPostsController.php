<?php

namespace AppBundle\Controller;

use AppBundle\Entity\BlogPost;
use AppBundle\Entity\Image;
use AppBundle\Utils\CustomMethods;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\UserBundle\Form\Type\ResettingFormType;
use Knp\Component\Pager\Paginator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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
     * delete blog post from db
     * @param $id
     */
    public function deleteBlogAction($id)
    {
        $post = $this->getDoctrine()->getRepository(BlogPost::class)->find($id);

        if (!$id || !$post) {
            return new JsonResponse(['status' => false, 'code' => 404, 'message' => 'Post not found'], 404);
        }

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try {
            $em->remove($post);
            $em->flush();
            $em->getConnection()->commit();
        } catch (\Exception $e) {
            $em->getConnection()->rollback();
            return new JsonResponse(['status' => false, 'code' => 500, 'message' => $e->getMessage()], 500);
        }
        return new JsonResponse(['status' => true, 'code' => 200, 'message' => 'ok'], 200);
    }

    /**
     * update existing blogpost item
     */
    public function putBlogAction(Request $request)
    {
        $data = $request->request->all();

        $id = $data['id'];
        $post = $this->getDoctrine()->getRepository(BlogPost::class)->find($id);

        if (!$id || !$post) {
            return new JsonResponse(['status' => false, 'code' => 404, 'message' => 'Post not found'], 404);
        }

        $em = $this->getDoctrine()->getManager();

        $post->setFromArray($data);

        $image_id = isset($data['_image_id']) ? $data['_image_id'] : 0;
        if ($image_id) {
            $image = $em->getRepository(Image::class)->find($data['_image_id']);
            $post->setImage($image);
        }

        $em->getConnection()->beginTransaction();
        try {
            $em->persist($post);
            $em->flush();
            $em->getConnection()->commit();
        } catch (\Exception $e) {
            $em->getConnection()->rollback();
            return new JsonResponse(['status' => false, 'code' => 500, 'message' => $e->getMessage()], 500);
        }

        return new JsonResponse(['status' => true, 'code' => 200, 'message' => 'ok'], 200);
    }

    /**
     * create new blogpost item or update exist
     */
    public function postBlogAction(Request $request)
    {
        $data = $request->request->all();

        $em = $this->getDoctrine()->getManager();

        $post = $em->getRepository(BlogPost::class)->setFromArray($data);

        $image_id = isset($data['_image_id']) ? $data['_image_id'] : 0;
        if ($image_id) {
            $image = $em->getRepository(Image::class)->find($data['_image_id']);
            $post->setImage($image);
        }

        $em->getConnection()->beginTransaction();
        try {
            $em->persist($post);
            $em->flush();
            $em->getConnection()->commit();
        } catch (\Exception $e) {
            $em->getConnection()->rollback();
            return new JsonResponse(['status' => false, 'code' => 500, 'message' => $e->getMessage()], 500);
        }

        return new JsonResponse(['status' => true, 'code' => 200, 'message' => 'ok'], 200);
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