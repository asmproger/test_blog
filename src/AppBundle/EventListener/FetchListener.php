<?php
/**
 * Created by PhpStorm.
 * User: sovkutsan
 * Date: 2/19/18
 * Time: 12:11 PM
 */

namespace AppBundle\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * Event listener. Triggered after custom fetch task end works
 * Class FetchListener
 * @package AppBundle\EventListener
 */
class FetchListener
{
    private $mailer;
    private $twig;
    private $logger;

    /**
     * mailer for sending email, twig for rendering email template, logger - for testing
     * FetchListener constructor.
     * @param \Swift_Mailer $mailer
     * @param \Twig_Environment $twig
     * @param LoggerInterface $logger
     */
    public function __construct(\Swift_Mailer $mailer, \Twig_Environment $twig, LoggerInterface $logger)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->logger = $logger;
    }

    public function onFetch(GenericEvent $event)
    {
        // lets fetch our report from evet
        $params = $event->getArguments();
        if (empty($params)) {
            // no data? lets inform admin about the error
            $params = [
                'postCollected' => 0,
                'time' => 0,
                'problems' => ['Invalid listener arguments']
            ];
        }

        // email sending code
        $message = (new \Swift_Message('Hello Email'))
            ->setFrom('no-reply@test_blog.local')
            ->setTo('asmproger@gmail.com')
            ->setBody(
                $this->twig->render('partials/email_template.html.twig', ['report' => $params]),
                'text/html'
            );
        $this->mailer->send($message);
    }
}