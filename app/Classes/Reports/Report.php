<?php

namespace App\Classes\Reports;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Report
{

    protected $reporter;
    private $resultType;

    public function __construct($id, $params = [], $resultType)
    {

        if ($id == 1)
            $this->reporter = new MeReporter();
        if ($id == 2)
            $this->reporter = new CashReporter();
        if ($id == 3)
            $this->reporter = new SlTxTpReporter();
        if ($id == 4)
            $this->reporter = new MeDetailReporter();
        if ($id == 5)
            $this->reporter = new PmixReporter();
        if ($id == 6)
            $this->reporter = new DpartReporter();
        if ($id == 7)
            $this->reporter = new MonthlyReporter();
        if ($id == 8)
            $this->reporter = new DiscountReporter();
        if ($id == 9)
            $this->reporter = new VITReporter();
        if ($id == 10)
            $this->reporter = new PmixtbReporter();
        if ($id == 11)
            $this->reporter = new BudgetReporter();
        if ($id == 12)
            $this->reporter = new VSemProdReporter();
        if ($id == 13)
            $this->reporter = new CheckListReporter();
        if ($id == 14)
            $this->reporter = new RvcSalesReporter();
        if ($id == 15)
            $this->reporter = new LastYearReporter();
        if ($id == 16)
            $this->reporter = new GuestReporter();
        if ($id == 17)
            $this->reporter = new PaLReporter();
        if ($id == 18)
            $this->reporter = new HeadCountReporter();
        if ($id == 19)
            $this->reporter = new RVCReporter();
        if ($id == 20)
            $this->reporter = new DayPartReporter();
        if ($id == 21)
            $this->reporter = new EncuestaReporter();
        if ($id == 22)
            $this->reporter = new DeliveryReporter();
        if ($id == 23)
            $this->reporter = new VentaSucReporter();
        if ($id == 24)
            $this->reporter = new SeguimientoArtReporter();
        if ($id == 25)
            $this->reporter = new AnalisisPrecioReporter();
        if ($id == 26) {
            $class = "\App\Classes\Reports\MonthlyExecReporter";
            $this->reporter = new $class();
        }
        if ($id == 27)
            $this->reporter = new CheckListInciReporter();
        if ($id == 28)
            $this->reporter = new MantenimientoReporter();

        $this->reporter->setParams($params);
        $this->resultType = $resultType;
    }

    public function runReport()
    {
        $this->reporter->runReport();
        return $this->reporter->getResult($this->resultType);
    }

    public function widget()
    {
        $this->reporter->widget();
        return $this->reporter->getResult($this->resultType);
    }
}
