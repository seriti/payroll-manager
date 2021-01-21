<?php
namespace App\Payroll;

use Seriti\Tools\Wizard;
use Seriti\Tools\Date;
use Seriti\Tools\Form;
use Seriti\Tools\Doc;
use Seriti\Tools\Calc;
use Seriti\Tools\Import;

use Seriti\Tools\STORAGE;
use Seriti\Tools\BASE_UPLOAD;
use Seriti\Tools\UPLOAD_TEMP;
use Seriti\Tools\UPLOAD_DOCS;

use App\Payroll\WageImport;
use App\Payroll\Helpers;

class WageImportWizard extends Wizard 
{
    //csv import object
    protected $import; 


    protected $upload_dir;
    //path to bank import file    
    protected $file_path; 
    //path to modified bank file to match internal representation
    protected $file_path_mod;
    protected $transact_type;

    //assign import class
    public function addImport(Import $import) {
        $this->import = $import;
    } 

    //configure
    public function setup($param = []) 
    {
        //var_dump($this->container);
        //exit; 

        $param = ['bread_crumbs'=>true,'strict_var'=>false];
        parent::setup($param);

        //define all wizard variables to be captured and stored for all wizard pages
        $this->addVariable(array('id'=>'import_format','type'=>'STRING','title'=>'Wage import format'));
        $this->addVariable(array('id'=>'wage_month','type'=>'INTEGER','title'=>'Import for month'));
        $this->addVariable(array('id'=>'wage_year','type'=>'INTEGER','title'=>'Import for year'));
        $this->addVariable(array('id'=>'days_in_month','type'=>'INTEGER','min'=>1,'max'=>31,'title'=>'Days in month'));
        $this->addVariable(array('id'=>'data_file','type'=>'FILE','title'=>'Import file'));
        $this->addVariable(array('id'=>'ignore_errors','type'=>'BOOLEAN','title'=>'Ignore errors in import file','new'=>false));
        
        //define pages and templates
        $this->addPage(1,'Select Wage month and file','payroll/wage_wizard_start.php');
        $this->addPage(2,'Review import data','payroll/wage_wizard_review.php');
        $this->addPage(3,'Confirmation page','payroll/wage_wizard_final.php',['final'=>true]);

    }

    

    public function processPage() 
    {
        $error = '';
        $message = '';
        $error_tmp = '';

        //upload bank file and display allocations for review
        if($this->page_no == 1) {
            $import_format = $this->form['import_format'];
            $wage_month = $this->form['wage_month'];
            $wage_year = $this->form['wage_year'];
            $ignore_errors = $this->form['ignore_errors'];

            //debug
            //echo '<br/>*************<br/>';
            //var_dump($this->form);
            //var_dump($this->container);
           // exit; 
            
            $modify_file = false;
            if($import_format !== 'STANDARD') {
                $modify_file = true;
            }
            
            $table_staff = TABLE_PREFIX.'staff';
            $table_wage = TABLE_PREFIX.'staff_wage';


            $sql = 'SELECT COUNT(*) FROM '.$table_wage.' AS W JOIN '.$table_staff.' AS S ON(W.staff_id = S.staff_id) '.
                   'WHERE company_id = "'.COMPANY_ID.'" AND '.
                         'month = "'.$this->db->escapeSql($wage_month).'" AND '.
                         'year = "'.$this->db->escapeSql($wage_year).'" ';
            $count = $this->db->readSqlValue($sql); 
            if($count != 0) {
                $this->addMessage('There are existing wage records for '.COMPANY_NAME.'! Proceed with caution');
            } else {  
                
            }


            if($error !== '') {
                $this->addError($error);
            } else {
                //configure CSV import object
                $param = [];
                $param['user_id'] = $this->getContainer('user')->getId();
                $param['wage_month'] = $this->form['wage_month'];
                $param['wage_year'] = $this->form['wage_year'];
                $param['days_in_month'] = $this->form['days_in_month'];
                $param['ignore_errors'] = $this->form['ignore_errors'];
                
                //NB:Should only be one file
                $file = $this->form['data_file'][0];
                $param['file_path'] = $file['save_path'];
                $param['file_path_mod'] = BASE_UPLOAD.UPLOAD_TEMP.$file['save_name'];
                $this->import->Setup($param);

                //modify wage file format to generic import format
                if($modify_file) {
                    $this->import->modifyWageFile($import_format,$message,$error);
                    if($message !== '') $this->addMessage($message);
                    if($error !== '') {
                        $this->addError($error);    
                    } else {
                        $this->import->useModifiedFile();
                    }    
                }
                

                //check that staff id matches names in file
                $this->import->checkStaffNames();

                //merge any import errors with wizard errors
                $errors = $this->import->getErrors(); 
                if(count($errors)) {
                    $this->errors_found = true; 
                    $this->errors = array_merge($this->errors,$errors);
                }

                //generate confirmation form 
                if(!$this->errors_found) {
                    
                    $param = [];
                    $this->data['transact_type'] = $transact_type;
                    $this->data['confirm_form'] = $this->import->viewConfirm('CSV',$param,$error);
                    if($error !== '') $this->addError($error,false);
                }    
            }    
            
        } 
        
        if($this->page_no == 2) {
            //configure CSV import object
            $param = [];
            $param['user_id'] = $this->getContainer('user')->getId();
            $param['wage_month'] = $this->form['wage_month'];
            $param['wage_year'] = $this->form['wage_year'];
            $param['days_in_month'] = $this->form['days_in_month'];
            $param['ignore_errors'] = $this->form['ignore_errors'];
            
            //NB:Should only be one file
            $file = $this->form['data_file'][0];
            $param['file_path'] = $file['save_path'];
            $param['file_path_mod'] = BASE_UPLOAD.UPLOAD_TEMP.$file['save_name'];
            $this->import->Setup($param);

            $import_data = [];
            $data_type = 'CONFIRM_FORM'; //could use 'CSV' and modified file as above if no confirmation necesary
            $this->import->createDataArray($data_type,$import_data);
            //print_r($import_data);

            $errors = $this->import->getErrors();
            if(count($errors) == 0) {
                $this->import->importDataArray($import_data);
                $errors = $this->import->getErrors();
            }

            $messages = $this->import->getMessages();
            $this->messages = array_merge($this->messages,$messages);

            if(count($errors)) {
                $this->errors_found = true; 
                $this->errors = array_merge($this->errors,$errors);
            } else {
                //for display on final page
                $this->data['import_data'] = $import_data;
            }
        }  
        
        //no processing required for final page
        if($this->page_no == 3) {
          
        } 
    }

}

?>


