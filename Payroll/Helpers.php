<?php
namespace App\Payroll;

use Exception;
use Seriti\Tools\Calc;
use Seriti\Tools\Csv;
use Seriti\Tools\Html;
use Seriti\Tools\Pdf;
use Seriti\Tools\Doc;
use Seriti\Tools\Date;
use Seriti\Tools\Secure;
use Seriti\Tools\BASE_UPLOAD;
use Seriti\Tools\UPLOAD_DOCS;
use Seriti\Tools\STORAGE;


class Helpers {
   
    //generic record get, add any exceptions you want
    public static function get($db,$table_prefix,$table,$id,$key = '') 
    {
        $table_name = $table_prefix.$table;

        if($key === '') $key = $table.'_id';    
        
        if($table === 'reserve') {
            $sql = 'SELECT * FROM '.$table_name.' WHERE '.$key.' = "'.$db->escapeSql($id).'" ';
        } else {
            $sql = 'SELECT * FROM '.$table_name.' WHERE '.$key.' = "'.$db->escapeSql($id).'" ';
        }

        $record = $db->readSqlRecord($sql);
                        
        return $record;
    } 

    public static function getCompany($db,$company_id) {
        $sql = 'SELECT name,description,status,date_start,date_end,'.
                      'vat_apply,vat_rate,vat_account_id,ret_account_id,calc_timestamp '.
               'FROM '.TABLE_PREFIX.'company '.
               'WHERE company_id = "'.$db->escapeSql($company_id).'" ';
        $company = $db->readSqlRecord($sql);
        if($company == 0) throw new Exception('PAYROLL_HELPER_ERROR: INVALID Company ID['.$company_id.']');
        
        return $company;
    } 

    public static function staffPaySlips($db,$company_id,$month,$year,$options = [],&$doc_name,&$error)   
    {
        $error = '';
        $error_tmp = '';
        $html = '';
        
        if(!isset($options['output'])) $options['output'] = 'BROWSER';
        if(!isset($options['layout'])) $options['layout'] = 'SINGLE';
        if(!isset($options['format'])) $options['format'] = 'PDF';
        $options['format'] = strtoupper($options['format']);

        $pdf_override = true;
        $doc_dir = BASE_UPLOAD.UPLOAD_DOCS;
         

        $table_staff = TABLE_PREFIX.'staff';
        $table_wage = TABLE_PREFIX.'staff_wage';

        $curr = CURRENCY_SYMBOL.' ';
        
        $sql = 'SELECT W.staff_id,S.first_name,S.surname,W.wage_monthly,W.wage_xtra,W.wage_bonus,W.wage_gross, '.
                    'W.tax_paid,W.savings_paid,W.pension_staff,W.advance_paid,W.loan_paid,'.
                    'W.loan_end,W.savings_end,W.wage_paid '.
               'FROM '.$table_wage.' AS W JOIN '.$table_staff.' AS S ON(W.staff_id = S.staff_id) '.
               'WHERE S.active = 1 AND '.
                   'S.company_id = "'.$db->escapeSql(COMPANY_ID).'" AND '.
                   'W.year = "'.$db->escapeSql($year).'" AND '.
                   'W.month = "'.$db->escapeSql($month).'" '.
               'ORDER BY S.surname,S.first_name ';
        $wage_data = $db->readSqlArray($sql); 
        if($wage_data == 0) $error .= 'No staff wages found for month['.$month.'] and year['.$year.']';
          
        if($error === '') {
            if($options['format'] === 'PDF') {
                $pdf_name = Secure::clean('basic','payslips_'.Date::monthName($month).$year).'.pdf';
                $doc_name = $pdf_name;

                $pdf = new Pdf('Portrait','mm','A4');
                $pdf->AliasNbPages();
                
                $pdf->setupLayout(['db'=>$db]);

                //NB:override PDF settings to economise on space (no logo, small margins,size-6 text)
                if($pdf_override) {
                    $pdf->bg_image = array('images/logo.jpeg',5,140,50,20,'YES'); //NB: YES flag turns off logo image display
                    $pdf->page_margin = array(10,10,10,10);//top,left,right,bottom!!
                    $pdf->text = array(0,0,0,'',8); //make text font black  with size 8pt
                    $pdf->h1_title = array(33,33,33,'B',10,'',8,20,'L','YES',33,33,33,'B',12,20,180);
                    $pdf->show_header = false;
                }

                //NB footer must be set before this
                //$pdf->AddPage();

                $row_h = 5;
                                
                //new layout: each slip duplicated
                if($options['layout'] === 'DUPLICATE') {
                    $i = 0;
                    foreach($wage_data as $staff_id => $wage) {
                        if(fmod($i,3) == 0) $pdf->AddPage();
                        $i++;
                        
                        for($col = 1; $col < 3; $col++) {
                            if($col == 1) {
                                $pos = 40;
                                $y = $pdf->GetY();
                            } 
                            if($col == 2) {
                                $pos = 130;
                                $pdf->SetY($y);
                            } 
                            //owner name and address
                            $pdf->changeFont('H3');
                            $pdf->Cell($pos,$row_h,'Staff member: ',0,0,'R',0);
                            $pdf->Cell(30,$row_h,$wage['first_name'].' '.$wage['surname'],0,0,'R',0);
                            $pdf->Ln($row_h);           
                            
                            $pdf->changeFont('TEXT');
                            $pdf->Cell($pos,$row_h,'Monthly wage: ',0,0,'R',0);
                            $pdf->Cell(30,$row_h,$wage['wage_monthly'],0,0,'R',0);
                            $pdf->Ln($row_h);           
                            $pdf->Cell($pos,$row_h,'Wage for extra days: ',0,0,'R',0);
                            $pdf->Cell(30,$row_h,$wage['wage_xtra'],0,0,'R',0);
                            $pdf->Ln($row_h);           
                            $pdf->Cell($pos,$row_h,'Bonus: ',0,0,'R',0);
                            $pdf->Cell(30,$row_h,$wage['wage_bonus'],0,0,'R',0);
                            $pdf->Ln($row_h);           
                            $pdf->Cell($pos,$row_h,'Wage for month: ',0,0,'R',0);
                            $pdf->Cell(30,$row_h,$wage['wage_gross'],0,0,'R',0);
                            $pdf->Ln($row_h);    
                            
                            $pdf->changeFont('H3');
                            $pdf->Cell($pos,$row_h,'DEDUCTIONS',0,0,'R',0);
                            $pdf->Ln($row_h);  
                            $pdf->changeFont('TEXT');
                            $pdf->Cell($pos,$row_h,'Tax : ',0,0,'R',0);
                            $pdf->Cell(30,$row_h,$wage['tax_paid'],0,0,'R',0);
                            $pdf->Ln($row_h);           
                            $pdf->Cell($pos,$row_h,'Savings scheme: ',0,0,'R',0);
                            $pdf->Cell(30,$row_h,$wage['savings_paid'],0,0,'R',0);
                            $pdf->Ln($row_h);           
                            $pdf->Cell($pos,$row_h,'Pension: ',0,0,'R',0);
                            $pdf->Cell(30,$row_h,$wage['pension_staff'],0,0,'R',0);
                            $pdf->Ln($row_h);  
                            $pdf->Cell($pos,$row_h,'Advance repaid: ',0,0,'R',0);
                            $pdf->Cell(30,$row_h,$wage['advance_paid'],0,0,'R',0);
                            $pdf->Ln($row_h);  
                            $pdf->Cell($pos,$row_h,'Loan repaid: ',0,0,'R',0);
                            $pdf->Cell(30,$row_h,$wage['loan_paid'],0,0,'R',0);
                            $pdf->Ln($row_h);  
                            
                            $pdf->changeFont('H3');
                            $pdf->Cell($pos,$row_h,'Loan balance: ',0,0,'R',0);
                            $pdf->Cell(30,$row_h,$wage['loan_end'],0,0,'R',0);
                            $pdf->Ln($row_h);  
                            $pdf->Cell($pos,$row_h,'Savings total: ',0,0,'R',0);
                            $pdf->Cell(30,$row_h,$wage['savings_end'],0,0,'R',0);
                            $pdf->Ln($row_h);  
                            $pdf->Cell($pos,$row_h,'Final wage received: ',0,0,'R',0);
                            $pdf->Cell(30,$row_h,$wage['wage_paid'],0,0,'R',0);
                            $pdf->Ln($row_h);  
                            
                            $pdf->Ln($row_h*3);
                        }  
                    }
                }      
                
                //single copy setup, no longer used but still here.
                if($options['layout'] === 'SINGLE') {
                    
                    $i = 0;
                    foreach($wage_data as $staff_id=>$wage) {
                        if(fmod($i,6) == 0) $pdf->AddPage();
                        $col = fmod($i,2)+1;
                        $i++;
                        
                        if($col == 1) {
                            $pos = 40;
                            $y = $pdf->GetY();
                        } 
                        if($col == 2) {
                            $pos = 130;
                            $pdf->SetY($y);
                        }  
                                    
                        //owner name and address
                        $pdf->changeFont('H3');
                        $pdf->Cell($pos,$row_h,'Staff member: ',0,0,'R',0);
                        $pdf->Cell(30,$row_h,$wage['first_name'].' '.$wage['surname'],0,0,'R',0);
                        $pdf->Ln($row_h);           
                        
                        $pdf->changeFont('TEXT');
                        $pdf->Cell($pos,$row_h,'Monthly wage: ',0,0,'R',0);
                        $pdf->Cell(30,$row_h,$wage['wage_monthly'],0,0,'R',0);
                        $pdf->Ln($row_h);           
                        $pdf->Cell($pos,$row_h,'Wage for extra days: ',0,0,'R',0);
                        $pdf->Cell(30,$row_h,$wage['wage_xtra'],0,0,'R',0);
                        $pdf->Ln($row_h);           
                        $pdf->Cell($pos,$row_h,'Bonus: ',0,0,'R',0);
                        $pdf->Cell(30,$row_h,$wage['wage_bonus'],0,0,'R',0);
                        $pdf->Ln($row_h);           
                        $pdf->Cell($pos,$row_h,'Wage for month: ',0,0,'R',0);
                        $pdf->Cell(30,$row_h,$wage['wage_gross'],0,0,'R',0);
                        $pdf->Ln($row_h);    
                        
                        $pdf->changeFont('H3');
                        $pdf->Cell($pos,$row_h,'DEDUCTIONS',0,0,'R',0);
                        $pdf->Ln($row_h);  
                        $pdf->changeFont('TEXT');
                        $pdf->Cell($pos,$row_h,'Tax : ',0,0,'R',0);
                        $pdf->Cell(30,$row_h,$wage['tax_paid'],0,0,'R',0);
                        $pdf->Ln($row_h);           
                        $pdf->Cell($pos,$row_h,'Savings scheme: ',0,0,'R',0);
                        $pdf->Cell(30,$row_h,$wage['savings_paid'],0,0,'R',0);
                        $pdf->Ln($row_h);           
                        $pdf->Cell($pos,$row_h,'Pension: ',0,0,'R',0);
                        $pdf->Cell(30,$row_h,$wage['pension_staff'],0,0,'R',0);
                        $pdf->Ln($row_h);  
                        $pdf->Cell($pos,$row_h,'Advance repaid: ',0,0,'R',0);
                        $pdf->Cell(30,$row_h,$wage['advance_paid'],0,0,'R',0);
                        $pdf->Ln($row_h);  
                        $pdf->Cell($pos,$row_h,'Loan repaid: ',0,0,'R',0);
                        $pdf->Cell(30,$row_h,$wage['loan_paid'],0,0,'R',0);
                        $pdf->Ln($row_h);  
                        
                        $pdf->changeFont('H3');
                        $pdf->Cell($pos,$row_h,'Loan balance: ',0,0,'R',0);
                        $pdf->Cell(30,$row_h,$wage['loan_end'],0,0,'R',0);
                        $pdf->Ln($row_h);  
                        $pdf->Cell($pos,$row_h,'Savings total: ',0,0,'R',0);
                        $pdf->Cell(30,$row_h,$wage['savings_end'],0,0,'R',0);
                        $pdf->Ln($row_h);  
                        $pdf->Cell($pos,$row_h,'Final wage received: ',0,0,'R',0);
                        $pdf->Cell(30,$row_h,$wage['wage_paid'],0,0,'R',0);
                        $pdf->Ln($row_h);  
                        
                        $pdf->Ln($row_h*3);
                    }  
                }           
                
                //finally create pdf file
                if($options['output'] === 'FILE') {
                    $file_path = $doc_dir.$doc_name;
                    $pdf->Output($file_path,'F');  
                }    

                //send directly to browser
                if($options['output'] === 'BROWSER') {
                    $pdf->Output($doc_name,'D');
                    exit;      
                }  
                
            }

            if($options['format'] === 'CSV') {
                $csv_data = '';
                $csv_data = Csv::sqlArrayDumpCsv('staff_id',$wage_data);
                $doc_name = Secure::clean('basic','payslip_'.seriti_date::month_name($month).$year).'.csv';
                Doc::outputDoc($csv_data,$doc_name,'DOWNLOAD','csv');
                exit;
            }

            if($options['format'] === 'HTML') {
                $html .= 'WTF';
                $html .= Html::arrayDumpHtml($wage_data);
                
            }      
        }  
        
        return $html;
    }

    
      
    
}
?>
