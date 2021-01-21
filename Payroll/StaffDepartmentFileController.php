<?php
namespace App\Payroll;

use Psr\Container\ContainerInterface;
use App\Payroll\StaffDepartmentFile;

class StaffDepartmentFileController
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke($request, $response, $args)
    {
        $table_name = TABLE_PREFIX.'file';
        $upload = new StaffDepartmentFile($this->container->mysql,$this->container,$table_name);

        $upload->setup();
        $html = $upload->processUpload();

        $template['html'] = $html;
        $template['title'] = MODULE_LOGO.'Staff department documents';
        return $this->container->view->render($response,'admin_popup.php',$template);
    }
}
