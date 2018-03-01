<?php

namespace AppBundle\Controller;

use AppBundle\Entity\BlogPost;
use AppBundle\Entity\Page;
use AppBundle\Utils\CustomMethods;
use Knp\Component\Pager\Paginator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\QueryBuilder;

class BlogController extends Controller
{
    /**
     * @Route("/blog", name="blog_index")
     */
    public function indexAction(Request $request)
    {
        /*$t = new GoogleParser($this->getDoctrine());
        $t->setQuery('sibers');
        $t->parse();
        $row = $t->getRow();

        print_r($row);
        die('ok');*/

        /**
         * @var QueryBuilder $builder
         */
        $builder = $this->getDoctrine()->getManager()->getRepository(BlogPost::class)->createQueryBuilder('bp');
        $builder
            ->where('bp.enabled = 1')
            ->orderBy('bp.id', 'DESC');
        $query = $builder->getQuery();

        $p = $this->get('knp_paginator');
        $paginator = $p->paginate( $query, $request->get('page', 1), 10);
        // replace this example code with whatever you need
        return $this->render('blog/index.html.twig', [
            'paginator' => $paginator,
            'base_dir' => realpath($this->getParameter('kernel.project_dir')) . DIRECTORY_SEPARATOR,
        ]);
    }

    /**
     * @Route("/blog-post/{id}", name="blog_view", requirements={"id"="\d+"})
     */
    public function pageAction(Request $request)
    {
        $id = (int)$request->get('id', 0);
        if (!$id) {
            return $this->redirectToRoute('blog_index');
        }

        $em = $this->getDoctrine()->getManager();
        $item = $em->getRepository(BlogPost::class)->find($id);

        return $this->render('blog/view.html.twig', [
            'item' => $item
        ]);
    }

    /**
     * @param Request $request
     * @Route("/blog-add", name="blog_add")
     */
    public function addAction(Request $request/*, CustomUploader $cU*/)
    {
        /**
         * @var BlogPost $post
         */
        $post = null;
        $method = 'POST';
        $id = $request->request->get('id', 0);
        if($id) {
            $post = $this->getDoctrine()->getRepository(BlogPost::class)->find($id);
            $method = 'PUT';
        }
        if(!$post) {
            $post = new BlogPost();
            $method = 'POST';
        }

        $form = $this->getForm($post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $post = $form->getData();

            $file = $post->getPic();
            if ($file) {
                $newName = md5(time()) . '.' . $file->guessExtension();
                $file->move($this->getParameter('images_directory'), $newName);
                $product = $form->getData();
                $product->setPic($newName);
            }

            $em->getConnection()->beginTransaction();
            try {

                $post->setCreatedDate(new \DateTime());
                $post->setEnabled(1);
                $post->setHref('http://test_blog.local');
                $em->persist($post);
                $em->flush();
                $em->getConnection()->commit();
            } catch(\Exception $e) {
                $em->getConnection()->rollBack();
                throw $e;
            }

            return $this->redirectToRoute('blog_index');
        }
        return $this->render('blog/add.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @param Request $request
     * @route("blog-edit/{id}", name="blog_edit", requirements={"id"="\d+"})
     */
    public function editAction(Request $request) {
        $id = (int)$request->get('id', 0);

        $post = $this->getDoctrine()->getRepository(BlogPost::class)->find($id);

        if(!$post) {
            throw new \Exception('Post not found');
        }

        $form = $this->getForm($post);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $file = $post->getPic();
            if($file) {
                $newName = md5(time()) . '.' . $file->guessExtension();
                $file->move($this->getParameter('images_directory'), $newName);
                $post = $form->getData();
                $post->setPic($newName);
            }

            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            try {
                $em->persist($post);
                $em->flush();
                $em->getConnection()->commit();
            } catch (\Exception $e) {
                $em->getConnection()->rollback();
                throw $e;
            }

        }

        return $this->render('blog/add.html.twig', [
            'form' => $form->createView()
        ]);
    }

    private function getForm(BlogPost $post)
    {
        $builder = $this->createFormBuilder($post);
        $builder
            ->add('title', TextType::class, [
                'label' => 'Title',
                'required' => 1
            ])
            ->add('short', TextareaType::class, [
                'label' => 'Short description',
                'required' => 1
            ])
            ->add('body', TextareaType::class, [
                'label' => 'Description',
                'required' => 1
            ])
            ->add('pic', FileType::class, [
                'label' => 'Image:',
                'required' => false,
                'data_class' => null
            ])
            ->add('submit', SubmitType::class, [
                'label' => $post->getId() ? 'Edit' : 'Add',
            ]);;
        return $builder->getForm();
    }
}
