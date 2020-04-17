<?php

namespace osparser;
use osparser\URLContent;
use osparser\StoreParser;

class FoxtrotParser implements StoreParser, \Serializable{

    private $urlContent;
    private $dataCategory;
    private $category;
    private $host;
    public  $product;
    private $countPages;
    private $page;
    private $link;

    private $minSecond;
    private $maxSecond;

    public function __construct(){
        $this->host="https://www.foxtrot.com.ua";
        $this->urlContent=new URLContent;
        $this->minSecond=$this->maxSecond=0;
        $this->setting(StoreParser::PRODUCT_ALL);
    }

    public function category($url=null){
        if ($url != null) {
            if (!is_string($url)) {
                throw new InvalidArgumentException("Argument must be a string in method " . __METHOD__);
            }
            $url = trim($url);
            if (empty($url)) {
                throw new InvalidArgumentException("Argument mustn't be empty in method " . __METHOD__);
            }
            if (strpos($url, $this->host) === false) {
                $url = $this->host . $url;
            }

            $this->category = $url;
        }
        return $this->category;
    }
    public function countPages(){
        if($this->dataCategory=$this->urlContent->setURL($this->category)->getHTML()){
            echo "data category".htmlspecialchars($this->dataCategory);
            $pattern = '|data-page="([^"]+)"|';
            if (preg_match_all($pattern, $this->dataCategory, $matches)) {
                return $this->countPages = $matches[1][count($matches[1]) - 2];
            }
        }
        return false;
    }
    public function getCurrentPage(){
        return $this->page;
    }
    public function page($page){

        if(!is_int($page)){
            throw new InvalidArgumentException("Argument must be integer in method ".__METHOD__);
        }
        if($page<=0){
            throw new InvalidArgumentException("Argument value must be greater than zero in method ".__METHOD__);
        }
        $this->page=$page;

        if($this->page==1){
            $url=$this->category;
        }
        else{
            $url=$this->category."?page=$this->page";

        }
        if(!$data=$this->urlContent->setURL($url)->getHTML())return false;
        $this->urlContent->setReferer($url);
        $this->dataCategory=$data;
        return $this;
    }
    public function currentPage(){
        return $this->page;
    }


    public function setInterval($min,$max){
        if(!is_int($min)&&!is_int($max)){
            throw new InvalidArgumentException("Arguments must be integer in method ".__METHOD__);
        }
        if($min<0||$max<0){
            throw new InvalidArgumentException("Argument value must be greater than zero in method ".__METHOD__);
        }
        if($min>$max){
            throw new InvalidArgumentException('Argument $min must be less than $max in method '.__METHOD__);
        }
        $this->minSecond=$min;
        $this->maxSecond=$max;
        return $this;
    }

    public function product($url=null){
        if($url!==null){
            if($dataProduct=$this->urlContent->setURL($url)->getHTML()){
                if(array_key_exists('title',$this->product)){
                    $pattern='|id="product-page-title".+?data-title="([^"]+)">|sm';
                    if(preg_match_all($pattern,$dataProduct,$matches)){
                        $this->product['title']=$matches[1][0];
                    }
                }

                if(array_key_exists('img',$this->product)){
                    $pattern='|<div class="product-img__carousel owl-carousel js-product-carousel">(.+?)</div>|sm';
                    if(preg_match_all($pattern,$dataProduct,$matches)&&
                        preg_match_all('|src="([^"]+)"|',$matches[1][0],$matches)){
                        $this->product['img']=$matches[1];
                    }
                }
                if(array_key_exists('desc',$this->product)) {
                    $pattern='|<td><p>([^<]+)</p></td>[^>]*<td>[^>]*<a[^>]+>[\s]*([\S]+)[\s]*<|sm';
                    if(preg_match_all($pattern,$dataProduct,$matches)) {
                        $this->product['desc']=null;
                        $size = count($matches[1]);
                        for ($i = 0; $i < $size; $i++) {
                            $this->product['desc'] .= $matches[1][$i] . ":" . str_replace(';', ',', $matches[2][$i]) . ";";
                        }
                    }
                }
                if(array_key_exists('price',$this->product)){
                    $pattern='|class="card-price">(.+?)<|sm';
                    if(preg_match_all($pattern,$dataProduct,$matches)){
                        $this->product['price']=intval(str_replace(" ","",$matches[1][0]));
                    }
                }
                $this->product['link']=$url;
            }

        }
        return $this->product;
    }

    public function parse()
    {

        sleep(rand($this->minSecond, $this->maxSecond));
        if ($this->productLinksFromPage()) {
            $countItems = count($this->link);
            $products=[];
            for ($i = 0; $i < $countItems; $i++) {
                sleep(rand($this->minSecond, $this->maxSecond));
                if ($this->product($this->host.$this->link[$i])) $products[] = $this->product();
                else break;
            }
            return $products;
        }
        return false;
    }

    public function setting($setting){
        if(!is_int($setting)){
            throw new InvalidArgumentException("Argument must be an integer in ".__METHOD__);
        }
        if(is_array($this->product)){
            $temp=$this->product;
        }
        unset($this->product);
        if($setting&StoreParser::PRODUCT_TITLE){
            $this->product['title']=$temp['title'];
        }
        if($setting&StoreParser::PRODUCT_PRICE){
            $this->product['price']=$temp['price'];
        }
        if($setting&StoreParser::PRODUCT_DESC){
            $this->product['desc']=$temp['desc'];
        }
        if($setting&StoreParser::PRODUCT_IMG){
            $this->product['img']=$temp['img'];
        }
        return $this;
    }

    public function productLinksFromPage(){
        $pattern='|class="card__image">[\s]*?<a href="([^"]+)">|s';
        if(preg_match_all($pattern,$this->dataCategory,$matches)) {
            return $this->link = $matches[1];
        }
        return false;
    }


    public function saveObject($filename){
        $file=fopen($filename,"w");
        if(!$file)return false;
        fwrite($file,$this->serialize());
        fclose($file);
        return true;
    }

    public function serialize(){
        return serialize([$this->category,$this->host,$this->countPages,$this->page,$this->minSecond,$this->maxSecond]);
    }
    public function unserialize($unserialized){
        $res=unserialize($unserialized);
        if($res===false) return false;
        list($this->category,$this->host,$this->countPages,$this->page,$this->minSecond,$this->maxSecond)=$res;
        return true;
    }


}