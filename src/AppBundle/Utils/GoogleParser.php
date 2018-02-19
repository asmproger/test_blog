<?php
/**
 * Created by PhpStorm.
 * User: sovkutsan
 * Date: 2/16/18
 * Time: 11:17 AM
 */

namespace AppBundle\Utils;

use AppBundle\Entity\BlogPost;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * Class GoogleParser
 * @package AppBundle\Service
 *
 * This class parse results of search request to google
 */
class GoogleParser
{
    private $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @return mixed
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * After creation, we should set search query
     * @param mixed $query
     */
    public function setQuery($query)
    {
        $this->query = $query;
    }

    private $query;

    private $limit;

    /**
     * @var \DOMXPath $xPath
     */
    private $xPath;

    public function getRows()
    {
        $rows = [];
        // google has simplified html for such curl requests
        // every result item has class "g"
        $rowsHtml = $this->xPath->query('//div[@class="g"]');
        $cntr = 0;
        //for($i = 0; $i < $this->limit ;$i++) {
        foreach ($rowsHtml as $row) {
            if($cntr >= $this->getLimit()) {
                break;
            }

            $item = [
                'title' => '',
                'body' => '',
                'href' => ''
            ];
            // url for this item
            $a = $this->xPath->query('div[@class="s"]/div[@class="kv"]/cite', $row);
            if ($a->length) {
                //$href = $a->item(0)->textContent;
                $item['href'] = $a->item(0)->textContent;
            }

            // item title
            $h3 = $this->xPath->query('h3', $row);
            if ($h3->length) {
                $title = $h3->item(0)->textContent;
                $item['title'] = $h3->item(0)->textContent;
            }

            // item body
            $span = $this->xPath->query('div[@class="s"]/span[@class="st"]', $row);
            if ($span->length) {
                $item['body'] = $span->item(0)->textContent;
            }

            if (!$this->checkIfRowExist($title)) {
                $rows[] = $item;
                $cntr++;
            }
        }

        return $rows;
    }

    /**
     * Here we parse DOMElement using DOMXpath and return array for inserting to DB
     * @return array
     * @throws \Exception
     */
    public function getRow()
    {
        // google has simplified html for such curl requests
        // every result item has class "g"
        $rows = $this->xPath->query('//div[@class="g"]');
        foreach ($rows as $row) {
            $title = '';
            $body = '';
            $href = '';

            // url for this item
            $a = $this->xPath->query('div[@class="s"]/div[@class="kv"]/cite', $row);
            if ($a->length) {
                $href = $a->item(0)->textContent;
            }

            // item title
            $h3 = $this->xPath->query('h3', $row);
            if ($h3->length) {
                $title = $h3->item(0)->textContent;
            }

            // item body
            $span = $this->xPath->query('div[@class="s"]/span[@class="st"]', $row);
            if ($span->length) {
                $body = $span->item(0)->textContent;
            }

            $item = [
                'title' => $title,
                'body' => $body,
                'href' => $href
            ];

            if (!$this->checkIfRowExist($title)) {
                return $item;
            }
        }
        return null;
        throw new \Exception('All rows imoprted');
    }

    /**
     * As for task, we should save unique rows. Here we can check, if there is same title in DB
     *
     * @param $title
     * @return bool
     */
    private function checkIfRowExist($title)
    {
        $rows = $this->doctrine->getManager()->getRepository(BlogPost::class)->findBy([
            'title' => $title
        ]);
        return !empty($rows);
    }

    /**
     * After setting search query we can search & parse results
     */
    public function parse()
    {
        $resultText = $this->request();
        //die($resultText);
        libxml_use_internal_errors(true);
        $dom = new \DOMDocument;
        $dom->loadHTML($resultText);

        $this->xPath = new \DOMXPath($dom);
        return;
    }

    /**
     * just curl request
     * @return mixed
     */
    public function request()
    {
        $url = $this->getUrl();
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
        $result = curl_exec($curl);
        return $result;

    }

    /**
     * Create and return formatted search string for google
     * @return string
     */
    private function getUrl()
    {
        $string = urlencode($this->getQuery());
        return "www.google.com/search?q={$string}&num=100";
    }

    /**
     * @return mixed
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @param mixed $limit
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
    }
}