<?php
namespace App\Payroll;

use Seriti\Tools\Table;
//use Seriti\Tools\Date;
//use Seriti\Tools\Form;
//use Seriti\Tools\Secure;

class StaffScale extends Table
{
    public function setup($param = []) 
    {
        $param = ['row_name'=>'Staff scale','row_name_plural'=>'Staff scales','col_label'=>'name','pop_up'=>false];
        parent::setup($param);

        //adds these values to any new staff scales
        $this->addColFixed(['id'=>'company_id','value'=>COMPANY_ID]);

        $this->addTableCol(['id'=>'scale_id','type'=>'INTEGER','title'=>'scale ID','key'=>true,'key_auto'=>true]);
        $this->addTableCol(['id'=>'name','type'=>'STRING','title'=>'Name']);
        $this->addTableCol(['id'=>'basis','type'=>'STRING','title'=>'Basis']);
        $this->addTableCol(['id'=>'position_level','type'=>'INTEGER','title'=>'Position level']);
        $this->addTableCol(['id'=>'wage','type'=>'DECIMAL','title'=>'Wage']);
        $this->addTableCol(['id'=>'status','type'=>'STRING','title'=>'Status']);
        $this->addTableCol(['id'=>'company_id','type'=>'INTEGER','title'=>'Company','join'=>'name FROM '.TABLE_PREFIX.'company WHERE company_id']);

        $this->addSql('WHERE','T.company_id = "'.COMPANY_ID.'"');

        $this->addSortOrder('T.scale_id DESC','Most recent first','DEFAULT');

        $this->addAction(['type'=>'edit','text'=>'edit','icon_text'=>'edit']);
        $this->addAction(['type'=>'delete','text'=>'delete','icon_text'=>'delete','pos'=>'R']);

        $this->addSearch(['scale_id','name','basis','position_level','wage','status','company_id'],['rows'=>1]);

        $this->addSelect('company_id','SELECT company_id, name FROM '.TABLE_PREFIX.'company ORDER BY name');
        $status = ['OK','HIDE'];
        $this->addSelect('status',['list'=>$status,'list_assoc'=>false]);

    }

    /*** EVENT PLACEHOLDER FUNCTIONS ***/
    //protected function beforeUpdate($id,$context,&$data,&$error) {}
    //protected function afterUpdate($id,$context,$data) {}
    //protected function beforeDelete($id,&$error) {}
    //protected function afterDelete($id) {}
    //protected function beforeValidate($col_id,&$value,&$error,$context) {}

}
