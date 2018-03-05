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
use Doctrine\Common\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Class for fetching result by GoogleParser and storing it ot the database
 * Class QueryHelper
 * @package AppBundle\Utils
 */
class QueryHelper
{
    private $doctrine;
    private $mailer;
    private $twig;
    private $logger;

    /**
     * Doctrine for db, mailer for report email, twig for email template rendering
     * QueryHelper constructor.
     * @param ManagerRegistry $doctrine
     * @param \Swift_Mailer $mailer
     * @param \Twig_Environment $twig
     */
    public function __construct(ManagerRegistry $doctrine, \Swift_Mailer $mailer, \Twig_Environment $twig, LoggerInterface $logger, EventDispatcher $d)
    {
        $this->doctrine = $doctrine;
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->logger = $logger;
        $this->e = $d;
    }

    private $e;

    /**
     * QueryHelper main method. All the logic here.
     */
    public function execute()
    {
        // lets get settings from db
        $query = trim($this->doctrine->getRepository(Setting::class)->getSetting('parser_task_query', ''));
        $limit = (int)$this->doctrine->getRepository(Setting::class)->getSetting('parser_task_count', 5);

        if (!$query) {
            return;
        }
        $query = explode(',', $query);

        // report data
        $report = [
            'postCollected' => 0,
            'time' => 0
        ];

        $startTime = time();
        $totalRows = [];

        // our parser
        $parser = new GoogleParser($this->doctrine);
        foreach ($query as $key) {

            // parser settings: query string, posts limit
            $parser->setQuery($key);
            $parser->setLimit($limit);

            // lets parse search result
            $parser->parse();

            // here is our  result
            $rows = $parser->getRows();

            // lets update report!
            if (count($rows) < $limit) {
                $cnt = count($rows);
                $report['problems'][] = "'{$key}' keyword problem: not enough posts fetched, {$cnt} instead of {$limit}. timestamp - " . time();
            }

            // all posts
            $totalRows = array_merge($totalRows, $rows);
        }

        $em = $this->doctrine->getManager();
        $em->getConnection()->beginTransaction();
        try {
            foreach ($totalRows as $row) {
                $post = $this->doctrine->getRepository(BlogPost::class)->setFromArray($row);
                $em->persist($post);
            }
            $em->flush();
            $em->getConnection()->commit();
            // everything is ok? lets update report and time for this task!
            $report['postCollected'] = count($totalRows);
            $report['time'] = time() - $startTime;
            $this->doctrine->getRepository(Setting::class)->setSetting('parser_task_last_update', time());
        } catch (\Exception $e) {
            // report for failed result
            $report['postCollected'] = 0;
            $report['time'] = 0;
            $report['problems'][] = 'Exception: ' . $e->getMessage();
            $em->getConnection()->rollBack();
        }

        $event = new GenericEvent(function () {}, $report);
        $this->e->dispatch('custom_fetch', $event);
    }
}