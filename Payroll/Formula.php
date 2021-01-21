<?php
namespace App\Payroll;

use Exception;
use Seriti\Tools\Calc;
use Seriti\Tools\Date;

//use this class to define all wage calculation formula available for your specific use case. Referenced in WageImport.php and Staff.php 
class Formula {
   
    public static function getList($type) {
        $list = [];

        if($type == 'TAX') $list = ['MALAWI_PAYE'=>'Malawi PAYE','MALAWI_WHT'=>'Malawi Witholding tax'];
        
        if($type == 'PENSION') $list = ['STANDARD'=>'Standard Pension'];
        
        return $list;    
    }    
    
    public static function calcWageTax($formula,$salary) {
        $tax = 0;
      
      
        if($formula === 'MALAWI_PAYE') {
            if($salary >= 3000000) {
                $tax = ($salary - 3000000) * 0.35 + 2950000 * 0.30 + 5000 * 0.15;
            } elseif($salary >= 50000) { 
                $tax = ($salary - 50000) * 0.30 + 5000 * 0.15;
            } elseif($salary >= 45000) {
                $tax = ($salary - 45000) * 0.15;
            }
        }

        if($formula === 'MALAWI_WHT') {
            if($salary >= 25000) {
                $tax = ($salary - 25000) * 0.2;
            }    
        }     

      
        $tax = round($tax,0);
      
        return $tax;
    }  

    public static function calcPension($formula,$salary) {
        $pension = [];
        
        if($formula === 'STANDARD') {
            $pension['staff'] = round($salary * 0.05,2);
            $pension['company'] = round($salary * 0.10,2);
            $pension['life'] = round($salary * 0.0135,2);
            $pension['admin'] = round($salary * 0.0035,2);
            $pension['total'] = round($pension['staff'] + $pension['company'] + $pension['life'] + $pension['admin'],2);  
        }
        
        
        return $pension;
    } 
    
      
    
}
?>
