<?php 
namespace App\Payroll;

use App\Payroll\Helpers;
use Seriti\Tools\Form;
use Seriti\Tools\Task as SeritiTask;

class Task extends SeritiTask
{
    public function setup()
    {
        $param = array();
        $param['separator'] = true;

        $this->addBlock('COMPANY',1,1,'Manage Active Company');
        $this->addTask('COMPANY','EDIT_COMPANY','Edit <b>'.COMPANY_NAME.'</b> details');
        $this->addTask('COMPANY','IMPORT_WAGE','Import staff monthly wages CSV  statement');
        $this->addTask('COMPANY','STAFF_DEPARTMENTS','Manage company departments');
        $this->addTask('COMPANY','STAFF_POSITIONS','Manage company positions');
        $this->addTask('COMPANY','STAFF_SCALES','Manage company staff scales');
                
        $this->addBlock('MANAGE',2,1,'All Companies');
        $this->addTask('MANAGE','CHANGE_COMPANY','Change active Company');
        $this->addTask('MANAGE','ALL_COMPANIES','Manage ALL companies');
        $this->addTask('MANAGE','ADD_COMPANY','Add a new company');
        //$this->addTask('MANAGE','SETUP_COMPANY','Setup default department,positios,scales for a NEW company');
        //$this->addTask('MANAGE','COPY_COMPANY','Copy Company setup from an existing company');
    }

    function processTask($id,$param = []) {
        $error = '';
        $message = '';
        $n = 0;
        
        if($id === 'EDIT_COMPANY') {
            $location = 'company?mode=edit&id='.COMPANY_ID;
            header('location: '.$location);
            exit;
        }

        if($id === 'IMPORT_WAGE') {
            $location = 'import_wage';
            header('location: '.$location);
            exit;
        }
        
        if($id === 'ADD_COMPANY') {
            $location = 'company?mode=add';
            header('location: '.$location);
            exit;
        }
        
        if($id === 'ALL_COMPANIES') {
            $location = 'company';
            header('location: '.$location);
            exit;
        }

        if($id === 'STAFF_DEPARTMENTS') {
            $location = 'staff_department';
            header('location: '.$location);
            exit;
        }

        if($id === 'STAFF_POSITIONS') {
            $location = 'staff_position';
            header('location: '.$location);
            exit;
        }

        if($id === 'STAFF_SCALES') {
            $location = 'staff_scale';
            header('location: '.$location);
            exit;
        }
        
        //setup default accounts for a NEW company
        if($id === 'SETUP_COMPANY') {
            if(!isset($param['process'])) $param['process'] = false;  
            if(!isset($param['company_id'])) $param['company_id'] = '';
        
            if($param['process'] === 'setup') {
                Helpers::setupCompany($this->db,$param['company_id'],$error);
                if($error === '') {
                    $this->addMessage('SUCCESSFULY setup company defaults!');
                } else {
                    $this->addError($error);   
                }     
            } else {
                $sql = 'SELECT company_id,name FROM '.TABLE_PREFIX.'company ORDER BY name';
                $list_param = [];
                $list_param['class'] = 'form-control input-large';
            
                $html = '';
                $class = 'form-control input-small';
                $html .= 'Please select Company ID that you wish to create default settings for.<br/>'.
                         '<form method="post" action="?mode=task&id='.$id.'" enctype="multipart/form-data">'.
                         '<input type="hidden" name="process" value="setup"><br/>'.
                         'Select Company: '.Form::sqlList($sql,$this->db,'company_id',$param['company_id'],$list_param).
                         '<input type="submit" name="submit" value="SETUP ACCOUNTS" class="'.$this->classes['button'].'">'.
                         '</form>';

                //display form in message box       
                $this->addMessage($html);      
            }  
        }
        
        if($id === 'CHANGE_COMPANY') {
            if(!isset($param['process'])) $param['process'] = false;  
            if(!isset($param['company_id'])) $param['company_id'] = '';
        
            if($param['process'] === 'change') {
                $cache = $this->getContainer('cache');  
                $company_id = $param['company_id']; 
                $cache->store('company_id',$company_id);      
        
                $location = 'dashboard';
                header('location: '.$location);
                exit;             
            } else {
                $sql = 'SELECT company_id,name FROM '.TABLE_PREFIX.'company ORDER BY name';
                $list_param = array();
                $list_param['class'] = 'form-control input-large';
            
                $html = '';
                $class = 'form-control input-small';
                $html .= 'Please select Company that you wish to work on.<br/>'.
                         '<form method="post" action="?mode=task&id='.$id.'" enctype="multipart/form-data">'.
                         '<input type="hidden" name="process" value="change"><br/>'.
                         'Select Company: '.Form::sqlList($sql,$this->db,'company_id',$param['company_id'],$list_param).
                         '<input type="submit" name="submit" value="CHANGE ACTIVE" class="'.$this->classes['button'].'">'.
                         '</form>'; 
                //display form in message box       
                $this->addMessage($html);      
            }  
        } 
        
            
    }
}
?>
                                                
