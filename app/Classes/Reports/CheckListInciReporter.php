<?php

namespace App\Classes\Reports;

use Illuminate\Support\Facades\DB;
use App\Classes\Reports\IReporter;
use App\Classes\Reports\utils\UserLocation;
use Illuminate\Support\Facades\Mail;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Storage;

class CheckListInciReporter implements iReporter
{

    private $initDate;
    private $endDate;
    private $month;
    private $months = ["", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
    private $location;
    private $result;
    private $locationID;
    private $lastWeek;
    private $idEvaluacion;
    private $cantidad;
    const resultView = "reports.results.evaluacion";

    public function setParams($params)
    {
        $tempDates = explode(' - ', $params["daterange"]);
        $this->initDate = $tempDates[0];
        $this->endDate = $tempDates[1];
        $this->idEvaluacion = $params['idEvaluacion'];
        $this->cantidad = $params['cantidad'];
        $location = new UserLocation();
        $location->get([$params["location"]], $params['typeLoc']);
        $this->location = implode(',', $location->locationNombres) ?? $location->locationName;
        $this->locationID = $location->locationID;
    }

    public function runReport()
    {
        $idEvaluacion = $this->idEvaluacion;
        $locationID = $this->locationID;
        $initDate = $this->initDate;
        $endDate = $this->endDate;
        $locations = $this->location;

        $limit = $this->cantidad == 'All' ? '' : "LIMIT $this->cantidad";

        $sql = "SELECT COUNT(B.idItem) as count, B.accion FROM checklist_evaluacion AS D INNER JOIN checklist_info AS E ON D.idEvaluacion = E.idEvaluacion INNER JOIN checklist_sub_info AS C ON E.id = C.idCheckNum INNER JOIN checklist_generados_detalles AS A ON C.idSubseccion = A.idSubseccion INNER JOIN checklist_generados AS F ON F.idCheckList = A.idCheckList INNER JOIN checklist_items AS B ON A.idPregunta = B.idItem WHERE D.idEvaluacion = $idEvaluacion AND B.idTipo != 4 AND F.idSuc IN ($locationID) AND ((A.estado*100)/B.puntaje <= 50) AND F.fechaGenerada BETWEEN '$initDate' AND '$endDate' GROUP BY B.accion ORDER BY COUNT(B.idItem) DESC $limit";

        $masIncidencias = DB::select($sql);
        $dataPreg = [];
        $idsItems = [];

        foreach ($masIncidencias as $key => $value) {
            $dataPreg['labels'][] = $value->accion;
            $dataPreg['data'][] = $value->count;
            $idsItems[] = "'$value->accion'";
        }
        $idsItems = implode(',', $idsItems);

        $sql = "SELECT GROUP_CONCAT(count) as count, accion, GROUP_CONCAT(month) as month FROM (
            SELECT COUNT(B.idItem) as count, B.accion, MONTH(F.fechaGenerada) as month FROM checklist_evaluacion AS D INNER JOIN checklist_info AS E ON D.idEvaluacion = E.idEvaluacion INNER JOIN checklist_sub_info AS C ON E.id = C.idCheckNum INNER JOIN checklist_generados_detalles AS A ON C.idSubseccion = A.idSubseccion INNER JOIN checklist_generados AS F ON F.idCheckList = A.idCheckList INNER JOIN checklist_items AS B ON A.idPregunta = B.idItem WHERE D.idEvaluacion = $idEvaluacion AND B.idTipo != 4 AND F.idSuc IN ($locationID) AND (A.estado*100)/B.puntaje <= 50 AND F.fechaGenerada BETWEEN '$initDate' AND '$endDate' AND B.accion IN ($idsItems) GROUP BY B.accion, MONTH(F.fechaGenerada) ORDER BY B.accion, MONTH(F.fechaGenerada)
        ) AS T GROUP BY accion ORDER BY SUM(count) DESC $limit;";

        $inciMonth = DB::select($sql, []);

        $dataMonth = [];
        $dataMonth['labels'] = [];

        $initMonth = date('n', strtotime($initDate));
        $endMonth = date('n', strtotime($endDate));

        for ($i = $initMonth; $i <= $endMonth; $i++) {
            $dataMonth['labels'][] = $this->months[$i];
        }

        foreach ($inciMonth as $key => $value) {
            $count = explode(',', $value->count);
            $month = explode(',', $value->month);
            $dataMonth['datasets'][$key]['label'] = $value->accion;
            for ($i = $initMonth; $i <= $endMonth; $i++) {
                if (in_array($i, $month)) {
                    $dataMonth['datasets'][$key]['data'][] = intval($count[array_search($i, $month)]);
                } else if ($i != $endMonth) {
                    $dataMonth['datasets'][$key]['data'][] = 0;
                }
            }
        }

        $sql = "SELECT COUNT(B.idItem) as count, C.nombre FROM checklist_evaluacion AS D INNER JOIN checklist_info AS E ON D.idEvaluacion = E.idEvaluacion INNER JOIN checklist_sub_info AS C ON E.id = C.idCheckNum INNER JOIN checklist_generados_detalles AS A ON C.idSubseccion = A.idSubseccion INNER JOIN checklist_generados AS F ON F.idCheckList = A.idCheckList INNER JOIN checklist_items AS B ON A.idPregunta = B.idItem WHERE D.idEvaluacion = $idEvaluacion AND B.idTipo != 4 AND F.idSuc IN ($locationID) AND (A.estado*100)/B.puntaje <= 50 AND F.fechaGenerada BETWEEN '$initDate' AND '$endDate' GROUP BY C.idSub, C.nombre ORDER BY COUNT(B.idItem) DESC $limit;";

        $inciSec = DB::select($sql, [$this->idEvaluacion, $this->locationID, $this->initDate, $this->endDate]);

        $dataSec = [];

        foreach ($inciSec as $key => $value) {
            $dataSec['labels'][] = $value->nombre;
            $dataSec['data'][] = $value->count;
        }


        $sql = "SELECT GROUP_CONCAT(COUNT ORDER BY COUNT desc) as count,accion ,GROUP_CONCAT(idSuc ORDER BY COUNT desc) as ids, GROUP_CONCAT(nombre ORDER BY COUNT desc) as nombres FROM ( SELECT COUNT(B.accion) AS count, B.accion, F.idSuc, G.nombre FROM checklist_evaluacion AS D INNER JOIN checklist_info AS E ON D.idEvaluacion = E.idEvaluacion INNER JOIN checklist_sub_info AS C ON E.id = C.idCheckNum INNER JOIN checklist_generados_detalles AS A ON C.idSubseccion = A.idSubseccion INNER JOIN checklist_generados AS F ON F.idCheckList = A.idCheckList INNER JOIN checklist_items AS B ON A.idPregunta = B.idItem INNER JOIN sucursales AS G ON G.id = F.idSuc WHERE D.idEvaluacion = $idEvaluacion AND B.idTipo != 4 AND (A.estado*100)/B.puntaje <= 50 AND F.idSuc IN ($locationID) AND B.accion IN ($idsItems) AND  F.fechaGenerada BETWEEN '$initDate' AND '$endDate' GROUP BY B.accion, F.idSuc, G.nombre ORDER BY COUNT(B.idItem) DESC, accion) q GROUP BY accion ORDER BY SUM(count) DESC $limit";

        $pregSuc = DB::select($sql);

        $dataPregSuc = [];
        $dataPregColl = [];

        $dataPregSuc['headers'] = ['Pregunta'];
        $locationsArray = explode(',', $locations);

        $dataPregSuc['headers'] = array_merge($dataPregSuc['headers'], $locationsArray);

        foreach ($pregSuc as $keyPreg => $value) {
            $nombresArray = explode(',', $value->nombres);
            $countArray = explode(',', $value->count);
            foreach ($dataPregSuc['headers'] as $key => $nombre) {
                $index = array_search($nombre, $nombresArray);
                if ($key == 0) {
                    $dataPregSuc['body'][$keyPreg][] = $value->accion;
                } else 
                if ($index != false) {
                    $dataPregSuc['body'][$keyPreg][] = $countArray[$index];
                } else {
                    $dataPregSuc['body'][$keyPreg][] = '0';
                }
            }
        }

        foreach ($pregSuc as $key => $value) {
            $dataPregColl[$key]['accion']  = $value->accion;
            $dataPregColl[$key]['suc']  = explode(',', $value->nombres);
            $dataPregColl[$key]['count']  = explode(',', $value->count);
            $index = array_search($value->accion, array_column($dataMonth['datasets'], 'label'));
            if ($index !== false) {
                $dataMonth['datasets'][$key]['dataPreg'] = $dataPregColl[$key];
            }
        }

        $this->result = [
            'inciPregunta' => $dataPreg,
            'inciMonth' => $dataMonth,
            'inciSec' => $dataSec,
            'pregSuc' => $dataPregSuc,
        ];
    }
    public function getResult($type)
    {
        if ($type == "xlsx") {
            $this->exportXLSX();
        } elseif ($type == "email") {
            $this->exportEmail();
        } else {
            $parser = new ReportParser($type);

            if ($type == "html")
                return $parser->parse($this->result, json_decode(json_encode(array("view" => SELF::resultView))));

            return $parser->parse($this->result);
        }
    }

    private function exportReport()
    {

        // $writer = IOFactory::createWriter($spreadsheet, "Xlsx");


        // return $writer;
    }

    public function exportXLSX()
    {
        // $writer = $this->exportReport();

        // header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        // header('Content-Disposition: attachment; filename="Evaluacion_' . date("Ymd") . '.xlsx"');
        // $writer->save("php://output");

        // Storage::delete("app/public/Evaluacion_" . date("Ymd") . ".xlsx");
    }

    public function exportEmail()
    {

        // $writer = $this->exportReport();

        // $path = storage_path('app/public') . "/Evaluacion_" . date("Ymd") . ".xlsx";
        // $writer->save($path);
        // $mes = $this->month;
        // Mail::send('reports.mail.mailEvaluacion', [], function ($message) use ($path, $mes) {
        //     $message->from('reportes@prigo.com.mx', 'Reportes PRIGO');
        //     $message->to(['arata@prigo.com.mx', 'amarch@prigo.com.mx', 'ggb@igobe.mx', 'acolin@maison-kayser.com.mx', 'fernandaflores@maison-kayser.com.mx', 'lgb@maison-kayser.com.mx', 'eromo@prigo.com.mx', 'cvillamar@prigo.com.mx', 'eromo@prigo.com.mx']);
        //     //$message->to(['javiles@prigo.com.mx']);
        //     $message->bcc(['rgallardo@prigo.com.mx']);
        //     $message->subject("Evaluacion " . $mes . " 2022");
        //     $message->attach($path);
        // });

        // Storage::delete("app/public/Evaluacion_" . date("Ymd") . ".xlsx");
    }
}
