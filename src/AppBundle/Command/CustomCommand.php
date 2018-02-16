<?php
/**
 * Created by PhpStorm.
 * User: sovkutsan
 * Date: 2/16/18
 * Time: 11:02 AM
 */

namespace AppBundle\Command;

use AppBundle\Entity\BlogPost;
use AppBundle\Repository\BlogPostRepository;
use AppBundle\Service\GoogleParser;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
        $query = $input->getArgument('query');
        if (empty($query)) {
            //$query = 'scarlett johansson';
            $query = 'sibers';
        }
        $output->writeln('Executing...');
        $output->writeln("Query string is '{$query}'");

        $this->parser->setQuery($query);
        $this->parser->parse();
        try {
            $row = $this->parser->getRow();
        } catch (\Exception $e) {
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
            $output->writeln('ok');
        } catch (\Exception $e) {
            $output->writeln('wrong! ' . $e->getMessage());
            $em->getConnection()->rollBack();
        }

    }
}