<?php
namespace App\Payroll;

use Seriti\Tools\Table;
//use Seriti\Tools\Date;
//use Seriti\Tools\Form;
//use Seriti\Tools\Secure;

class CompanyNote extends Table
{
    public function setup($param = []) 
    {
        $param = ['row_name'=>'Note','row_name_plural'=>'Notes','col_label'=>'name','pop_up'=>true];
        parent::setup($param);

        $this->setupMaster(['table'=>TABLE_PREFIX.'company','key'=>'company_id','child_col'=>'location_id','child_prefix'=>'CMP',
                            'show_sql'=>'SELECT CONCAT("Company: ",name) FROM '.TABLE_PREFIX.'company WHERE company_id = "{KEY_VAL}" ']);

        $this->addTableCol(['id'=>'note_id','type'=>'INTEGER','title'=>'note ID','key'=>true,'key_auto'=>true,'list'=>false]);
        $this->addTableCol(['id'=>'date_create','type'=>'DATE','title'=>'Date create','edit'=>false]);
        $this->addTableCol(['id'=>'note','type'=>'TEXT','title'=>'Note','rows'=>12,'required'=>false]);
        $this->addTableCol(['id'=>'status','type'=>'STRING','title'=>'Status']);

        $this->addSortOrder('T.note_id DESC','Most recent first','DEFAULT');

        $this->addAction(['type'=>'edit','text'=>'edit','icon_text'=>'edit']);
        $this->addAction(['type'=>'delete','text'=>'delete','icon_text'=>'delete','pos'=>'R']);

        $this->addSearch(['date_create','note','status'],['rows'=>2]);

        $status = ['OK','HIDE'];
        $this->addSelect('status',['list'=>$status,'list_assoc'=>false]);
        
    }

    /*** EVENT PLACEHOLDER FUNCTIONS ***/
    //protected function beforeUpdate($id,$context,&$data,&$error) {}
    protected function afterUpdate($id,$context,$data) 
    {
        if($context === 'INSERT') {
            $sql = 'UPDATE '.$this->table.' SET date_create = CURDATE() '.
                   'WHERE '.$this->key['id'].' = "'.$this->db->escapeSql($id).'" ';
            $this->db->executeSql($sql,$error_tmp);
        }
    }
    //protected function beforeDelete($id,&$error) {}
    //protected function afterDelete($id) {}
    //protected function beforeValidate($col_id,&$value,&$error,$context) {}

}
