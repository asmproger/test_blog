<?php
/**
 * Created by PhpStorm.
 * User: sovkutsan
 * Date: 2/16/18
 * Time: 11:17 AM
 */

namespace AppBundle\Service;

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
class GoogleParser
{
    public function __construct()
    {
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
    private $itemsList;

    public function parse()
    {
        $resultText = $this->request();

        $bodyStart = strpos($resultText, '<div id="search"');
        $bodyEnd = strpos($resultText, '<div id="foot">');
        $body = substr($resultText, $bodyStart, $bodyEnd - $bodyStart);

        libxml_use_internal_errors(true);
        $dom = new \DOMDocument;

        if (!$dom->loadHTML($body)) {
            throw new \Exception('parsing error');
        }

        $el1 = $dom->getElementById('ires');
        if ($el1) {
            $this->itemsList = $el1->firstChild->childNodes;
        } else {
            throw new \Exception('parsing error');
        }


        $rows = $this->getRow();
        foreach ($rows as $row) {
            return $row;
        }

        /*$item = [
            'title' => 'Some title ' . mt_rand(1, 99999),
            'short' => 'Some short',
            'body' => 'Some body',
            'pic' => 'some pic'
        ];*/
        //return $item;
    }

    public function request()
    {
        $url = $this->getUrl();
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt ($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
        $result = curl_exec($curl);
        return $result;

    }

    private function getUrl()
    {
        $string = urlencode($this->getQuery());
        return "www.google.com/search?q={$string}";
    }

    private function getRow()
    {
        for ($i = 0; $i < 5; $i++) {
            /**
             * @var $node \DOMElement;
             */
            $node = $this->itemsList->item($i);
            if (!$node) {

            }
            $titleNode = $node->getElementsByTagName('h3');

            $title = $this->getTitle($titleNode);
            $href = $this->getHref($titleNode);
            $description = $this->getDescription($node);
            $img = $this->getImage($node);

            $row = array(
                'pic' => $img,
                //'href' => $href,
                'title' => $title,
                'body' => $description,
                'short' => substr($description, 0, 200)
            );
            yield $row;
        }
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

class GoogleEngine
{
    private $_itemsList;

    public function getHtml($count = 0)
    {
        $this->_rowsCount = $count;
        $url = $this->getSearchUrl();
        $html = '';
        try {
            $this->search($url);

            $this->parseResult();

            $rows = $this->getRow();

            foreach ($rows as $row) {
                $rowHtml = $this->getRowHtml($row);
                $html .= trim($rowHtml);
            }

            return $html;
        } catch (Exception $e) {
            return '';
        }
    }

    private function getSearchUrl()
    {
        $string = urlencode($this->string);
        return "www.google.com/search?q={$string}";
    }

    private function parseResult()
    {
        $bodyStart = strpos($this->_resultHtml, '<div id="search"');
        $bodyEnd = strpos($this->_resultHtml, '<div id="foot">');
        $this->_resultBody = substr($this->_resultHtml, $bodyStart, $bodyEnd - $bodyStart);

        libxml_use_internal_errors(true);
        $dom = new DOMDocument;

        if (!$dom->loadHTML($this->_resultBody)) {
            throw new Exception;
        }
        $el1 = $dom->getElementById('ires');
        if ($el1) {
            $this->_itemsList = $el1->firstChild->childNodes;
        } else {
            throw new Exception;
        }
    }

    private function getRow()
    {
        for ($i = 0; $i < $this->_rowsCount; $i++) {
            /**
             * @var $node DOMElement;
             */
            $node = $this->_itemsList->item($i);
            if (!$node) {

            }
            $titleNode = $node->getElementsByTagName('h3');

            $title = $this->getTitle($titleNode);
            $href = $this->getHref($titleNode);
            $description = $this->getDescription($node);
            $img = $this->getImage($node);

            $row = array(
                'image' => $img,
                'href' => $href,
                'title' => $title,
                'description' => $description
            );
            yield $row;
        }

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

    private function getRowHtml($row = array())
    {
        if (!$row) {
            return '';
        }
        $template = file_get_contents('tpls/row.tpl');
        foreach ($row as $k => $v) {
            $template = str_replace('{' . $k . '}', trim($v), $template);
        }
        return $template;
    }
}