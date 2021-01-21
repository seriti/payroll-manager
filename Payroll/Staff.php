<?php
namespace App\Payroll;

use Seriti\Tools\Table;
//use Seriti\Tools\Date;
//use Seriti\Tools\Form;
//use Seriti\Tools\Secure;

class Staff extends Table
{
    public function setup($param = []) 
    {
        $param = ['row_name'=>'Staff member','col_label'=>'first_name','pop_up'=>false];
        parent::setup($param);

        //adds these values to any new staff
        $this->addColFixed(['id'=>'company_id','value'=>COMPANY_ID]);

        $this->addTableCol(['id'=>'staff_id','type'=>'INTEGER','title'=>'staff ID','key'=>true,'key_auto'=>true]);
        $this->addTableCol(['id'=>'first_name','type'=>'STRING','title'=>'First name']);
        $this->addTableCol(['id'=>'surname','type'=>'STRING','title'=>'Surname']);
        $this->addTableCol(['id'=>'gender','type'=>'STRING','title'=>'Gender','new'=>'Male']);
        $this->addTableCol(['id'=>'date_of_birth','type'=>'DATE','title'=>'Date of birth','new'=>date('Y-m-d')]);
        $this->addTableCol(['id'=>'service_start','type'=>'DATE','title'=>'Service start','new'=>date('Y-m-d')]);
        $this->addTableCol(['id'=>'active','type'=>'BOOLEAN','title'=>'Active']);
        $this->addTableCol(['id'=>'department_id','type'=>'INTEGER','title'=>'Department','join'=>'name FROM '.TABLE_PREFIX.'staff_department WHERE department_id']);
        $this->addTableCol(['id'=>'position_id','type'=>'INTEGER','title'=>'Position','join'=>'name FROM '.TABLE_PREFIX.'staff_position WHERE position_id']);
        $this->addTableCol(['id'=>'tip_star_rating','type'=>'INTEGER','title'=>'Tip star rating']);
        $this->addTableCol(['id'=>'scale_id','type'=>'INTEGER','title'=>'Scale','join'=>'name FROM '.TABLE_PREFIX.'staff_scale WHERE scale_id']);
        $this->addTableCol(['id'=>'wage_basis','type'=>'STRING','title'=>'Wage basis']);
        $this->addTableCol(['id'=>'gross_wage','type'=>'DECIMAL','title'=>'Gross wage']);
        //$this->addTableCol(['id'=>'tax_basis','type'=>'STRING','title'=>'Tax basis','new'=>'NONE']);
        $this->addTableCol(['id'=>'tax_formula','type'=>'STRING','title'=>'Tax formula','new'=>'NONE']);
        $this->addTableCol(['id'=>'saving_amount','type'=>'DECIMAL','title'=>'Saving amount','new'=>'0.00']);
        $this->addTableCol(['id'=>'pension_active','type'=>'BOOLEAN','title'=>'Pension active']);
        $this->addTableCol(['id'=>'pension_no','type'=>'STRING','title'=>'Pension no','new'=>'NONE']);
        $this->addTableCol(['id'=>'pension_start_date','type'=>'DATE','title'=>'Pension start date','new'=>date('Y-m-d')]);
        $this->addTableCol(['id'=>'pension_end_date','type'=>'DATE','title'=>'Pension end date','new'=>date('Y-m-d')]);
        $this->addTableCol(['id'=>'pension_formula','type'=>'STRING','title'=>'Pension formula','new'=>'NONE']);
        
        $this->addSql('WHERE','T.company_id = "'.COMPANY_ID.'"');

        $this->addSortOrder('T.staff_id DESC','Most recent first','DEFAULT');

        $this->addAction(['type'=>'edit','text'=>'edit','icon_text'=>'edit']);
        $this->addAction(['type'=>'delete','text'=>'delete','icon_text'=>'delete','pos'=>'R']);
        $this->addAction(['type'=>'popup','text'=>'Notes','url'=>'staff_note','mode'=>'view','width'=>600,'height'=>600]);

        $this->addSearch(['staff_id','first_name','surname','gender','date_of_birth','service_start','department_id',
                          'position_id','tip_star_rating','scale_id','wage_basis','gross_wage','tax_basis','tax_formula',
                          'saving_amount','pension_no','pension_start_date','pension_end_date','pension_formula','pension_active',
                          'company_id','active'],['rows'=>6]);

        $this->addSelect('department_id','SELECT department_id, name FROM '.TABLE_PREFIX.'staff_department ORDER BY name');
        $this->addSelect('position_id','SELECT position_id, name FROM '.TABLE_PREFIX.'staff_position ORDER BY name');
        $this->addSelect('scale_id','SELECT scale_id, name FROM '.TABLE_PREFIX.'staff_scale ORDER BY name');
        $this->addSelect('company_id','SELECT company_id, name FROM '.TABLE_PREFIX.'company ORDER BY name');
        $this->addSelect('wage_basis',['list'=>WAGE_BASIS]);
        $this->addSelect('tax_formula',['list'=>Formula::getList('TAX')]);
        $this->addSelect('pension_formula',['list'=>Formula::getList('PENSION')]);

        $this->setupFiles(['table'=>TABLE_PREFIX.'file','location'=>'STF','max_no'=>100,
                           'icon'=>'<span class="glyphicon glyphicon-file" aria-hidden="true"></span>&nbsp;manage',
                           'list'=>true,'list_no'=>5,'storage'=>STORAGE,
                           'link_url'=>'staff_file','link_data'=>'SIMPLE','width'=>'700','height'=>'600']);

    }

    /*** EVENT PLACEHOLDER FUNCTIONS ***/
    //protected function beforeUpdate($id,$context,&$data,&$error) {}
    //protected function afterUpdate($id,$context,$data) {}
    //protected function beforeDelete($id,&$error) {}
    //protected function afterDelete($id) {}
    //protected function beforeValidate($col_id,&$value,&$error,$context) {}

}
