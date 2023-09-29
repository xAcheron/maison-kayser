<?php

namespace App\Classes\Reports;

use Illuminate\Support\Facades\DB;
use App\Classes\Reports\IReporter;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use App\Classes\Reports\utils\UserLocation;
use Exception;

class EncuestaReporter implements iReporter
{
    private $prevWeekDate;
    private $initDate;
    private $endDate;
    private $LastMonthinitDate;
    private $LastMonthendDate;
    private $LastYearinitDate;
    private $LastYearendDate;
    private $TwoYearinitDate;
    private $TwoYearendDate;
    private $location;
    private $locationID;
    private $companyID;
    private $result;
    private $dias = array("Domingo", "Lunes", "Martes", "Miercoles", "Jueves", "Viernes", "Sabado");
    private $tier;
    private $widgetType;

    public function setParams($params)
    {

        if (empty($params["daterange"]) || $params["daterange"] == "All") {
            $this->initDate = date("Y-m-01");
            $this->endDate = date("Y-m-t");
        } else {
            $tmpDates = explode(" - ", $params["daterange"]);
            $this->initDate = $tmpDates[0];
            $this->endDate = $tmpDates[1];
        }

        $this->widgetType = empty($params["refs"]) ? 0 : $params["refs"];

        $location = new UserLocation();
        $location->get($params["location"], $params['typeLoc']);
        $this->location = $location->locationName;
        $this->locationID = $location->locationID;
        $this->companyID = $location->company;
    }

    public function runReport()
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

    public function exportReport()
    {
    }

    public function widget($tipo = 0)
    {

        $formats = '';
        $headers = '';
        if (!empty($this->locationID)) {
            $location = "IN ($this->locationID)";
        } else {
            $location = "";
        }

        if ($this->widgetType == 1) {

            $sql = "SELECT COUNT(idEncu) AS total, AVG(P1) AS promedioP1, AVG(P2) AS promedioP2, AVG(P3) AS promedioP3, AVG(P4) AS promedioP4 FROM encuestas_rest A INNER JOIN sucursales B ON A.idSuc = B.idEMC WHERE A.idRvc IN (1,2,4) AND B.id $location AND A.fechaReg BETWEEN ? AND ? GROUP BY idRvc";
            $encuestas = DB::select($sql, [$this->initDate, $this->endDate]);

            $sql = "SELECT SUM(A.checks) AS checkes, A.rvc, IF(A.rvc = 'Salon', 1, IF(A.rvc = 'Servicio Domicilio', 4, IF(A.rvc = 'Vitrina', 2, 0))) AS id FROM vds_rvc A WHERE MONTH(A.fecha) = 3 AND A.fecha BETWEEN ? AND ? AND A.idSucursal $location AND A.rvc IN ('Salon', 'Servicio Domicilio', 'Vitrina') GROUP BY A.rvc ORDER BY id";
            $checkes = DB::select($sql, [$this->initDate, $this->endDate]);

            $resultsNegative = DB::table('encuestas_rest')
                ->join('sucursales AS B', 'encuestas_rest.idSuc', '=', 'B.idEMC')
                ->select(DB::raw('count(idEncu) as total'))
                ->whereRaw("B.id $location AND fechaReg BETWEEN '{$this->initDate}' AND '{$this->endDate}'")
                ->where(function ($query) {
                    $query->orWhere('P8', 'LIKE', '%mala%')
                        ->orWhereBetween('P1', [1, 2])
                        ->orWhereBetween('P2', [1, 2])
                        ->orWhere('P4', '=', 1)
                        ->orWhere('P8', 'LIKE', '%malo%')
                        ->orWhere('P8', 'LIKE', '%pesimo%')
                        ->orWhere('P8', 'LIKE', '%error%')
                        ->orWhere('P8', 'LIKE', '%covid%')
                        ->orWhere('P8', 'LIKE', '%baja%')
                        ->orWhere('P8', 'LIKE', '%bajo%')
                        ->orWhere('P8', 'LIKE', '%danino%')
                        ->orWhere('P8', 'LIKE', '%dañino%')
                        ->orWhere('P8', 'LIKE', '%daño%')
                        ->orWhere('P8', 'LIKE', '%feo%')
                        ->orWhere('P8', 'LIKE', '%excesibo%')
                        ->orWhere('P8', 'LIKE', '%exsecibo%')
                        ->orWhere('P8', 'LIKE', '%exsecivo%')
                        ->orWhere('P8', 'LIKE', '%excesivo%')
                        ->orWhere('P8', 'LIKE', '%exceso%')
                        ->orWhere('P8', 'LIKE', '%caro%')
                        ->orWhere('P8', 'LIKE', '%menor%')
                        ->orWhere('P8', 'LIKE', '%menos%')
                        ->orWhere('P8', 'LIKE', '%podrido%')
                        ->orWhere('P8', 'LIKE', '%roto%')
                        ->orWhere('P8', 'LIKE', '%deshonesto%')
                        ->orWhere('P8', 'LIKE', '%robo%')
                        ->orWhere('P8', 'LIKE', '%pesimo%')
                        ->orWhere('P8', 'LIKE', '%bajo%')
                        ->orWhere('P8', 'LIKE', '%erroneo%')
                        ->orWhere('P8', 'LIKE', '%equivocacion%')
                        ->orWhere('P8', 'LIKE', '%equivocasion%')
                        ->orWhere('P8', 'LIKE', '%insecto%')
                        ->orWhere('P8', 'LIKE', '%bicho%')
                        ->orWhere('P8', 'LIKE', '%olor%')
                        ->orWhere('P8', 'LIKE', '%oliente%')
                        ->orWhere('P8', 'LIKE', '%pelo%')
                        ->orWhere('P8', 'LIKE', '%cabello%')
                        ->orWhere('P8', 'LIKE', '%espera%')
                        ->orWhere('P8', 'LIKE', '%falto%')
                        ->orWhere('P8', 'LIKE', '%falta%')
                        ->orWhere('P8', 'LIKE', '%falta%')
                        ->orWhere('P8', 'LIKE', '%maltrato%')
                        ->orWhere('P8', 'LIKE', '%violencia%')
                        ->orWhere('P8', 'LIKE', '%violento%')
                        ->orWhere('P8', 'LIKE', '%grosero%')
                        ->orWhere('P8', 'LIKE', '%grocero%')
                        ->orWhere('P8', 'LIKE', '%insulto%')
                        ->orWhere('P8', 'LIKE', '%erronea%')
                        ->orWhere('P8', 'LIKE', '%errroneo%')
                        ->orWhere('P8', 'LIKE', '%error%')
                        ->orWhere('P8', 'LIKE', '%animal%')
                        ->orWhere('P8', 'LIKE', '%rata%')
                        ->orWhere('P8', 'LIKE', '%peor%')
                        ->orWhere('P8', 'LIKE', '%horrible%')
                        ->orWhere('P8', 'LIKE', '%retraso%')
                        ->orWhere('P8', 'LIKE', '%tardo%')
                        ->orWhere('P8', 'LIKE', '%tarda%')
                        ->orWhere('P8', 'LIKE', '%lenta%')
                        ->orWhere('P8', 'LIKE', '%lento%')
                        ->orWhere('P8', 'LIKE', '%violenta%')
                        ->orWhere('P8', 'LIKE', '%sucia%')
                        ->orWhere('P8', 'LIKE', '%sucio%')
                        ->orWhere('P8', 'LIKE', '%insultar%')
                        ->orWhere('P8', 'LIKE', '%insultante%')
                        ->orWhere('P8', 'LIKE', '%pobre%')
                        ->orWhere('P8', 'LIKE', '%atraco%')
                        ->orWhere('P8', 'LIKE', '%ladron%')
                        ->orWhere('P8', 'LIKE', '%deshonesto%')
                        ->orWhere('P8', 'LIKE', '%faltante%');
                })
                ->groupBy('idRvc')
                ->get();

            $data = [];
            $rvc = array('Salon', 'Vitrina', 'Delivery');
            foreach ($encuestas as $key => $value) {
                $data[$key][0] = $rvc[$key];
                $data[$key][1] = round(($value->promedioP1 * 100) / 5);
                $data[$key][2] = round(($value->promedioP2 * 100) / 5);
                //dataios[$key][3] = round($value->promedioP3, 2);
                $data[$key][3] = round(($value->promedioP4 * 100) / 3);
                #$data[$key][4] = round($value->total / $checkes[$key]->checkes, 2);
                $data[$key][4] = empty($checkes[$key]) ? 0 : round($value->total / $checkes[$key]->checkes, 2);
                $data[$key][5] = $resultsNegative[$key]->total ?? 0;
            }

            $formats = array("T", "T", "T", "T", "T", "T");
            $titulo = 'Encuestas de satisfaccion';
            $headers = array('', 'Atn %', 'Cal %', 'Exp %', '% Conv', 'Neg');
        } else if ($this->widgetType == 2) {


            if (!empty($resultsNegative[0])) {
                $data = (object)[
                    'value' => $resultsNegative[0]->total,
                    'type' => 'T',
                    'indicator' => '',
                    'company' => '',
                ];
            }

            $titulo = 'Encuestas Negativas';
        }

        $this->result = json_decode(json_encode(array(
            'titulo' => $titulo,
            'subtitulo' => "{$this->initDate} - {$this->endDate}",
            'headers' => $headers,
            'data' => $data,
            'formats' => $formats
        )));
    }
}
