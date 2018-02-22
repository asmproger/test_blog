<?php

namespace AppBundle\Controller;

use AppBundle\Entity\BlogPost;
use AppBundle\Entity\Image;
use AppBundle\Entity\Page;
use AppBundle\Utils\CustomMethods;
use AppBundle\Utils\QueryHelper;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sonata\BlockBundle\Block\BlockLoaderChain;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{

    /**
     * @Route("/upload-image", name="rest_blog_upload")
     */
    public function uploadImageAction(Request $request) {
        /**
         * @var BlogPost $post
         */
        $post = new BlogPost();
        $form = $this->getPostForm($post);
        $form->handleRequest($request);

        if($form->isSubmitted()) {
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
            } catch(\Exception $e) {
                $em->getConnection()->rollback();
                throw $e;
            }

            die(get_class($file));
        }


        print_r($_FILES);
        die;
    }

    /**
     * @Route("/rest-blog", name="rest_blog")
     * @param Request $request
     */
    public function blogAction(Request $request)
    {
        /**
         * @var BlogPost $post
         */
        $post = new BlogPost();
        $form = $this->getPostForm($post);

        $form->handleRequest($request);
        if($form->isSubmitted()) {
            if( $form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $token = $form->get('_image_token')->getData();
                $image = $em->getRepository(Image::class)->findOneBy(['token' => $token]);

                $post = $form->getData();
                if($image) {
                    $post->setImage($image);
                    //$image->setToken('');
                }

                $post->setCreatedDate(new \DateTime());
                $post->setEnabled(1);
                $post->setHref('http://test_blog.local');

                $em->getConnection()->beginTransaction();
                try {
                    $em->persist($post);
                    //$em->persist($image);
                    $em->flush();
                    $em->getConnection()->commit();

                    return new JsonResponse(['status' => true]);

                } catch (\Exception $e) {
                    $em->getConnection()->rollback();
                    throw $e;
                }

                die;
            } else {
                $errs = [];
                $errors = $form->getErrors(true, true);
                //print_r($errors->count()); die;
                foreach($errors as $err) {
                    $errs[] = [
                        'element' => $err->getOrigin()->getName(),
                        'error' => $err->getMessage()
                    ];
                }

                return new JsonResponse(['status' => false, 'errs' => $errs]);
            }
        }

        return $this->render('default/rest_blog.html.twig', [
            'form' => $form->createView(),
            'base_dir' => realpath($this->getParameter('kernel.project_dir')) . DIRECTORY_SEPARATOR,
        ]);
    }

    private function getPostForm(BlogPost $post)
    {
        $builder = $this->createFormBuilder($post);
        $builder
            ->setMethod('POST')
            ->add('_image_id', HiddenType::class, [
                'mapped' => false
            ])
            ->add('_image_token', HiddenType::class, [
                'mapped' => false
            ])
            ->add('title', TextType::class, [
                'label' => 'Title'
            ])
            ->add('short', TextareaType::class, [
                'label' => 'Short description'
            ])
            ->add('body', TextareaType::class, [
                'label' => 'Body'
            ])
            ->add('pic', FileType::class, [
                'label' => 'Image:',
                'required' => false,
                'data_class' => null
            ])
            ->add('submit', SubmitType::class, [
                'label' => $post->getId() ? 'Edit' : 'Add'
            ])
        ;
        return $builder->getForm();
    }

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
     * @Route("/test2", name="default_rest")
     * @param Request $request
     */
    public function test2Action(Request $request)
    {
        return $this->render('default/rest.html.twig', []);
    }

    /**
     * @Route("/test", name="default_test")
     */
    public function testAction(Request $request, \Swift_Mailer $mailer, LoggerInterface $logger)
    {
        die('test controller');
        $logger->error('ok');

        $dispatcher = $this->get('event_dispatcher_custom');
        $dispatcher->dispatch('custom_list', new GenericEvent());

        die;
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
