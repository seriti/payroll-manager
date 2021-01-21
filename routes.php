<?php  
/*
NB: This is not stand alone code and is intended to be used within "seriti/slim3-skeleton" framework
The code snippet below is for use within an existing src/routes.php file within this framework
copy the "/payroll" group into the existing "/admin" group within existing "src/routes.php" file 
*/

//*** BEGIN admin access ***
$app->group('/admin', function () {

     $this->group('/payroll', function () {
        $this->any('/dashboard', \App\Payroll\DashboardController::class);

        $this->any('/company', App\Payroll\CompanyController::class);
        $this->any('/company_file', App\Payroll\CompanyFileController::class);
        $this->any('/company_note', App\Payroll\CompanyNoteController::class);

        $this->any('/file', App\Payroll\FileController::class);
        
        $this->any('/staff', App\Payroll\StaffController::class);
        $this->any('/staff_file', App\Payroll\StaffFileController::class);
        $this->any('/staff_note', App\Payroll\StaffNoteController::class);
        $this->any('/staff_department', App\Payroll\StaffDepartmentController::class);
        $this->any('/staff_department_file', App\Payroll\StaffDepartmentFileController::class);
        $this->any('/staff_position', App\Payroll\StaffPositionController::class);
        $this->any('/staff_scale', App\Payroll\StaffScaleController::class);
        $this->any('/staff_wage', App\Payroll\StaffWageController::class);

        $this->any('/import_csv', App\Payroll\ImportCsvWizardController::class);
        $this->any('/import_wage', App\Payroll\WageImportWizardController::class);
        
        $this->any('/setup', \App\Payroll\TaskController::class);
        $this->get('/setup_data', \App\Payroll\SetupDataController::class);
        $this->any('/report', \App\Payroll\ReportController::class);
    })->add(\App\Payroll\Config::class);


})->add(\App\User\ConfigAdmin::class);
//*** END admin access ***
