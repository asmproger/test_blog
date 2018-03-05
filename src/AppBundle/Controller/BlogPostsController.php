<?php

namespace AppBundle\Controller;

use AppBundle\Entity\BlogPost;
use AppBundle\Entity\Image;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\QueryBuilder;

use Symfony\Component\HttpFoundation\Response;

/**
 * REST API class for blogposts
 * Class BlogPostsController
 * @package AppBundle\Controller
 */
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
     * one action for create & edit blogpost (put & post request)
     * @param Request $request
     * @Route("api/v1/blog.{_format}", name="api_post_action", methods={"POST", "PUT"}, defaults={"_format"="json"})
     */
    public function actionAction(Request $request)
    {
        /**
         * @var BlogPost $post
         * @var \Symfony\Component\Validator\ConstraintViolation $err
         * @var \Symfony\Component\Validator\ConstraintViolationList $errors
         * @var \Symfony\Component\Validator\Validator\TraceableValidator $validator
         */
        $data = $request->request->all();
        $post = new BlogPost();

        if($request->isMethod('PUT')) { // post edition
            $id = isset($data['id']) ? $data['id'] : 0;
            $post = ($id) ? $this->getDoctrine()->getRepository(BlogPost::class)->find($id) : null;
            if (!$id || !$post) {
                return new JsonResponse(['status' => false, 'code' => 404, 'message' => 'Post not found'], 404);
            }
        }

        $post->setFromArray($data);
        // let's link image to this post
        if (isset($data['_image_id']) && !empty($data['_image_id'])) {
            $image = $this->getDoctrine()->getRepository(Image::class)->find($data['_image_id']);
            if ($image) {
                $post->setImage($image);
            }
        }
        // if there is path to image, let's remove current image_id
        if (isset($data['pic']) && !empty($data['pic'])) {
            $post->setImage(null);
        }


        $validator = $this->get('validator');
        $errors = $validator->validate($post);
        $errs = [];
        //we have some errors?
        if (count($errors)) {
            foreach ($errors->getIterator() as $err) {
                $errs[] = [ // let's collect it!
                    'name' => $err->getPropertyPath(),
                    'error' => $err->getMessage()
                ];
            }
            return new JsonResponse(['status' => false, 'message' => 'Invalid data', 'errors' => $errs], 400);
        }

        //everything is ok
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try {
            $em->persist($post);
            $em->flush();
            $em->getConnection()->commit();
            return new JsonResponse(['status' => true, 'message' => 'OK'], 200);
        } catch (\Exception $e) {
            $em->getConnection()->rollback();
            return new JsonResponse(['status' => false, 'message' => 'Server error', 'message' => $e->getMessage()], 500);
        }
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