<?php
namespace App\Payroll;

use Psr\Container\ContainerInterface;
use App\Payroll\CompanyNote;

class CompanyNoteController
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke($request, $response, $args)
    {
        $table_name = TABLE_PREFIX.'note';
        $table = new CompanyNote($this->container->mysql,$this->container,$table_name);

        $table->setup();
        $html = $table->processTable();

        $template['html'] = $html;
        //$template['title'] = MODULE_LOGO.' Company Notes';
        return $this->container->view->render($response,'admin_popup.php',$template);
    }
}
