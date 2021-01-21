<?php
/*
NB: This is not stand alone code and is intended to be used within "seriti/slim3-skeleton" framework
The code snippet below is for use within an existing src/setup_app.php file within this framework
add the below code snippet to the end of existing "src/setup_app.php" file.
This tells the framework about module: name, sub-memnu route list and title, database table prefix.
*/

$container['config']->set('module','payroll',['name'=>'Payroll management',
                                              'route_root'=>'admin/payroll/',
                                              'route_list'=>['dashboard'=>'Dashboard','staff_wage'=>'Wages paid','staff'=>'Staff',
                                                             'setup'=>'Setup','report'=>'Reports'],
                                              'table_prefix'=>'prl_'
                                             ]);