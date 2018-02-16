<?php

namespace AppBundle\Controller;

use AppBundle\Entity\BlogPost;
use AppBundle\Entity\Page;
use AppBundle\Service\GoogleParser;
use Knp\Component\Pager\Paginator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\QueryBuilder;

class BlogController extends Controller
{
    /**
     * @Route("/blog", name="blog_index")
     */
    public function indexAction(Request $request)
    {
        /**
         * @var QueryBuilder $builder
         */
        $builder = $this->getDoctrine()->getManager()->getRepository(BlogPost::class)->createQueryBuilder('bp');
        $builder
            ->where('bp.enabled = 1')
            ->orderBy('bp.id', 'DESC');
        $query = $builder->getQuery();

        $p = $this->get('knp_paginator');
        $paginator = $p->paginate( $query, $request->get('page', 1), 5);

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
}
