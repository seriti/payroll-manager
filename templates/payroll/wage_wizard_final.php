<?php
use Seriti\Tools\Form;
use Seriti\Tools\Html;


$html = '';

$html .= '<div class="row">'.
         '<div class="col-lg-12">';

$html .= '<h1>Wage import completed.</h1>';

$html .= '<a href="import_wage"><button class="btn btn-primary">Restart import wizard</button></a>';

$html .= '<p>See wage data below:</p>';
  
$html .= Html::arrayDumpHtml($data['import_data']);
        
$html .= '</div>'.
         '</div>';      
      
echo $html;          

//print_r($form);
//print_r($data);
?>
