<?php 
namespace App\Payroll;

use Psr\Container\ContainerInterface;
use Seriti\Tools\BASE_URL;
use Seriti\Tools\SITE_NAME;

class Config
{
    
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

    }

    /**
     * Example middleware invokable class
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @param  \Psr\Http\Message\ResponseInterface      $response PSR7 response
     * @param  callable                                 $next     Next middleware
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke($request, $response, $next)
    {
        
        $module = $this->container->config->get('module','payroll');
        $menu = $this->container->menu;
        $cache = $this->container->cache;
        $user = $this->container->user;
        $db = $this->container->mysql;
        
        $user_specific = true;
        $cache->setCache('Payroll',$user_specific);
        
        define('TABLE_PREFIX',$module['table_prefix']);
        define('MODULE_ID','BUCKET');
        define('MODULE_LOGO','<span class="glyphicon glyphicon-usd" aria-hidden="true"></span> ');
        define('MODULE_PAGE',URL_CLEAN_LAST);

        //NB: used to calculate daily wage, not actual days in month, default value
        define('DAYS_IN_MONTH',26);

        define('WAGE_BASIS',['MONTHLY'=>'Monthly wage','DAILY'=>'Daily_wage']);
       
        $setup_pages = ['company','staff_department','staff_position','staff_scale'];

        $setup_link = '';
        if(in_array(MODULE_PAGE,$setup_pages)) {
            $page = 'setup';
            $setup_link = '<a href="setup"> -- back to setup options --</a><br/><br/>';
        } elseif(stripos(MODULE_PAGE,'_wizard') !== false) {
            $page = str_replace('_wizard','',MODULE_PAGE);
        } else {    
            $page = MODULE_PAGE;
        }

        $user_data = $cache->retrieveAll();
        $table_company = TABLE_PREFIX.'company';
        if(!isset($user_data['company_id'])) {
            //first run on setup fails if table does not exist
            if($db->checkTableExists($table_company)) {
                $sql = 'SELECT company_id FROM '.$table_company.' ORDER BY name LIMIT 1';
                $company_id = $db->readSqlValue($sql,0);
                if($company_id !== 0) {
                    $user_data['company_id'] = $company_id;
                    $cache->store('company_id',$company_id);  
                }   
            }  
        }   

        if(isset($user_data['company_id'])) {
            $sql = 'SELECT company_id,name,status FROM '.$table_company.' '.
                   'WHERE company_id = "'.$user_data['company_id'].'" ';    
            $company = $db->readSqlRecord($sql);
            define('COMPANY_ID',$user_data['company_id']);
            define('COMPANY_NAME',$company['name']);
        } else {
            define('COMPANY_ID',0);
            define('COMPANY_NAME','');
        }
        
        //only show module sub menu for users with normal non-route based access
        if($user->getRouteAccess() === false) {
            $submenu_html = $menu->buildNav($module['route_list'],$page).$setup_link;
            $this->container->view->addAttribute('sub_menu',$submenu_html);
        }    
       
        $response = $next($request, $response);
        
        return $response;
    }
}