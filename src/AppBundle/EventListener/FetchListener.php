<?php
/**
 * Created by PhpStorm.
 * User: sovkutsan
 * Date: 2/19/18
 * Time: 12:11 PM
 */

namespace AppBundle\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Event listener. Triggered after custom fetch task end works. Send repost email to site admin.
 * Class FetchListener
 * @package AppBundle\EventListener
 */
class FetchListener
{
    private $mailer;
    private $twig;
    private $logger;

    private $robot_email;
    private $admin_email;

    /**
     * mailer for sending email, twig for rendering email template, logger - for testing
     * FetchListener constructor.
     * @param \Swift_Mailer $mailer
     * @param \Twig_Environment $twig
     * @param LoggerInterface $logger
     */
    public function __construct(\Swift_Mailer $mailer, \Twig_Environment $twig, LoggerInterface $logger, $robot_email, $admin_email)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->logger = $logger;
        $this->robot_email = $robot_email;
        $this->admin_email = $admin_email;
    }

    /**
     * Main method. All the logic here.
     * @param GenericEvent $event
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
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
            ->setFrom($this->robot_email)
            ->setTo($this->admin_email)
            ->setBody(
                $this->twig->render('partials/email_template.html.twig', ['report' => $params]),
                'text/html'
            );
        $this->mailer->send($message);
    }
}