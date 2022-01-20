<?php
require('vendor/autoload.php');

class Pix {

  var $API_KEY      = null;
  var $POSTBACK_URL = "https://eticket.esp.br/processa/postback.php?pedId=";
  var $DEBUG        = true;
  
  /**
   * Construtor principal
   * Inicia a classe principal de controle informando o Modelo Atual
   */
  public function __construct() {
    
    if(!$this->DEBUG){
      $this->POSTBACK_URL = 'https://eticket.esp.br/processa/payment/postback.php?pedId=';
      $this->API_KEY      = 'ak_live_d0FWY3L3gyFgYQHPP04sGGxCROFbSy';
    }else{
      $this->POSTBACK_URL = 'http://127.0.0.1/eticket.esp.br/processa/payment/postback.php?pedId=';
      $this->API_KEY      = 'ak_live_d0FWY3L3gyFgYQHPP04sGGxCROFbSy';
      //$this->API_KEY      = 'ak_test_t5Vn6or0LBlTnVkPsc5EWkPJRcHGaq';
    }
  
  }

  /**
   * Habilita / Desabilita o debug do retorno da ação de pagamento
   * @param type $status
   */
  public function setDebug($status){
    $this->DEBUG = $status;
  }

  /**
   * Envia uma transação pix para a paga.me
   * 
   * @param $amount
   * @param $atleta
   * @param $dtVencimento
   * @param $pedidoId
   * @return Object
   */
  public function setPixPayment($amount, $atleta, $dtVencimento, $pedidoId){
    $pagarme  = new PagarMe\Client($this->API_KEY);
    $util     = new Util();
    $password = new Password();

    $transaction = $pagarme->transactions()->create([
      'amount' => $amount,
      'postback_url' => $this->POSTBACK_URL.$password->cript($pedidoId),
      'payment_method' => 'pix',
      'pix_expiration_date' => $dtVencimento.'T23:59:59',
      'payments' => [
        [
          'payment_method' => 'pix',
          'pix'=> [
            'expires_in' => $dtVencimento.'T23:59:59',
            'additional_information' => [
                [
                  'name' => 'Quantidade',
                  'value' => '1'
                ]              
            ]
          ]
        ]
      ],
      'customer' => [
          'external_id' => $atleta->id,
          'name' => $atleta->nome,
          'type' => 'individual',
          'country' => 'br',
          'email' => $atleta->email,
          'documents' => [
            [
              'type' => 'cpf',
              'number' => $atleta->cpf
            ]
          ],
          'phone_numbers' => [$util->phoneNumberBr($atleta->fone)]
        ],
        'billing' => [
          'name' => $atleta->nome,
          'address' => [
            'country' => 'br',
            'street' => 'Av. Antonio de Paiva Cantelmo',
            'street_number' => '954',
            'state' => 'pr',
            'city' => 'Francisco Beltrão',
            'neighborhood' => 'Centro',
            'zipcode' => '85601270'
          ]
      ],
      'items' => [
        [
          'id' => '1',
          'title' => 'Inscricao E-Ticket',
          'unit_price' => $amount,
          'quantity' => 1,
          'tangible' => false,
          'venue' => 'Dois Vizinhos',
          'date' => '2022-03-02'
        ], 
      ],
      'split_rules' => [
        [
          'recipient_id' => 're_ck76f7tel57bafb64kn2n5npp',
         // 'recipient_id' => 're_cizecsj2c00qibu6dgehxxmcp',
          'liable' => true,
          'charge_processing_fee' => false,
          'percentage' => 97,
          'charge_remainder' => false
        ],
        [
          'recipient_id' => 're_cizedoufr0pcqep5zifmrx229',
          //'recipient_id' => 're_ckw7s640y08360p9tu4su7bt7',
          'liable' => false,
          'charge_processing_fee' => true,
          'percentage' => 3,
          'charge_remainder' => true
        ]
      ]
    ]);

    if(!empty($transaction->id)){
      $isTransacted  = true;
      $paymentReturn = array('id' => $transaction->id, 'status' => $this->getStatusTransaction($transaction->status), 'codPix' => $transaction->pix_qr_code );
    }else{
      $isTransacted  = false;
      $paymentReturn = array();
    }

    
  
    return array('status' =>  $isTransacted , 'payment' => $paymentReturn);

  }

  /**
   * Retorna o status da transação no padrão do sistema atual
   * 
   * @param $status
   * @return String
   */
  public function getStatusTransaction($apiStatus){
    switch($apiStatus){
        case "paid":            $status = "Quitado";     break;
        case "refused":         $status = "Cancelado";   break;
        case "processing":      $status = "Processando"; break;
        case "authorized":      $status = "Aguardando";  break;
        case "waiting_payment": $status = "Aguardando";  break;   
        case "pending_refund":  $status = "Aguardando";  break;    
        case "refunded":        $status = "Estornado";   break;  
        case "chargedback":     $status = "Estornado";   break; 
    }
    return $status;
  }
} 