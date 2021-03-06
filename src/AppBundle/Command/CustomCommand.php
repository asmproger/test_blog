<?php
/**
 * Created by PhpStorm.
 * User: sovkutsan
 * Date: 2/16/18
 * Time: 11:02 AM
 */

namespace AppBundle\Command;

use AppBundle\Entity\Setting;
use AppBundle\Utils\QueryHelper;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * This is custom command for Symfony console.
 * Fetch one row from Google search result(according to settings) and store it into DB
 *
 * Class CustomCommand
 * @package AppBundle\Command
 */
class CustomCommand extends Command
{
    private $helper;
    private $doctrine;
    private $logger;
private $e;
    /**
     * CustomCommand constructor.
     */
    public function __construct(Registry $doctrine, \Swift_Mailer $mailer, \Twig_Environment $twig, LoggerInterface $logger, $e)
    {
        $this->doctrine = $doctrine;
        $this->helper = new QueryHelper($doctrine, $mailer, $twig, $logger, $e);
        $this->logger = $logger;
        $this->e = $e;
        parent::__construct();
    }

    public function configure()
    {
        $this
            ->setName('custom:query')
            ->setDescription('Query to google')
            ->setHelp('Search query to google')
            ->addArgument('query', null, 'Query string to search engine', null);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        //fetching settings from DB or input
        /*$query = $this->doctrine->getRepository(Setting::class)->getSetting(
            'parser_task_query', $input->getArgument('query')
        );*/
        $period = (int)$this->doctrine->getRepository(Setting::class)->getSetting('parser_task_period', 10);
        $lastUpdate = (int)$this->doctrine->getRepository(Setting::class)->getSetting('parser_task_last_update', 0);

        $period *= 60;

        // is it showtime?
        if( (time() - $lastUpdate) < $period) {
            $output->writeln('It\'s not time for this');
            //return;
        }

        $output->writeln([
            'period - ' => $period,
            'lastUpdate - ' => $lastUpdate
        ]);

        $output->writeln('Executing...');
        $this->helper->execute($output);
    }
}