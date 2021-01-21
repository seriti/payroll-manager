<?php
namespace App\Payroll;

use Seriti\Tools\Form;
use Seriti\Tools\Report AS ReportTool;

class Report extends ReportTool
{
     

    //configure
    public function setup() 
    {
        //$this->report_header = '';
        $this->always_list_reports = true;

        $param = ['input'=>['select_format','select_month']];
        $this->addReport('CONDENSED_PAYSLIP_DUPLICATE','Condensed Payslips(duplicates) for all staff in period',$param); 
        $this->addReport('CONDENSED_PAYSLIP','Condensed Payslips(single) for all staff in period',$param); 
        

        $this->addInput('select_month','Select Month');
        $this->addInput('select_format','Select Report format');
    }

    protected function viewInput($id,$form = []) 
    {
        $html = '';
        
        if($id === 'select_month') {
            $past_years = 5;
            $future_years = 0;

            $param = [];
            $param['class'] = 'form-control input-small input-inline';
            
            $html .= 'From:';
            if(isset($form['month'])) $month = $form['month']; else $month = date('m');
            if(isset($form['year'])) $year = $form['year']; else $year = date('Y');
            $html .= Form::monthsList($month,'month',$param);
            $html .= Form::yearsList($year,$past_years,$future_years,'year',$param);
        }

        if($id === 'select_format') {
            if(isset($form['format'])) $format = $form['format']; else $format = 'HTML';
            $html .= Form::radiobutton('format','PDF',$format).'&nbsp;<img src="/images/pdf_icon.gif">&nbsp;PDF document<br/>';
            $html .= Form::radiobutton('format','CSV',$format).'&nbsp;<img src="/images/excel_icon.gif">&nbsp;CSV/Excel document<br/>';
            $html .= Form::radiobutton('format','HTML',$format).'&nbsp;Show on page<br/>';
        }

        return $html;       
    }

    protected function processReport($id,$form = []) 
    {
        $html = '';
        $error = '';

        $options = [];
        $options['format'] = $form['format'];

        if($id === 'CONDENSED_PAYSLIP' or $id === 'CONDENSED_PAYSLIP_DUPLICATE') {
            if($id === 'CONDENSED_PAYSLIP') $options['layout'] = 'SINGLE'; else $options['layout'] = 'DUPLICATE';
            $html .= Helpers::staffPaySlips($this->db,COMPANY_ID,$form['month'],$form['year'],$options,$pdf_name,$error);
            if($error !== '') $this->addError($error);
        }

        return $html;
    }
}

?>