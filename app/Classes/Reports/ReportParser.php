<?php 

namespace App\Classes\Reports;

class ReportParser {
    
    private $parser;

    public function __construct($type) {
        if($type=='json')
            $this->parser = new JsonParser();
        if($type=='xlsx')
            $this->parser = new XslxParser();
        if($type=='html')
            $this->parser = new HtmlParser();
    }

    public function parse($content,$params=[]){
        return $this->parser->parse($content, $params);
    }

}