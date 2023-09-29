<?php

namespace App\Classes\Reports;

use Illuminate\Support\Facades\DB;
use App\Classes\Reports\IReporter;
use App\Classes\Reports\utils\UserLocation;

class HeadCountReporter implements iReporter
{
    private $initDate;
    private $endDate;
    private $company;
    private $result;

    public function setParams($params)
    {
        if (empty($params['daterange']) || $params['daterange'] == "All") {
            $this->initDate = date("Y-m-01", strtotime(date("Y-m-d")));
            $this->endDate = date("Y-m-t", strtotime(date("Y-m-d")));
        }

        if (empty($params['empresa'])) {
            $location = new UserLocation();
            $empresasUsuario = $location->getHierachy(1, $params['idUsuario'] ?? '');
            if ($empresasUsuario[0]["id"])
                $this->company = $empresasUsuario[0]["id"];
            else
                $this->company = 0;
            dd($this->company);
        }
    }

    public function runReport()
    {
    }

    public function exportReport()
    {
    }

    public function getResult($type)
    {
        if ($type == "xlsx") {
            $this->exportReport();
        } else {
            $parser = new ReportParser($type);
            return $parser->parse($this->result);
        }
    }

    public function widget()
    {
    }
}
