<?php
namespace App\Payroll;

use Seriti\Tools\SetupModuleData;

class SetupData extends SetupModuledata
{

    public function setupSql()
    {
        $this->tables = ['staff','staff_department','staff_position','staff_scale','staff_wage','company','note','file'];

        $this->addCreateSql('staff',
                            'CREATE TABLE `TABLE_NAME` (
                            `staff_id` int(11) NOT NULL AUTO_INCREMENT,
                            `first_name` varchar(64) NOT NULL,
                            `surname` varchar(64) NOT NULL,
                            `gender` char(1) NOT NULL,
                            `date_of_birth` date NOT NULL,
                            `service_start` date NOT NULL,
                            `department_id` int(11) NOT NULL,
                            `position_id` int(11) NOT NULL,
                            `tip_star_rating` int(11) NOT NULL,
                            `scale_id` int(11) NOT NULL,
                            `wage_basis` varchar(16) NOT NULL,
                            `gross_wage` decimal(12,2) NOT NULL,
                            `tax_basis` varchar(16) NOT NULL,
                            `tax_formula` varchar(16) NOT NULL,
                            `saving_amount` decimal(12,2) NOT NULL,
                            `pension_no` varchar(64) NOT NULL,
                            `pension_start_date` date NOT NULL,
                            `pension_end_date` date NOT NULL,
                            `pension_formula` varchar(16) NOT NULL,
                            `pension_active` tinyint(1) NOT NULL,
                            `company_id` int(11) NOT NULL,
                            `active` tinyint(1) NOT NULL,
                            PRIMARY KEY (`staff_id`)
                          ) ENGINE=MyISAM DEFAULT CHARSET=utf8'); 

        $this->addCreateSql('staff_department',
                            'CREATE TABLE `TABLE_NAME` (
                                `department_id` int(11) NOT NULL AUTO_INCREMENT,
                                `name` varchar(250) NOT NULL,
                                `rank` int(11) NOT NULL,
                                `status` varchar(16) NOT NULL,
                                `company_id` int(11) NOT NULL,
                                PRIMARY KEY (`department_id`)
                            ) ENGINE=MyISAM DEFAULT CHARSET=utf8'); 

        $this->addCreateSql('staff_position',
                            'CREATE TABLE `TABLE_NAME` (
                                `position_id` int(11) NOT NULL AUTO_INCREMENT,
                                `name` varchar(250) NOT NULL,
                                `rank` int(11) NOT NULL,
                                `status` varchar(16) NOT NULL,
                                `level` int(11) NOT NULL,
                                `company_id` int(11) NOT NULL,
                                PRIMARY KEY (`position_id`)
                            ) ENGINE=MyISAM DEFAULT CHARSET=utf8'); 

        $this->addCreateSql('staff_scale',
                            'CREATE TABLE `TABLE_NAME` (
                                `scale_id` int(11) NOT NULL AUTO_INCREMENT,
                                `name` varchar(250) NOT NULL,
                                `basis` varchar(16) NOT NULL,
                                `position_level` int(11) NOT NULL,
                                `wage` decimal(12,2) NOT NULL,
                                `status` varchar(16) NOT NULL,
                                `company_id` int(11) NOT NULL,
                                PRIMARY KEY (`scale_id`)
                            ) ENGINE=MyISAM DEFAULT CHARSET=utf8'); 

        $this->addCreateSql('staff_wage',
                            'CREATE TABLE `TABLE_NAME` (
                                `wage_id` int(11) NOT NULL AUTO_INCREMENT,
                                `month` int(11) NOT NULL,
                                `year` int(11) NOT NULL,
                                `staff_id` int(11) NOT NULL,
                                `days_worked` int(11) NOT NULL,
                                `days_off` int(11) NOT NULL,
                                `days_sick` int(11) NOT NULL,
                                `days_funeral` int(11) NOT NULL,
                                `days_absent` int(11) NOT NULL,
                                `days_xtra` int(11) NOT NULL,
                                `loan_taken` decimal(12,2) NOT NULL,
                                `advance_taken` decimal(12,2) NOT NULL,
                                `savings_taken` decimal(12,2) NOT NULL,
                                `wage_monthly` decimal(12,2) NOT NULL,
                                `wage_xtra` decimal(12,2) NOT NULL,
                                `wage_bonus` decimal(12,2) NOT NULL,
                                `tax_paid` decimal(12,2) NOT NULL,
                                `savings_paid` decimal(12,2) NOT NULL,
                                `pension_paid` decimal(12,2) NOT NULL,
                                `loan_start` decimal(12,2) NOT NULL,
                                `loan_end` decimal(12,2) NOT NULL,
                                `wage_adjust` decimal(12,2) NOT NULL,
                                `wage_gross` decimal(12,2) NOT NULL,
                                `wage_deduct` decimal(12,2) NOT NULL,
                                `wage_paid` decimal(12,2) NOT NULL,
                                `advance_paid` decimal(12,2) NOT NULL,
                                `savings_start` decimal(12,2) NOT NULL,
                                `savings_end` decimal(12,2) NOT NULL,
                                `loan_paid` decimal(12,2) NOT NULL,
                                `pension_staff` decimal(12,2) NOT NULL,
                                `pension_company` decimal(12,2) NOT NULL,
                                `pension_life` decimal(12,2) NOT NULL,
                                `pension_admin` decimal(12,2) NOT NULL,
                                `user_id` int(11) NOT NULL,
                                `date_create` date NOT NULL DEFAULT \'0000-00-00\',
                                PRIMARY KEY (`wage_id`),
                                UNIQUE KEY `period_idx` (`month`,`year`,`staff_id`)
                            ) ENGINE=MyISAM DEFAULT CHARSET=utf8'); 

        $this->addCreateSql('company',
                            'CREATE TABLE `TABLE_NAME` (
                                `company_id` int(11) NOT NULL AUTO_INCREMENT,
                                `name` varchar(250) NOT NULL,
                                `address_postal` text NOT NULL,
                                `address_physical` text NOT NULL,
                                `contact` varchar(64) NOT NULL,
                                `tel` varchar(64) NOT NULL,
                                `fax` varchar(64) NOT NULL,
                                `cell` varchar(64) NOT NULL,
                                `email` varchar(250) NOT NULL,
                                `tax_no` varchar(64) NOT NULL,
                                `reg_no` varchar(64) NOT NULL,
                                `vat_reg` tinyint(1) NOT NULL,
                                `vat_no` varchar(64) NOT NULL,
                                `status` varchar(16) NOT NULL,
                                PRIMARY KEY (`company_id`)
                            ) ENGINE=MyISAM DEFAULT CHARSET=utf8'); 

        $this->addCreateSql('note',
                            'CREATE TABLE `TABLE_NAME` (
                                `note_id` int(11) NOT NULL AUTO_INCREMENT,
                                `location_id` varchar(64) NOT NULL,
                                `date_create` date NOT NULL,
                                `note` text NOT NULL,
                                `status` varchar(16) NOT NULL,
                                PRIMARY KEY (`note_id`),
                                KEY `idx_prl_note1` (`location_id`) 
                            ) ENGINE=MyISAM DEFAULT CHARSET=utf8'); 
        
        $this->addCreateSql('file',
                            'CREATE TABLE `TABLE_NAME` (
                              `file_id` int(10) unsigned NOT NULL,
                              `title` varchar(255) NOT NULL,
                              `file_name` varchar(255) NOT NULL,
                              `file_name_orig` varchar(255) NOT NULL,
                              `file_text` longtext NOT NULL,
                              `file_date` date NOT NULL DEFAULT \'0000-00-00\',
                              `location_id` varchar(64) NOT NULL,
                              `location_rank` int(11) NOT NULL,
                              `key_words` text NOT NULL,
                              `description` text NOT NULL,
                              `file_size` int(11) NOT NULL,
                              `encrypted` tinyint(1) NOT NULL,
                              `file_name_tn` varchar(255) NOT NULL,
                              `file_ext` varchar(16) NOT NULL,
                              `file_type` varchar(16) NOT NULL,
                              PRIMARY KEY (`file_id`),
                              FULLTEXT KEY `idx_prl_file1` (`key_words`),
                              KEY `idx_prl_file2` (`location_id`) 
                            ) ENGINE=MyISAM DEFAULT CHARSET=utf8');  

        //initialisation
        //$this->addInitialSql('INSERT INTO `TABLE_PREFIXbucket` (name,description,date_create,status,access,access_level) '.
        //                     'VALUES("My Bucket","My first bucket",CURDATE(),"OK","USER",2)');

        //updates use time stamp in ['YYYY-MM-DD HH:MM'] format, must be unique and sequential
        //$this->addUpdateSql('YYYY-MM-DD HH:MM','Update TABLE_PREFIX--- SET --- "X"');
    }
 
}


  
?>
