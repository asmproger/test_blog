<?php

namespace AppBundle\Controller;

use AppBundle\Entity\BlogPost;
use AppBundle\Entity\Image;
use AppBundle\Entity\Page;
use AppBundle\Form\BlogPostType;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DefaultController
 * @package AppBundle\Controller
 */
class DefaultController extends Controller
{

    /**
     * Main page of blog angular version
     * @Route("/blog-angular", name="blog_angular")
     * @param Request $request
     */
    public function blogaAction(Request $request)
    {
        return $this->render('default/angular_blog.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')) . DIRECTORY_SEPARATOR,
        ]);
    }

    /**
     * Upload photo for angular blog version
     * @Route("/upload-angular", name="upload_angular")
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function uploadAngularAction(Request $request)
    {
        /**
         * @var BlogPost $post
         */
        $response = ['status' => false];
        if (!empty($_FILES) && isset($_FILES['file'])) {
            $name = $_FILES['file']['name'];
            $ext = explode('.', $name);
            if (count($ext) == 2) {
                $ext = $ext[1];
            } else {
                $ext = '';
            }
            $newName = md5(time() . $_FILES['file']['name']) . '.' . $ext;

            if (copy($_FILES['file']['tmp_name'], $this->getParameter('images_directory') . '/' . $newName)) {
                $response['status'] = true;
                $response['file'] = $newName;
            }
            return new JsonResponse($response, 200);
        }
        return new JsonResponse($response, 400);
    }

    /**
     * uploads image and creates new Image row in table. return image_id, image name & token
     * @Route("/upload-image", name="rest_blog_upload")
     */
    public function uploadImageAction(Request $request)
    {
        /**
         * @var BlogPost $post
         */
        $post = new BlogPost();
        $form = $this->createForm(BlogPostType::class, $post, ['label' => $post->getId() ? 'Edit' : 'Add']);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            /**
             * @var UploadedFile $file
             */
            $file = $post->getPic();
            $newName = md5(time() . $file->getBasename()) . '.' . $file->guessExtension();
            $file->move($this->getParameter('images_directory'), $newName);

            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            try {
                $token = md5(time() . $newName);
                $image = new Image();
                $image->setPath($newName);
                $image->setToken($token);

                $em->persist($image);
                $em->flush();
                $em->getConnection()->commit();

                return new JsonResponse([
                    'status' => true,
                    'data' => [
                        'token' => $token,
                        'id' => $image->getId(),
                        'image' => $newName
                    ]
                ], 200);
            } catch (\Exception $e) {
                $em->getConnection()->rollback();
                return new JsonResponse(['status' => false]);
            }
        }
        return new JsonResponse(['status' => false]);
    }

    /**
     * Main page of blog ajax version. Using rest as a backend
     * @Route("/rest-blog", name="rest_blog")
     * @param Request $request
     */
    public function blogAction(Request $request, SerializerInterface $serializer)
    {
        /**
         * @var BlogPost $post
         */
        $post = new BlogPost();
        $form = $this->createForm(BlogPostType::class, $post, ['method' => 'POST']);

        return $this->render('default/rest_blog.html.twig', [
            'form' => $form->createView(),
            'base_dir' => realpath($this->getParameter('kernel.project_dir')) . DIRECTORY_SEPARATOR,
        ]);
    }

    /**
     * Standart symfony homepage
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')) . DIRECTORY_SEPARATOR,
        ]);
    }


    /**
     * Test controller, playing with DQL
     * Commented blocks - examples
     * @Route("/test", name="default_test")
     */
    public function testAction(Request $request, \Swift_Mailer $mailer, LoggerInterface $logger)
    {
        $em = $this->getDoctrine()->getManager();

        // disabled items
        /*$query = $em
            ->createQuery('SELECT p FROM AppBundle:BlogPost p WHERE p.enabled = :enabled ORDER BY p.id ASC')
            ->setParameter('enabled', 0);*/

        // enabled items
        /*$query = $em
            ->createQuery('SELECT p FROM AppBundle:BlogPost p WHERE p.enabled = :enabled ORDER BY p.id ASC')
            ->setParameter('enabled', 1);*/

        // with Image pics
        $query = $em
            ->createQuery('SELECT DISTINCT bp FROM AppBundle:BlogPost bp JOIN bp.image i')
        ;
        // with field pics
        $query = $em
            ->createQuery('SELECT DISTINCT bp FROM AppBundle:BlogPost bp where bp.image is null')
            //->createQuery('SELECT DISTINCT bp, i FROM AppBundle:BlogPost bp JOIN bp.image i')
            //->createQuery('SELECT DISTINCT u.id FROM CmsArticle a JOIN a.user u')
        ;


        $items = $query->getResult();

        return $this->render('default/test.html.twig', [
            'items' => $items
        ]);
    }

    /**
     * Display pseudo static page (About, Contacts, etc.)
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
        $page = $query->getResult();

        if (!empty($page)) {
            $page = $page[0];
        } else {
            $page = false;
        }

        return $this->render('default/page.html.twig', [
            'product' => $page
        ]);
    }
}