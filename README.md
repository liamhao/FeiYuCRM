# FeiYuCRM
今日头条飞鱼CRM接口加密调用类

[![Build Status](https://travis-ci.org/w736611944/FeiYuCRM.svg)](https://travis-ci.org/w736611944/FeiYuCRM)
[![Latest Stable Version](https://poser.pugx.org/haosijia/feiyu-crm/v/stable)](https://packagist.org/packages/haosijia/feiyu-crm)
[![Total Downloads](https://poser.pugx.org/haosijia/feiyu-crm/downloads)](https://packagist.org/packages/haosijia/feiyu-crm)
[![License](https://poser.pugx.org/haosijia/feiyu-crm/license)](https://packagist.org/packages/haosijia/feiyu-crm)
[![Code Coverage](https://scrutinizer-ci.com/g/w736611944/FeiYuCRM/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/w736611944/FeiYuCRM/?branch=master)

## 安装
    $ composer require haosijia/feiyu-crm

## 使用
```php
<?php

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
```