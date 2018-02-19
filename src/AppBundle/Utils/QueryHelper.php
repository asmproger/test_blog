<?php
/**
 * Created by PhpStorm.
 * User: sovkutsan
 * Date: 2/19/18
 * Time: 9:36 AM
 */

namespace AppBundle\Utils;

use AppBundle\Entity\Setting;
use AppBundle\Entity\BlogPost;
//use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Console\Output\OutputInterface;

class QueryHelper
{
    private $doctrine;
    private $mailer;
    private $twig;

    public function __construct(ManagerRegistry $doctrine, \Swift_Mailer $mailer, \Twig_Environment $twig)
    {
        $this->doctrine = $doctrine;
        $this->mailer = $mailer;
        $this->twig = $twig;
    }

    public function execute(/*OutputInterface*/ $output)
    {
        //$output->writeln('helper');
        $query = trim($this->doctrine->getRepository(Setting::class)->getSetting('parser_task_query', ''));
        $limit = (int)$this->doctrine->getRepository(Setting::class)->getSetting('parser_task_count', 5);

        if (!$query) {
            //$output->writeln('Empty query.');
            return;
        }
        $query = explode(',', $query);

        $report = [
            'postCollected' => 0,
            'time' => 0
        ];
        $startTime = time();
        $totalRows = [];
        $parser = new GoogleParser($this->doctrine);
        foreach ($query as $key) {
            $parser->setQuery($key);
            $parser->setLimit($limit);
            $parser->parse();
            $rows = $parser->getRows();
            if (count($rows) < $limit) {
                $cnt = count($rows);
                $report['problems'][] = "'{$key}' keyword problem: not enough posts fetched, {$cnt} instead of {$limit}. timestamp - " . time();
            }
            $totalRows = array_merge($totalRows, $rows);
        }
        /*CustomMethods::print_arr($query);
        CustomMethods::print_arr($report);
        CustomMethods::print_die($totalRows);*/

        $em = $this->doctrine->getManager();
        $em->getConnection()->beginTransaction();
        try {
            foreach ($totalRows as $row) {
                $post = $this->doctrine->getRepository(BlogPost::class)->setFromArray($row);
                $em->persist($post);
            }
            $em->flush();
            $em->getConnection()->commit();
            // everything is ok? lets update time for this task!
            $report['postCollected'] = count($totalRows);
            $report['time'] = time() - $startTime;
            $this->doctrine->getRepository(Setting::class)->setSetting('parser_task_last_update', time());
        } catch (\Exception $e) {
            $report['postCollected'] = 0;
            $report['time'] = 0;
            $report['problems'][] = 'Exception: ' . $e->getMessage();
            $em->getConnection()->rollBack();
        }

        $message = (new \Swift_Message('Hello Email'))
            ->setFrom('no-reply@test_blog.local')
            ->setTo('asmproger@gmail.com')
            ->setBody(
                $this->twig->render('partials/email_template.html.twig', ['report' => $report]),
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
        $result = $this->mailer->send($message);
        CustomMethods::print_arr($result);
        CustomMethods::print_die($report);
    }
}