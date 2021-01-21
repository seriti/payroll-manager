<?php
namespace App\Payroll;

use Seriti\Tools\Dashboard AS DashboardTool;

class Dashboard extends DashboardTool
{
     

    //configure
    public function setup($param = []) 
    {
        $this->col_count = 2;  

        $login_user = $this->getContainer('user'); 

        //(block_id,col,row,title)
        $this->addBlock('ADD',1,1,'Capture new data');
        $this->addItem('ADD','Add a new staff member',['link'=>"staff?mode=add"]);

        $this->addBlock('DATA',2,1,'Data interfaces');
        $this->addItem('DATA','Import staff wages from Excel/Csv',['link'=>"import_wage"]);
        
        if($login_user->getAccessLevel() === 'GOD') {
            $this->addBlock('CONFIG',1,2,'Module Configuration');
            $this->addItem('CONFIG','Setup Database',['link'=>'setup_data','icon'=>'setup']);
        }    
        
    }

}

?>