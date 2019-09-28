<?php

require_once '../vendor/autoload.php';

use FeiYuCRM\FeiYu;

$feiyu = new FeiYu([
  'host' => 'https://feiyu.oceanengine.com',
  'pull_route' => '/crm/v2/openapi/pull-clues/',
  'push_route' => '/crm/v2/openapi/clue/callback/',
  'signature_key' => 'ABCDEFGHIGKLMNOP',
  'token' => '01234567890123abcdefghijklmnopqrstuvwxyz',
]);

$feiyu->pullData('2019-08-01', '2019-09-01', 100)->run(function($customers){
  foreach ($customers as $customer) {
    // run yourself function
    print_r($customer);
    die;
  }
});