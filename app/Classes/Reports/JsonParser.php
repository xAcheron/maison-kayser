<?php

namespace App\Classes\Reports;

use App\Classes\Reports\IReportParser;
use Illuminate\Http\Response;

class JsonParser implements IReportParser {

    public function parse($content, $params=[]) {
       return response()->json(["success" => true, "data" => $content]);
    }

}