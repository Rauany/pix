<?php
require('vendor/autoload.php');

//$chave = 'ak_test_t5Vn6or0LBlTnVkPsc5EWkPJRcHGaq';
$chave = 'ak_live_d0FWY3L3gyFgYQHPP04sGGxCROFbSy';

$pagarme = new PagarMe\Client($chave);

$transaction = $pagarme->transactions()->create([
  'amount' => 113,
  'postback_url' => 'https://eticket.esp.br/processa/payment/postback.php?pedId=20',
  'payment_method' => 'pix',
  'pix_expiration_date' => '2021-12-31T23:59:59',
  'payments' => [
    [
      'payment_method' => 'pix',
      'pix'=> [
          'expires_in' => '2021-12-31T23:59:59',
          'additional_information' => [
              [
                  'name' => 'Quantidade',
                  'value' => '2'
              ]              
          ]
      
      ]
    ]
  ],
  'customer' => [
      'external_id' => '19',
      'name' => 'Rauany Esperandim',
      'type' => 'individual',
      'country' => 'br',
      'email' => 'raulesperandim@gmail.com',
      'documents' => [
        [
          'type' => 'cpf',
          'number' => '08646758958'
        ]
      ],
      'phone_numbers' => [ '+5546991038046' ]
    ],
    'billing' => [
      'name' => 'Rauany Jorge Esperandim',
      'address' => [
        'country' => 'br',
        'street' => 'Avenida Antonio de paiva cantelmo',
        'street_number' => '1811',
        'state' => 'pr',
        'city' => 'Francisco Beltrap',
        'neighborhood' => 'centro',
        'zipcode' => '85601270'
      ]
  ],
  'items' => [
    [
      'id' => '1',
      'title' => 'Inscricao evento',
      'unit_price' => 113,
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

var_dump($transaction);


echo '<br>';

echo '<li> id: '.$transaction->id.'</li>';
echo '<li> status: '.$transaction->status.'</li>';
echo '<li> pix_qr_code: '.$transaction->pix_qr_code.'</li>';
