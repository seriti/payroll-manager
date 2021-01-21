<?php
namespace App\Payroll;

use Seriti\Tools\Table;
//use Seriti\Tools\Date;
//use Seriti\Tools\Form;
//use Seriti\Tools\Secure;

class StaffPosition extends Table
{
    public function setup($param = []) 
    {
        $param = ['row_name'=>'Staff position','row_name_plural'=>'Staff positions','col_label'=>'name','pop_up'=>false];
        parent::setup($param);

        //adds these values to any new positions
        $this->addColFixed(['id'=>'company_id','value'=>COMPANY_ID]);

        $this->addTableCol(['id'=>'position_id','type'=>'INTEGER','title'=>'position ID','key'=>true,'key_auto'=>true]);
        $this->addTableCol(['id'=>'name','type'=>'STRING','title'=>'Name']);
        $this->addTableCol(['id'=>'rank','type'=>'INTEGER','title'=>'Rank']);
        $this->addTableCol(['id'=>'status','type'=>'STRING','title'=>'Status']);
        $this->addTableCol(['id'=>'level','type'=>'INTEGER','title'=>'Level']);
        $this->addTableCol(['id'=>'company_id','type'=>'INTEGER','title'=>'Company','join'=>'name FROM '.TABLE_PREFIX.'company WHERE company_id']);

        $this->addSql('WHERE','T.company_id = "'.COMPANY_ID.'"');

        $this->addSortOrder('T.position_id DESC','Most recent first','DEFAULT');

        $this->addAction(['type'=>'edit','text'=>'edit','icon_text'=>'edit']);
        $this->addAction(['type'=>'delete','text'=>'delete','icon_text'=>'delete','pos'=>'R']);

        $this->addSearch(['position_id','name','rank','status','level','company_id'],['rows'=>1]);

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
