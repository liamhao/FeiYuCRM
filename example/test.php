<?php

require_once '../src/FeiYu.php';

use FeiYuCRM\FeiYu;

$feiyu = new FeiYu([
  'host' => 'https://feiyu.oceanengine.com',
  'pull_route' => '/crm/v2/openapi/pull-clues/',
  'push_route' => '/crm/v2/openapi/clue/callback/',
  'signature_key' => 'ABCDEFGHIGKLMNOP',
  'token' => '01234567890123abcdefghijklmnopqrstuvwxyz',
]);

// 拉取数据方法案例
$feiyu->pullData('2019-08-01', '2019-09-01', 100)->run(function($customers){
  foreach ($customers as $customer) {
    // run yourself function
    print_r($customer);
    die;
  }
});

// 回传数据方法案例
$res = $feiyu->pushData([
  'clue_id' => '1234567891234567891',
  'clue_convert_state' => 3,
]);