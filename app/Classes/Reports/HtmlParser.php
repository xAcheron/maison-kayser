<?php

namespace App\Classes\Reports;

use App\Classes\Reports\IReportParser;
use Illuminate\Http\Response;

class HtmlParser implements iReportParser {

    public function parse($content, $params) {

        if(!empty($params->view))
            return view($params->view, ["content" => $content]);

        return "<div>Parsing Error: View has not been defined</div>";
    }

}