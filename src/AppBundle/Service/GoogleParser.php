<?php
/**
 * Created by PhpStorm.
 * User: sovkutsan
 * Date: 2/16/18
 * Time: 11:17 AM
 */

namespace AppBundle\Service;

use AppBundle\Entity\BlogPost;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Persistence\ManagerRegistry;

if (!function_exists('print_arr')) {
    function print_arr($var, $return = false, $special = true)
    {
        $type = gettype($var);

        $out = print_r($var, true);
        if ($special) {
            //$out = htmlspecialchars($out);
        }
        $out = str_replace(' ', '&nbsp;', $out);
        if ($type == 'boolean') {
            $content = $var ? 'true' : 'false';
        } else {
            $content = nl2br($out);
        }
        $count = '';
        if ($type == 'array') {
            $count = ' (' . count($var) . ' items)';
        }

        $out = '<div style="
       border:2px inset #666;
       background:black;
       font-family:monospace;
       font-size:12px;
       color:#6F6;
       text-align:left;
       margin:20px;
       padding:16px">
         <span style="color: #F66">(' . $type . ')</span>' . $count . ' ' . $content . '</div><br /><br />';

        if (!$return)
            echo $out;
        else
            return $out;
    }
}

function print_die($var, $return = false, $special = true)
{
    print_arr($var, $return, $special);
    $info = debug_backtrace();
    print_arr("File: {$info[0]['file']} Line: {$info[0]['line']}");
    die ();
}

/**
 * Class GoogleParser
 * @package AppBundle\Service
 *
 * This class parse
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
     * @param mixed $query
     */
    public function setQuery($query)
    {
        $this->query = $query;
    }

    private $query;
    /**
     * @var \DOMXPath $xPath
     */
    private $xPath;

    public function getRow()
    {
        $rows = $this->xPath->query('//div[@class="g"]');
        foreach ($rows as $row) {
            $title = '';
            $body = '';
            $href = '';

            //$a = $this->xPath->query('div[@class="s"]/div[@class="kv"]', $row);
            $a = $this->xPath->query('div[@class="s"]/div[@class="kv"]/cite', $row);
            if ($a->length) {
                $href = $a->item(0)->textContent;
            }

            $h3 = $this->xPath->query('h3', $row);
            if ($h3->length) {
                $title = $h3->item(0)->textContent;
                }

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
        throw new \Exception('All rows imoprted');
    }

    private function checkIfRowExist($title)
    {
        $rows = $this->doctrine->getManager()->getRepository(BlogPost::class)->findBy([
            'title' => $title
        ]);
        return !empty($rows);
    }

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

    private function getUrl()
    {
        $string = urlencode($this->getQuery());
        return "www.google.com/search?q={$string}";
    }

    private function getDescription($node)
    {
        $description = '';
        if ($node) {
            $tmpNodes = $node->getElementsByTagName('span');
            if ($tmpNodes) {
                foreach ($tmpNodes as $tmp) {
                    $class = ($tmp) ? $tmp->getAttribute('class') : '';
                    if ($class == 'st') {
                        $description = $tmp->textContent;
                    }
                }
            }
        }
        return $description;
    }

    private function getTitle($node)
    {
        if ($node) {
            $element = $node->item(0);
            if ($element) {
                return $element->textContent;
            }
        }
        return '';
    }

    private function getHref($node)
    {
        $link = '';

        if ($node) {
            $href = $node->item(0);
            if ($href) {
                $a = $href->getElementsByTagName('a');
                if ($a) {
                    $link = $a->item(0)->getAttribute('href');
                    $link = $this->processLink($link);
                }
            }
        }

        return $link;
    }

    private function getImage($node)
    {
        $image = '';
        if ($node) {
            $imgNode = $node->getElementsByTagName('img')->item(0);
            if ($imgNode) {
                $img = $imgNode->getAttribute('src');
                $image = "<img src={$img}>";
            }
        }
        return $image;
    }

    private function processLink($link)
    {
        $link = str_replace('/url?q=', '', $link);
        if ($link) {
            $pos = strpos($link, '&sa');
            if ($pos !== false) {
                $link = substr($link, 0, $pos);
                return urldecode($link);
            }
        }
        return $link;
    }
}