<?php
use Seriti\Tools\Form;
use Seriti\Tools\Html;

$list_param = ['class'=>'form-control'];
$list_param_month = ['class'=>'form-control input-inline input-small'];
$days_param = ['class'=>'form-control input-small'];
$file_param = ['class'=>'btn btn-primary'];
$check_param = ['class'=>'form-control'];

$import_format = ['STANDARD'=>'Standard Monthly day count based CSV dump'];

$past_years = 0;
$future_years = 0;
if($form['wage_month'] == '') $form['wage_month'] = date('m');
if($form['wage_year'] == '') $form['wage_year'] = date('Y');
if($form['days_in_month'] == '') $form['days_in_month'] = DAYS_IN_MONTH;

$html = '';

$html .= '<div class="row">'.
         '<div class="col-lg-6">';

$html .= '<div class="row"><div class="col-lg-12">'.
         '1.) Select the file format that you wish to import:<br/>'.
         Form::arrayList($import_format,'import_format',$form['import_format'],true,$list_param).
         '</div></div>';

$html .= '<div class="row"><div class="col-lg-12">'.
         '2.) Select the Month that you wish to import wage data for:<br/>'.
         Form::monthsList($form['wage_month'],'wage_month',$list_param_month).
         Form::yearsList($form['wage_year'],$past_years,$future_years,'wage_year',$list_param_month).
         '</div></div>';

$html .= '<div class="row"><div class="col-lg-12">'.
         '3.) Number of days in month:<br/>'.
         Form::daysList($form['days_in_month'],'days_in_month',$days_param).
         '</div></div>';  

$html .= '<div class="row"><div class="col-lg-12">'.
         '4.) Select the data file you wish to import (*.txt or *.csv ONLY):<br/>'.
         Form::fileInput('data_file',$form['data_file'],$file_param).
         '</div></div>';  

$html .= '<div class="row"><div class="col-lg-12">'.
         '5.) Do you want to ignore any errors and import valid data?:<br/>'.
         Form::checkBox('ignore_errors',true,$form['ignore_errors'],$check_param).
         '</div></div>';

$html .= '<div class="row"><div class="col-lg-12">'.
         '6.) Upload data file and review data before processing...<br/>'.
         '<input type="submit" class="btn btn-primary" id="import_button" value="Upload & Review" onclick="link_download("import_button");">'.
         '</div></div>';

$html .= '</div>'.
         '<div class="col-lg-6">';        
                        
$html .= '<div class="row"><div class="col-lg-12">'.
         '<p><b>NB1:</b> Your CSV text file must be correctly formatted to import correctly!</p>'.
         '<p><b>NB2:</b> All formats have different CSV structure. If you are not sure which format to use please contact us!</p>'.
         '<p><b>NB3:</b> You will be able to review all data before imported.</p>'.
         '</div></div>';       
        
$html .= '</div>'.
         '</div>';      
      
echo $html;          

//print_r($form);
//print_r($data);
?>
