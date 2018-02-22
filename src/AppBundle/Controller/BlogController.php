<?php

namespace AppBundle\Controller;

use AppBundle\Entity\BlogPost;
use AppBundle\Entity\Page;
use AppBundle\Service\GoogleParser;
use Doctrine\DBAL\Types\TextType;
use Knp\Component\Pager\Paginator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
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
        $paginator = $p->paginate($query, $request->get('page', 1), 10);
        // replace this example code with whatever you need
        return $this->render('blog/index.html.twig', [
            'paginator' => $paginator,
            'base_dir' => realpath($this->getParameter('kernel.project_dir')) . DIRECTORY_SEPARATOR,
        ]);
    }

    /**
     * @param Request $request
     * #Route("/blog-add", name="blog_add")
     */
    public function addAction(Request $request)
    {
        $post = new BlogPost();
        $form = $this->getForm($post);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            die('FORM OK');
        }
        return $this->render('blog/add.html.twig', [
            'form' => $form
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

    private function getForm(BlogPost $post) {
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
            ]);
        ;
        return $builder->getForm();
    }
}
