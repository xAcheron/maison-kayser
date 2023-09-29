<?php

namespace App\Classes\Reports;

use App\Classes\Reports\IReportParser;
use Illuminate\Http\Response;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class XslxParser implements IReportParser {

    public function parse($content, $params=[]) {

        $spreadsheet = new Spreadsheet();
        $spreadsheet->setActiveSheetIndex(0);
        $currentSheet = $spreadsheet->getActiveSheet();
        $spreadsheet->getDefaultStyle()->getFont()->setSize(11);

        $currentSheet->setCellValue('A1', 'Major Grp');
        $currentSheet->setCellValue('B1', 'Net Sales');
        $currentSheet->setCellValue('C1', 'Gross Sales');

        $i=2;

        foreach($content as $partida)
        {
            $currentSheet->setCellValue('A'.$i, $partida->major);
            $currentSheet->setCellValue('B'.$i, $partida->ventaNeta);
            $currentSheet->setCellValue('C'.$i, $partida->ventaBruta);
            $i++;
        }

        $writer = IOFactory::createWriter($spreadsheet, "Xlsx");
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="MenuEng_'.date("Ymd").'.xlsx"');
        $writer->save("php://output");

    }

}