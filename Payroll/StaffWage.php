<?php
namespace App\Payroll;

use Seriti\Tools\Table;
//use Seriti\Tools\Date;
//use Seriti\Tools\Form;
//use Seriti\Tools\Secure;

class StaffWage extends Table
{
    public function setup($param = []) 
    {
        $param = ['row_name'=>'Staff wage','row_name_plural'=>'Staff wages','col_label'=>'name','pop_up'=>false];
        parent::setup($param);

        $this->addTableCol(['id'=>'wage_id','type'=>'INTEGER','title'=>'wage ID','key'=>true,'key_auto'=>true,'list'=>false]);
        $this->addTableCol(['id'=>'month','type'=>'INTEGER','title'=>'Month']);
        $this->addTableCol(['id'=>'year','type'=>'INTEGER','title'=>'Year']);
        $this->addTableCol(['id'=>'staff_id','type'=>'INTEGER','title'=>'Staff member',
                            'join'=>'CONCAT(surname,", ",first_name) FROM '.TABLE_PREFIX.'staff WHERE staff_id']);
        
        $this->addTableCol(['id'=>'days_worked','type'=>'INTEGER','title'=>'Days worked']);
        $this->addTableCol(['id'=>'days_off','type'=>'INTEGER','title'=>'Days off']);
        $this->addTableCol(['id'=>'days_sick','type'=>'INTEGER','title'=>'Days sick']);
        $this->addTableCol(['id'=>'days_funeral','type'=>'INTEGER','title'=>'Days funeral']);
        $this->addTableCol(['id'=>'days_absent','type'=>'INTEGER','title'=>'Days absent']);
        $this->addTableCol(['id'=>'days_xtra','type'=>'INTEGER','title'=>'Days xtra']);
        
        $this->addTableCol(['id'=>'wage_monthly','type'=>'DECIMAL','title'=>'Wage monthly']);
        $this->addTableCol(['id'=>'wage_xtra','type'=>'DECIMAL','title'=>'Wage xtra']);
        $this->addTableCol(['id'=>'wage_bonus','type'=>'DECIMAL','title'=>'Wage bonus']);
        $this->addTableCol(['id'=>'wage_adjust','type'=>'DECIMAL','title'=>'Wage adjust']);
        $this->addTableCol(['id'=>'wage_gross','type'=>'DECIMAL','title'=>'Wage gross']);
        $this->addTableCol(['id'=>'wage_deduct','type'=>'DECIMAL','title'=>'Wage deduct']);
        $this->addTableCol(['id'=>'wage_paid','type'=>'DECIMAL','title'=>'Wage paid']);

        $this->addTableCol(['id'=>'advance_taken','type'=>'DECIMAL','title'=>'Advance taken']);
        $this->addTableCol(['id'=>'advance_paid','type'=>'DECIMAL','title'=>'Advance paid']);
        
        $this->addTableCol(['id'=>'tax_paid','type'=>'DECIMAL','title'=>'Tax paid']);
                
        $this->addTableCol(['id'=>'loan_start','type'=>'DECIMAL','title'=>'Loan start']);
        $this->addTableCol(['id'=>'loan_taken','type'=>'DECIMAL','title'=>'Loan taken']);
        $this->addTableCol(['id'=>'loan_paid','type'=>'DECIMAL','title'=>'Loan paid']);
        $this->addTableCol(['id'=>'loan_end','type'=>'DECIMAL','title'=>'Loan end']);
        
        $this->addTableCol(['id'=>'savings_start','type'=>'DECIMAL','title'=>'Savings start']);
        $this->addTableCol(['id'=>'savings_taken','type'=>'DECIMAL','title'=>'Savings taken']);
        $this->addTableCol(['id'=>'savings_paid','type'=>'DECIMAL','title'=>'Savings paid']);
        $this->addTableCol(['id'=>'savings_end','type'=>'DECIMAL','title'=>'Savings end']);
        
        $this->addTableCol(['id'=>'pension_paid','type'=>'DECIMAL','title'=>'Pension paid']);
        $this->addTableCol(['id'=>'pension_staff','type'=>'DECIMAL','title'=>'Pension staff']);
        $this->addTableCol(['id'=>'pension_company','type'=>'DECIMAL','title'=>'Pension company']);
        $this->addTableCol(['id'=>'pension_life','type'=>'DECIMAL','title'=>'Pension life']);
        $this->addTableCol(['id'=>'pension_admin','type'=>'DECIMAL','title'=>'Pension admin']);

        $this->addSql('JOIN','JOIN '.TABLE_PREFIX.'staff AS S ON(T.staff_id = S.staff_id)');
        $this->addSql('WHERE','S.company_id = "'.COMPANY_ID.'"');

        $this->addSortOrder('T.wage_id DESC','Most recent first','DEFAULT');

        $this->addAction(['type'=>'check_box','text'=>'']);
        $this->addAction(['type'=>'edit','text'=>'edit','icon_text'=>'edit']);
        $this->addAction(['type'=>'delete','text'=>'delete','icon_text'=>'delete','pos'=>'R']);

        $this->addSearch(['staff_id','month','year','days_worked','days_off','days_sick','days_funeral','days_absent','days_xtra','loan_taken','advance_taken','savings_taken','wage_monthly','wage_xtra','wage_bonus','tax_paid','savings_paid','pension_paid','loan_start','loan_end','wage_adjust','wage_gross','wage_deduct','wage_paid','advance_paid','savings_start','savings_end','loan_paid','pension_staff','pension_company','pension_life','pension_admin'],['rows'=>8]);

        $this->addSelect('staff_id','SELECT staff_id,CONCAT(surname,", ",first_name) FROM '.TABLE_PREFIX.'staff ORDER BY surname,first_name');

    }

    /*** EVENT PLACEHOLDER FUNCTIONS ***/
    //protected function beforeUpdate($id,$context,&$data,&$error) {}
    //protected function afterUpdate($id,$context,$data) {}
    //protected function beforeDelete($id,&$error) {}
    //protected function afterDelete($id) {}
    //protected function beforeValidate($col_id,&$value,&$error,$context) {}

}
