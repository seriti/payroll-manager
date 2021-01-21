<?php
namespace App\Payroll;

use Psr\Container\ContainerInterface;
use App\Payroll\Task;

class TaskController
{
    protected $container;
    
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke($request, $response, $args)
    {
        $param = [];
        $task = new Task($this->container->mysql,$this->container,$param);

        $task->setup();
        $html = $task->processTasks();
        
        $template['html'] = $html;
        $template['title'] = MODULE_LOGO.COMPANY_NAME.': Setup tasks';
        
        return $this->container->view->render($response,'admin.php',$template);
    }
}