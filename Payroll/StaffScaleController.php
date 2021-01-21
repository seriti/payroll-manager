<?php
namespace App\Payroll;

use Psr\Container\ContainerInterface;
use App\Payroll\StaffScale;

class StaffScaleController
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke($request, $response, $args)
    {
        $table_name = TABLE_PREFIX.'staff_scale';
        $table = new StaffScale($this->container->mysql,$this->container,$table_name);

        $table->setup();
        $html = $table->processTable();

        $template['html'] = $html;
        $template['title'] = MODULE_LOGO.COMPANY_NAME.': Staff wage scales';
        return $this->container->view->render($response,'admin.php',$template);
    }
}
