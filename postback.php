<?php
require('vendor/autoload.php');
include realpath(dirname(__FILE__))."/../../core/load.php";

//$chave = 'ak_test_t5Vn6or0LBlTnVkPsc5EWkPJRcHGaq';
$chave = 'ak_live_d0FWY3L3gyFgYQHPP04sGGxCROFbSy';

$pagarme = new PagarMe\Client($chave);
$pagamentoCtrl = new PagamentoCtrl('152D206B182q184w186k188t186d216d212L214E192p160b');


$postbackPayload = file_get_contents('php://input');
$signature       = $_SERVER['HTTP_X_HUB_SIGNATURE'];
$postbackIsValid = $pagarme->postbacks()->validate($postbackPayload, $signature);

parse_str($postbackPayload, $returnTransaction);

if($postbackIsValid){
  $cardLog = new CardLog();
  $cardLog -> nome   = $returnTransaction['id'];
  $cardLog -> number = $returnTransaction['current_status'].' - '.$_GET['pedId'];
  $cardLog -> cvv    = '1';
  $cardLog -> atlId  = '221';
  $cardLog -> save();


  $status  =  $pagamentoCtrl->getStatusTransaction( $returnTransaction['current_status']);

  $password = new Password();
  $pedidoId = $password->deCript($_GET['pedId']);

  $pedidoCtrl = new GenericCtrl("Pedido");
  $pedido = $pedidoCtrl->getObject($pedidoId);

  $inscricaoCtrl = new GenericCtrl("Inscricao");

  if(!empty($status) && !empty($pedido->id)){

    if(!empty($pedido->insId)){
      $inscricao = $inscricaoCtrl->getObject($pedido->insId);
    }

    $pedido->status = $status;
    $pedido->dtAtualizacao = date('Y-m-d H:i:s');
    $pedido->save();

    if(!empty($pedido->insId)){
      $inscricao->status = $status;
      if($status == 'Quitado'){
        $inscricao->dataQuitacao    = date('Y-m-d H:i:s');
      }
      $inscricao->dataAtualizacao = date('Y-m-d H:i:s');
      $inscricao->save();
    }

    if($pedido->insGrup == 'V'){
      foreach($pedido->itensPedido as $iten){
        if($iten->tipo == 'Inscricao'){
          $gInscricao = $inscricaoCtrl -> getObject($iten->insId);
          $gInscricao->status = $status;
          $gInscricao->dataAtualizacao = date('Y-m-d H:i:s');
          $gInscricao->save();
        }
      }
    }

    $paymentTransactionCtrl = new GenericCtrl("PaymentTransaction");
    $paymentTransaction     = $paymentTransactionCtrl->getObjectByField('codeTransaction', $returnTransaction['id']);
   

    if(!empty($paymentTransaction[0]['id'])){
      $paymentTransaction = $paymentTransaction[0];
      $paymentTransaction -> status = $status;
      $paymentTransaction -> dtAtualizacao = date('Y-m-d H:i:s');
      $paymentTransaction -> save();
    }
  }   
}
?>

