<?php
use Seriti\Tools\Date;
use Seriti\Tools\Form;
use Seriti\Tools\Html;

$list_param = ['class'=>'form-control'];
//$list_param['onchange']='display_options();';
$file_param = ['class'=>'btn btn-primary'];
$check_param = ['class'=>'form-control'];

$html = '';

$html .= '<div class="row">'.
         '<div class="col-lg-12">';

$html .= '<input type="submit" class="btn btn-primary" id="submit_button" '.
           'value="Process Wage data" onclick="link_download("submit_button");">';

//$html .= '<p>Assign description keywords to accounts for future recognition: '.Form::checkBox('assign_keywords','1',$form['assign_keywords']).'</p>';
  
$html .= '<p>Wages for <strong>'.Date::monthName($form['wage_month']).' '.$form['wage_year'].'</strong> '.
         'Days in month: <strong>'.$form['days_in_month'].'</strong></p>';

$html .= $data['confirm_form'];
        
$html .= '</div>'.
         '</div>';      
      
echo $html;          

//print_r($form);
//print_r($data);
?>
