<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Page;
use AppBundle\Utils\QueryHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')) . DIRECTORY_SEPARATOR,
        ]);
    }

    /**
     * @Route("/test", name="default_test")
     */
    public function testAction(Request $request, \Swift_Mailer $mailer)
    {
        $helper = new QueryHelper($this->getDoctrine(), $this->get('mailer'), $this->get('twig'));
        $helper->execute(null);


        return;
        $message = (new \Swift_Message('Hello Email'))
            ->setFrom('no-reply@test_blog.local')
            ->setTo('asmproger@gmail.com')
            ->setBody(
                $this->renderView(
                    'partials/email_template.html.twig',
                    [
                        'posts_count' => mt_rand(0, 999),
                        'time_cost' => mt_rand(0, 99999)
                    ]
                ),
                'text/html'
            )/*
             * If you also want to include a plaintext version of the message
            ->addPart(
                $this->renderView(
                    'Emails/registration.txt.twig',
                    array('name' => $name)
                ),
                'text/plain'
            )
            */
        ;

        $res = $mailer->send($message);
        die('result - _' . $res . '_');
        // replace this example code with whatever you need
        return $this->render('default/test.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')) . DIRECTORY_SEPARATOR,
        ]);
    }

    /**
     * @Route("/page/{url}", name="default_page")
     */
    public function pageAction(Request $request)
    {
        $url = $request->get('url', '');
        if (!$url) {
            return $this->redirectToRoute('homepage');
        }

        $em = $this->getDoctrine()->getManager();
        $builder = $em->getRepository(Page::class)->createQueryBuilder('p');
        $builder
            ->where('p.label = :label')
            ->setParameter('label', $url);
        $query = $builder->getQuery();
        $product = $query->getResult();

        if (!empty($product)) {
            $product = $product[0];
        } else {
            $product = false;
        }

        return $this->render('default/page.html.twig', [
            'product' => $product
        ]);

        die('ok');
    }
}
