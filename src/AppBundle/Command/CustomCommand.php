<?php
/**
 * Created by PhpStorm.
 * User: sovkutsan
 * Date: 2/16/18
 * Time: 11:02 AM
 */

namespace AppBundle\Command;

use AppBundle\Entity\BlogPost;
use AppBundle\Entity\Setting;
use AppBundle\Repository\BlogPostRepository;
use AppBundle\Service\GoogleParser;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This is custom command for Symfony console.
 * Fetch one row from Google search result(according to settings) and store it into DB
 *
 * Class CustomCommand
 * @package AppBundle\Command
 */
class CustomCommand extends Command
{
    private $parser;
    private $doctrine;

    /**
     * CustomCommand constructor.
     */
    public function __construct(Registry $doctrine)
    {
        $this->parser = new GoogleParser($doctrine);
        $this->doctrine = $doctrine;
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
        $query = $this->doctrine->getRepository(Setting::class)->getSetting(
            'parser_task_query', $input->getArgument('query')
        );
        $period = (int)$this->doctrine->getRepository(Setting::class)->getSetting('parser_task_period', 10);
        $lastUpdate = (int)$this->doctrine->getRepository(Setting::class)->getSetting('parser_task_last_update', 0);

        $period *= 60;

        // is it showtime?
        if( (time() - $lastUpdate) < $period) {
            $output->writeln('It\'s not time for this');
            return;
        }

        // default query, if there is no setting in db, and no input
        if (empty($query)) {
            //$query = 'scarlett johansson';
            $query = 'sibers';
        }
        $output->writeln([
            'query - ' => $query,
            'period - ' => $period,
            'lastUpdate - ' => $lastUpdate
        ]);

        $output->writeln('Executing...');
        $output->writeln("Query string is '{$query}'");

        // work with parser class
        $this->parser->setQuery($query);
        $this->parser->parse();
        try {
            $row = $this->parser->getRow();
        } catch (\Exception $e) {
            // something went wrong
            // may be send letter to admin?
            return;
        }


        $output->writeln('----------------------');
        $output->writeln($row);
        $output->writeln('----------------------');

        $em = $this->doctrine->getManager();
        $em->getConnection()->beginTransaction();
        try {
            $post = $this->doctrine->getRepository(BlogPost::class)->setFromArray($row);
            $em->persist($post);
            $em->flush();
            $em->getConnection()->commit();
            // everything is ok? lets update time for this task!
            $this->doctrine->getRepository(Setting::class)->setSetting('parser_task_last_update', time());
        } catch (\Exception $e) {
            $em->getConnection()->rollBack();
        }

    }
}