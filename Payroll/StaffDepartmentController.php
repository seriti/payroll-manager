<?php
namespace App\Payroll;

use Psr\Container\ContainerInterface;
use App\Payroll\StaffDepartment;

class StaffDepartmentController
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke($request, $response, $args)
    {
        $table_name = TABLE_PREFIX.'staff_department';
        $table = new StaffDepartment($this->container->mysql,$this->container,$table_name);

        $table->setup();
        $html = $table->processTable();

        $template['html'] = $html;
        $template['title'] = MODULE_LOGO.COMPANY_NAME.': Staff departments';
        return $this->container->view->render($response,'admin.php',$template);
    }
}
