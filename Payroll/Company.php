<?php
namespace App\Payroll;

use Seriti\Tools\Table;
//use Seriti\Tools\Date;
//use Seriti\Tools\Form;
//use Seriti\Tools\Secure;

class Company extends Table
{
    public function setup($param = []) 
    {
        $param = ['row_name'=>'Company','row_name_plural'=>'Companies','col_label'=>'name','pop_up'=>false];
        parent::setup($param);

        $this->addTableCol(['id'=>'company_id','type'=>'INTEGER','title'=>'company ID','key'=>true,'key_auto'=>true]);
        $this->addTableCol(['id'=>'name','type'=>'STRING','title'=>'Name']);
        $this->addTableCol(['id'=>'address_postal','type'=>'TEXT','title'=>'Address postal','required'=>false]);
        $this->addTableCol(['id'=>'address_physical','type'=>'TEXT','title'=>'Address physical','required'=>false]);
        $this->addTableCol(['id'=>'contact','type'=>'STRING','title'=>'Contact','required'=>false]);
        $this->addTableCol(['id'=>'tel','type'=>'STRING','title'=>'Tel','required'=>false]);
        $this->addTableCol(['id'=>'fax','type'=>'STRING','title'=>'Fax','required'=>false]);
        $this->addTableCol(['id'=>'cell','type'=>'STRING','title'=>'Cell','required'=>false]);
        $this->addTableCol(['id'=>'email','type'=>'EMAIL','title'=>'Email','required'=>false]);
        $this->addTableCol(['id'=>'tax_no','type'=>'STRING','title'=>'Tax no','required'=>false]);
        $this->addTableCol(['id'=>'reg_no','type'=>'STRING','title'=>'Reg no','required'=>false]);
        $this->addTableCol(['id'=>'vat_reg','type'=>'BOOLEAN','title'=>'Vat reg','required'=>false]);
        $this->addTableCol(['id'=>'vat_no','type'=>'STRING','title'=>'Vat no','required'=>false]);
        $this->addTableCol(['id'=>'status','type'=>'STRING','title'=>'Status']);


        $this->addSortOrder('T.company_id DESC','Most recent first','DEFAULT');

        $this->addAction(['type'=>'edit','text'=>'edit','icon_text'=>'edit']);
        $this->addAction(['type'=>'delete','text'=>'delete','icon_text'=>'delete','pos'=>'R']);
        $this->addAction(['type'=>'popup','text'=>'Notes','url'=>'company_note','mode'=>'view','width'=>600,'height'=>600]);

        $this->addSearch(['company_id','name','address_postal','address_physical','contact',
                          'tel','fax','cell','email','tax_no','reg_no','vat_reg','vat_no','status'],['rows'=>5]);

        $status = ['OK','HIDE'];
        $this->addSelect('status',['list'=>$status,'list_assoc'=>false]);

        $this->setupFiles(['table'=>TABLE_PREFIX.'file','location'=>'COM','max_no'=>100,
                           'icon'=>'<span class="glyphicon glyphicon-file" aria-hidden="true"></span>&nbsp;manage',
                           'list'=>true,'list_no'=>5,'storage'=>STORAGE,
                           'link_url'=>'company_file','link_data'=>'SIMPLE','width'=>'700','height'=>'600']);

    }

    /*** EVENT PLACEHOLDER FUNCTIONS ***/
    //protected function beforeUpdate($id,$context,&$data,&$error) {}
    //protected function afterUpdate($id,$context,$data) {}
    //protected function beforeDelete($id,&$error) {}
    //protected function afterDelete($id) {}
    //protected function beforeValidate($col_id,&$value,&$error,$context) {}

}
