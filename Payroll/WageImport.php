<?php
namespace App\Payroll;

use Seriti\Tools\Date;
use Seriti\Tools\Form;
use Seriti\Tools\Doc;
use Seriti\Tools\Calc;
use Seriti\Tools\Secure;
use Seriti\Tools\Import;

use Seriti\Tools\BASE_UPLOAD;
use Seriti\Tools\UPLOAD_TEMP;
use Seriti\Tools\UPLOAD_DOCS;

use App\Payroll\Formula;
use App\Payroll\Helpers;
use App\Payroll\COMPANY_ID;
use App\Payroll\TABLE_PREFIX;

class WageImport extends Import {

    protected $upload_dir;
    
    //path to modified bank file to match internal representation
    protected $file_path_mod;
    protected $wage_month;
    protected $wage_year;
    protected $days_in_month = 26;
    protected $user_id;
    protected $ignore_errors = false;

    protected $staff = [];
    protected $wage_previous = [];
    
    //check for duplicates and errors
    protected function beforeImportData(&$data,&$valid) {
        $where = ['staff_id'=>$data['staff_id'],
                  'month'=>$this->wage_month,
                  'year'=>$this->wage_year];
        $rec = $this->db->getRecord($this->table,$where);
        if($rec !== 0 ) {
            $valid = false;
            $str = 'Wage record ID['.$rec['wage_id'].'] for Staff ID['.$data['staff_id'].'] for month['.$this->wage_year.'-'.$this->wage_month.'] already exists.';
            if($this->ignore_errors) {
                $this->addMessage($str.' IGNORED!');
            } else {
                $this->addError($str);
            }
        } 

        //NOT necessary as checked in modifyCsvLine()
        /*
        $staff = Helpers::get($this->db,TABLE_PREFIX,'staff',$data['staff_id']);
        if($staff['company_id'] !== COMPANY_ID) {
            $valid = false;
            $str = 'Staff ID['.$data['staff_id'].'] is NOT part of company['.COMPANY_NAME.'] ';
            if($this->ignore_errors) {
                $this->addMessage($str.' IGNORED!');
            } else {
                $this->addError($str);
            }
        }
        */

    }                 
                    
    public function setup($param = []) { 

        $parent_param = ['file_path'=>$param['file_path'],'data_type'=>'CSV','audit'=>true];
        parent::setup($parent_param);

        //$this->file_path = $param['file_path'];
        $this->user_id = $param['user_id'];
        $this->file_path_mod = $param['file_path_mod'];
        $this->wage_month = $param['wage_month'];
        $this->wage_year = $param['wage_year'];
        $this->days_in_month = $param['days_in_month'];
        $this->ignore_errors = $param['ignore_errors'];

        //NB: tax_basis not currently used but keep for possible usage
        $sql = 'SELECT S.staff_id,S.first_name,S.surname,S.wage_basis,S.gross_wage, '.
                      'S.saving_amount,S.pension_active,S.pension_formula,S.tax_basis,S.tax_formula,S.active '.
               'FROM '.TABLE_PREFIX.'staff AS S '.
               'WHERE S.company_id = "'.$this->db->escapeSql(COMPANY_ID).'" ORDER BY S.staff_id ';
        $this->staff = $this->db->readSqlArray($sql);

        $period = Date::getMonthInfo($this->wage_month,$this->wage_year);
      
        $sql='SELECT W.staff_id,W.loan_end,W.savings_end '.
             'FROM '.TABLE_PREFIX.'staff_wage AS W JOIN '.TABLE_PREFIX.'staff as S ON(W.staff_id = S.staff_id) '.
             'WHERE S.company_id = "'.$this->db->escapeSql(COMPANY_ID).'" AND '.
                   'W.year = "'.$period['prev']['year'].'" AND W.month = "'.$period['prev']['month'].'" ';
        $this->wage_previous = $this->db->readSqlArray($sql);       

        //static values for every record
        $this->addColFixed(array('id'=>'user_id','type'=>'STRING','title'=>'User ID','value'=>$this->user_id));
        $this->addColFixed(array('id'=>'date_create','type'=>'DATE','title'=>'Create Date','value'=>date('Y-m-d')));
        $this->addColFixed(array('id'=>'month','type'=>'INTEGER','title'=>'Wage month','value'=>$this->wage_month));
        $this->addColFixed(array('id'=>'year','type'=>'INTEGER','title'=>'Wage year','value'=>$this->wage_year));
                        
        //static values that are *** CALCULATED *** for every record in modify_csv_line()
        $this->addColFixed(['id'=>'wage_monthly','type'=>'INTEGER','title'=>'Monthly Wage','value'=>'0','update'=>true]);
        $this->addColFixed(['id'=>'wage_xtra','type'=>'DECIMAL','title'=>'Monthly Xtra','value'=>'0','update'=>true]);
        $this->addColFixed(['id'=>'tax_paid','type'=>'DECIMAL','title'=>'PAYE paid','value'=>'0','update'=>true]);
        $this->addColFixed(['id'=>'pension_staff','type'=>'DECIMAL','title'=>'Pension staff component','value'=>'0','update'=>true]);
        $this->addColFixed(['id'=>'pension_company','type'=>'DECIMAL','title'=>'Pension company component','value'=>'0','update'=>true]);
        $this->addColFixed(['id'=>'pension_life','type'=>'DECIMAL','title'=>'Pension life cover','value'=>'0','update'=>true]);
        $this->addColFixed(['id'=>'pension_admin','type'=>'DECIMAL','title'=>'Pension admin fee','value'=>'0','update'=>true]);
        $this->addColFixed(['id'=>'pension_paid','type'=>'DECIMAL','title'=>'Pension paid','value'=>'0','update'=>true]);
        $this->addColFixed(['id'=>'loan_start','type'=>'DECIMAL','title'=>'Loan open balance','value'=>'0','update'=>true]);
        $this->addColFixed(['id'=>'loan_end','type'=>'DECIMAL','title'=>'Loan close balance','value'=>'0','update'=>true]);
        $this->addColFixed(['id'=>'savings_start','type'=>'DECIMAL','title'=>'Saving open balance','value'=>'0','update'=>true]);
        $this->addColFixed(['id'=>'savings_paid','type'=>'DECIMAL','title'=>'Savings paid','value'=>'0','update'=>true]);
        $this->addColFixed(['id'=>'savings_end','type'=>'DECIMAL','title'=>'Saving close balance','value'=>'0','update'=>true]);
        $this->addColFixed(['id'=>'advance_paid','type'=>'DECIMAL','title'=>'Advance paid','value'=>'0','update'=>true]);
        $this->addColFixed(['id'=>'wage_gross','type'=>'DECIMAL','title'=>'Wage gross','value'=>'0','update'=>true]);
        $this->addColFixed(['id'=>'wage_deduct','type'=>'DECIMAL','title'=>'Wage deduction','value'=>'0','update'=>true]);
        $this->addColFixed(['id'=>'wage_paid','type'=>'DECIMAL','title'=>'Wage paid','value'=>'0','update'=>true]);
        $this->addColFixed(['id'=>'wage_adjust','type'=>'DECIMAL','title'=>'Wage adjust','value'=>'0','update'=>true]);
        
        //NB: need to specify key even if not imported 
        $this->setupKey(['id'=>'wage_id','type'=>'INTEGER','title'=>'Wage_ID','key'=>true,'key_auto'=>true]);
        //CSV file configuration in correct col order
        $this->addImportCol(['id'=>'staff_id','type'=>'INTEGER','title'=>'Staff_ID','update'=>true]);
        //names are checked but NOT imported
        $this->addImportCol(['id'=>'first_name','type'=>'IGNORE','title'=>'First_name']);
        $this->addImportCol(['id'=>'surname','type'=>'IGNORE','title'=>'Surname']);

        $this->addImportCol(['id'=>'days_worked','type'=>'INTEGER','title'=>'Days_worked','update'=>true]);
        $this->addImportCol(['id'=>'days_off','type'=>'INTEGER','title'=>'Days_off','update'=>true]);
        $this->addImportCol(['id'=>'days_sick','type'=>'INTEGER','title'=>'Sick_days','update'=>true]);
        $this->addImportCol(['id'=>'days_funeral','type'=>'INTEGER','title'=>'Funeral_days','update'=>true]);
        $this->addImportCol(['id'=>'days_absent','type'=>'INTEGER','title'=>'Absent_days','update'=>true]);
        $this->addImportCol(['id'=>'days_xtra','type'=>'INTEGER','title'=>'Extra_days','update'=>true]);
        $this->addImportCol(['id'=>'advance_taken','type'=>'DECIMAL','title'=>'Advance_taken','required'=>false,'update'=>true]);
        $this->addImportCol(['id'=>'loan_taken','type'=>'DECIMAL','title'=>'Loan_taken','required'=>false,'update'=>true]);
        $this->addImportCol(['id'=>'savings_taken','type'=>'DECIMAL','title'=>'Savings_withdrawal','required'=>false,'update'=>true]);
        $this->addImportCol(['id'=>'loan_paid','type'=>'DECIMAL','title'=>'Loan_repaid','required'=>false,'update'=>true]);
        $this->addImportCol(['id'=>'wage_bonus','type'=>'DECIMAL','title'=>'Bonus_paid','required'=>false,'update'=>true]);

    } 

    public function useModifiedFile() 
    {
        $this->file_path = $this->file_path_mod;
    }

    public function checkStaffNames() 
    {
        $i = 0;
        $line = [];
        $handle = fopen($this->file_path,'r');
        while(($line = fgetcsv($handle,0, ",")) !== FALSE) {
            $i++;
            $value_num = count($line);
            
            //print_r($line);
            if($this->trim_values) {
                $line = array_map('trim',$line);
            }

            if($i > 1 and $value_num > 1) {
                $error = '';

                $staff_id = trim($line[0]);
                $first_name = $line[1];
                $surname = $line[2];
                if($staff_id != '') {
                    if(!isset($this->staff[$staff_id])) {
                        $error .= 'Staff ID['.$staff_id.'] not valid for company['.COMPANY_NAME.']<br/>'; 
                    } else {
                        $member = $this->staff[$staff_id];
                        if(strcasecmp($member['first_name'],$first_name) !== 0 or strcasecmp($member['surname'],$surname) !== 0) {
                            $error .= 'Invalid staff name['.$first_name.' '.$surname.'] expecting['.$member['first_name'].' '.$member['surname'].']<br/>';    
                        }
                        if(!$member['active']) {
                            $error .= 'Staff member['.$member['first_name'].' '.$member['surname'].'] NOT currently employed!<br/>';    
                        }  
                    }    
                }

                if($error != '') $this->addError($error.'In line '.$i,false); 

            }    
        }
    }

    //NB: not used as CONFIRM_FORM used
    protected function modifyCsvLine(&$line,&$error_line,&$valid_line) {
        $error = '';

        $staff_id = trim($line[0]);
        $first_name = $line[1];
        $surname = $line[2];
        if($staff_id == '') {
            $valid_line = false;
            return;
        } else {
            if(!isset($this->staff[$staff_id])) {
                $error .= 'Staff ID['.$staff_id.'] not valid for company['.COMPANY_NAME.']<br/>'; 
            } else {
                $member = $this->staff[$staff_id];
                if(strcasecmp($member['first_name'],$first_name) !== 0 or strcasecmp($member['surname'],$surname) !== 0) {
                    $error .= 'Invalid staff name['.$first_name.' '.$surname.'] expecting['.$member['first_name'].' '.$member['surname'].']<br/>';    
                }
                if(!$member['active']) {
                    $error .= 'Staff member['.$member['first_name'].' '.$member['surname'].'] NOT currently employed!<br/>';    
                }  
            }    
        }  
        
        if($error !== '') {
            $error_line = true;
            $this->addError('Line error: '.$error);
        } else {    
            //reset all values for row
            $days = [];            
            $calc = [];
            
            //convert any blank strings to zero
            for($i = 3; $i < 12; $i++) {
              if(trim($line[$i]) === '') $line[$i] = '0';
            }  
            
            $days['worked'] = intval($line[3]);
            $days['off'] = intval($line[4]);
            $days['sick'] = intval($line[5]);
            $days['funeral'] = intval($line[6]);
            $days['absent'] = intval($line[7]);
            $days['xtra'] = intval($line[8]);

            $advance_taken = Calc::floatVal($line[9]);
            $loan_taken = Calc::floatVal($line[10]);
            $savings_taken = Calc::floatVal($line[11]);
            $loan_paid = Calc::floatVal($line[12]);
            $wage_bonus = Calc::floatVal($line[13]);
             
            if($member['wage_basis'] === 'MONTHLY') {
                $calc['wage_monthly'] = $member['gross_wage'];
                $daily_wage = $calc['wage_monthly'] / $this->days_in_month;
                $pension_wage = $member['gross_wage'];
            } elseif($member['wage_basis'] === 'DAILY') {
                $daily_wage = $member['gross_wage'];
                $calc['wage_monthly'] = $daily_wage * $days['worked'];
                $pension_wage = $member['gross_wage'] * $this->days_in_month;
            }    
            
            if($days['xtra'] != 0) {
                $calc['wage_xtra'] = $days['xtra'] * $daily_wage;
            } else {
                $calc['wage_xtra'] = 0;
            }   
            $calc['wage_gross'] = $calc['wage_monthly'] + $calc['wage_xtra'] + $wage_bonus;
            
            $calc['tax_paid'] = 0;
            $calc['tax_paid'] = Formula::calcWageTax($member['tax_formula'],$calc['wage_gross']);
            
            $pension = ['staff'=>0,'company'=>0,'life'=>0,'admin'=>0,'total'=>0];
            if($member['pension_active']) $pension = Formula::calcPension($member['pension_formula'],$pension_wage);
            
            $calc['wage_deduct'] = $calc['tax_paid']  + $pension['staff'] + $member['saving_amount'] + $advance_taken + $loan_paid;
            //round wage paid to nearest 50 Kwacha and capture adjustment
            $calc['wage_paid'] = $calc['wage_gross'] - $calc['wage_deduct'];
            $calc['wage_paid'] = ceil($calc['wage_paid'] / 50) * 50;
            $calc['wage_adjust'] = $calc['wage_paid'] - ($calc['wage_gross'] - $calc['wage_deduct']);
            
            //finally assign calculated values to static cols
            $this->static_cols['wage_monthly']['value'] = $calc['wage_monthly'];
            $this->static_cols['wage_xtra']['value'] = $calc['wage_xtra'];
            
            $this->static_cols['pension_staff']['value'] = $pension['staff']; 
            $this->static_cols['pension_company']['value'] = $pension['company'];
            $this->static_cols['pension_life']['value'] = $pension['life'];
            $this->static_cols['pension_admin']['value'] = $pension['admin'];
            $this->static_cols['pension_paid']['value'] = $pension['total'];
             
            $this->static_cols['savings_start']['value'] = $this->wage_previous[$staff_id]['savings_end']; 
            $this->static_cols['savings_paid']['value'] = $member['saving_amount']; 
            $this->static_cols['savings_end']['value'] = $this->wage_previous[$staff_id]['savings_end'] - $savings_taken + $member['saving_amount'];
            
            $this->static_cols['loan_start']['value'] = $this->wage_previous[$staff_id]['loan_end']; 
            $this->static_cols['loan_end']['value'] = $this->wage_previous[$staff_id]['loan_end'] + $loan_taken - $loan_paid;  
            
            $this->static_cols['advance_paid']['value'] = $advance_taken;  
            
            $this->static_cols['wage_gross']['value'] = $calc['wage_gross'];
            $this->static_cols['tax_paid']['value'] = $calc['tax_paid'];
            $this->static_cols['wage_deduct']['value'] = $calc['wage_deduct'];
            $this->static_cols['wage_paid']['value'] = $calc['wage_paid'];
            $this->static_cols['wage_adjust']['value'] = $calc['wage_adjust'];
        }
    }

    //NB: this processes/validates confirm form NOT csv line
    protected function modifyConfirmRow(&$row,&$error_row,&$valid_row) {
        $error = '';

        if($error !== '') {
            $error_row = true;
            $this->addError('Confirm row error: '.$error);
        } else { 
            $days = [];            
            $calc = [];
            
            $staff_id = $row['staff_id'];
            if($staff_id == '') {
                $error_ = true;
                $valid_row = false;
                return;
            } else {
                if(!isset($this->staff[$staff_id])) {
                    $error_str .= 'Staff ID['.$staff_id.'] not valid for company['.COMPANY_NAME.']<br/>'; 
                } else {
                    $member = $this->staff[$staff_id];
                }    
            } 

            $days['worked'] = intval($row['days_worked']);
            $days['off'] = intval($row['days_off']);
            $days['sick'] = intval($row['days_sick']);
            $days['funeral'] = intval($row['days_funeral']);
            $days['absent'] = intval($row['days_absent']);
            $days['xtra'] = intval($row['days_xtra']);

            $advance_taken = Calc::floatVal($row['advance_taken']);
            $loan_taken = Calc::floatVal($row['loan_taken']);
            $savings_taken = Calc::floatVal($row['savings_taken']);
            $loan_paid = Calc::floatVal($row['loan_paid']);
            $wage_bonus = Calc::floatVal($row['wage_bonus']);
             
            if($member['wage_basis'] === 'MONTHLY') {
                $calc['wage_monthly'] = $member['gross_wage'];
                $daily_wage = $calc['wage_monthly'] / $this->days_in_month;
                $pension_wage = $member['gross_wage'];
            } elseif($member['wage_basis'] === 'DAILY') {
                $daily_wage = $member['gross_wage'];
                $calc['wage_monthly'] = $daily_wage * $days['worked'];
                $pension_wage = $member['gross_wage'] * $this->days_in_month;
            }    
            
            if($days['xtra'] != 0) {
                $calc['wage_xtra'] = $days['xtra'] * $daily_wage;
            } else {
                $calc['wage_xtra'] = 0;
            }   
            $calc['wage_gross'] = $calc['wage_monthly'] + $calc['wage_xtra'] + $wage_bonus;
            
            $calc['tax_paid'] = 0;
            $calc['tax_paid'] = Formula::calcWageTax($member['tax_formula'],$calc['wage_gross']);
            
            $pension = ['staff'=>0,'company'=>0,'life'=>0,'admin'=>0,'total'=>0];
            if($member['pension_active']) $pension = Formula::calcPension($member['pension_formula'],$pension_wage);
            
            $calc['wage_deduct'] = $calc['tax_paid']  + $pension['staff'] + $member['saving_amount'] + $advance_taken + $loan_paid;
            //round wage paid to nearest 50 Kwacha and capture adjustment
            $calc['wage_paid'] = $calc['wage_gross'] - $calc['wage_deduct'];
            $calc['wage_paid'] = ceil($calc['wage_paid'] / 50) * 50;
            $calc['wage_adjust'] = $calc['wage_paid'] - ($calc['wage_gross'] - $calc['wage_deduct']);
            
            //finally assign calculated values to static cols
            $this->cols_fixed['wage_monthly']['value'] = $calc['wage_monthly'];
            $this->cols_fixed['wage_xtra']['value'] = $calc['wage_xtra'];
            
            $this->cols_fixed['pension_staff']['value'] = $pension['staff']; 
            $this->cols_fixed['pension_company']['value'] = $pension['company'];
            $this->cols_fixed['pension_life']['value'] = $pension['life'];
            $this->cols_fixed['pension_admin']['value'] = $pension['admin'];
            $this->cols_fixed['pension_paid']['value'] = $pension['total'];

            $this->cols_fixed['savings_start']['value'] = $this->wage_previous[$staff_id]['savings_end']; 
            $this->cols_fixed['savings_paid']['value'] = $member['saving_amount']; 
            $this->cols_fixed['savings_end']['value'] = $this->wage_previous[$staff_id]['savings_end'] - $savings_taken + $member['saving_amount'];
            
            $this->cols_fixed['loan_start']['value'] = $this->wage_previous[$staff_id]['loan_end']; 
            $this->cols_fixed['loan_end']['value'] = $this->wage_previous[$staff_id]['loan_end'] + $loan_taken - $loan_paid;  
            
            $this->cols_fixed['advance_paid']['value'] = $advance_taken;  
            
            $this->cols_fixed['wage_gross']['value'] = $calc['wage_gross'];
            $this->cols_fixed['tax_paid']['value'] = $calc['tax_paid'];
            $this->cols_fixed['wage_deduct']['value'] = $calc['wage_deduct'];
            $this->cols_fixed['wage_paid']['value'] = $calc['wage_paid'];
            $this->cols_fixed['wage_adjust']['value'] = $calc['wage_adjust'];
        }
    }

    public function modifyWageFile($format,&$message = '',&$error = '') 
    {
        //internal STANDARD representation
        $header = ['Staff_ID','First_name','Surname','Days_worked','Days_off','Days_sick','Days_funeral','Days_absent','Days_xtra',
                   'Advance_taken','Loan_taken','Saving_withdrawal','Loan_repaid','Bonus_paid'];
    
        //echo 'WTF file path: '.$this->file_path.'<br/>';
        //echo 'WTF file path mod: '.$this->file_path_mod.'<br/>';
        //exit;

        $handle_read = fopen($this->file_path,'r');
        $handle_write = fopen($this->file_path_mod,'w');
        
        //write first line of modified csv file
        fputcsv($handle_write,$header); 
        
        //get any company specific rules
        $company = Helpers::getCompany($this->db,COMPANY_ID);
        
        $message .= $company['name'].': CSV wages import.<br/>';
                        
        if($format === 'XXX') {
            $i=0;
            $v=0;
            while(($line = fgetcsv($handle_read,0,",")) !== FALSE) {
                $i++;
                $valid = false;
                
                //need at least three csv values in line to be valid
                if(count($line) > 3) {
                    $valid = true;  
                }  
                
                //get all values from file  and modify where nevcessary       
                if($valid) {
                     
                }
                
                //assign to STANDARD forma
                if($valid) { 
                    $v++;

                    $line_mod = [];
                    $line_mod[] = $staff_id;
                    $line_mod[] = $first_name;
                    $line_mod[] = $surname;
                    //......and so on
                    
                    fputcsv($handle_write,$line_mod);
    
                }  
            }
        }   
        
        //close bank file and converted/modified file
        fclose($handle_read);
        fclose($handle_write);

        //check if any valid lines found
        if($v === 0) $error = 'No valid data found in bank import file. Check file is for bank you selected?';
    }
    
}