<?php
namespace App\Payroll;

use Psr\Container\ContainerInterface;

use App\Payroll\WageImport;
use App\Payroll\WageImportWizard;

use Seriti\Tools\Template;
use Seriti\Tools\BASE_TEMPLATE;

class WageImportWizardController
{
    protected $container;
    

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }


    public function __invoke($request, $response, $args)
    {
        $cache = $this->container->cache;
        $user_specific = true;
        $cache->setCache('wage_import_wizard',$user_specific);

        $wizard_template = new Template(BASE_TEMPLATE);


        $table = TABLE_PREFIX.'staff_wage';
        $import = new WageImport($this->container->mysql,$this->container,$table);
        
        $wizard = new WageImportWizard($this->container->mysql,$this->container,$cache,$wizard_template);
        
        $wizard->setup();
        $wizard->addImport($import);

        $html = $wizard->process();

        $template['html'] = $html;
        $template['title'] = MODULE_LOGO.COMPANY_NAME.': Staff wage import wizard ';
        //$template['javascript'] = $dashboard->getJavascript();

        return $this->container->view->render($response,'admin.php',$template);
    }
}