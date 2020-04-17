<?php

namespace osparser;

class URLContent{
    
    private $url;
    private $connect;
    private $data;
    private $referer;
    private $responseCode;

    public function __construct($url=null){
        $this->setURL($url);
        if(!$this->connect=curl_init()) throw new Exception("Curl is not init");
    }

    public function setURL($url){
        $this->url=$url;
        return $this;
    }

    public function getHTML(){
        
        $this->responseCode=$this->data=null;

        if($this->url==null){
            throw new Exception("URL not set");
        }

        curl_setopt($this->connect,CURLOPT_HEADER,0);
        curl_setopt($this->connect,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($this->connect,CURLOPT_URL,$this->url);
        //curl_setopt($this->connect,CURLOPT_USERAGENT,"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.122 Safari/537.36");
        curl_setopt($this->connect,CURLOPT_USERAGENT,"Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.160 Mobile Safari/537.36");
        if($this->referer)curl_setopt($this->connect,CURLOPT_REFERER,$this->referer);

    
        $this->data=curl_exec($this->connect);

        $this->responseCode=curl_getinfo($this->connect)['http_code'];
        if($this->responseCode!=200&&$this->responseCode!=404){
            return false;
        }
      
        return $this->data;
    }

    function saveImg($url,$filename){
        if(!$data=file_get_contents($url))return false;
        $file=fopen($filename,"w");
        if(!$file)return false;
        fwrite($file,$data);
        fclose($file);
        return true;    
    }

    public function getResponseCode(){
        return $this->responseCode;
    }
    public function setReferer($url){
        $this->referer=$url;
        return $this;
    }

    public function getFile($filename){
        if(!is_string($filename)){
            throw new Exception("Params must be string in ".__METHOD__);
        }
        $filename=trim($filename);
        if(empty($filename)){
            throw new Exception("File not named");
        }

        $file=fopen($filename,"w+");
        if(!$file) throw new Exception("File cannot open!");
        if(!$this->data){
            if(!$this->getHTML())return false;
        }
        fwrite($file,$this->data);
        fclose($file);

        return true;
    }

    public function __destruct(){
        curl_close($this->connect);
    }
}

