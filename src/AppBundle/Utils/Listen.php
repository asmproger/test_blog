<?php
/**
 * Created by PhpStorm.
 * User: sovkutsan
 * Date: 2/19/18
 * Time: 3:11 PM
 */

namespace AppBundle\Utils;


use Psr\Log\LoggerInterface;

class Listen
{
    private $logger;
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function trig($event) {
        $this->logger->error('Custom event');
    }

}