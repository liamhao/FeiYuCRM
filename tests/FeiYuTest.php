<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use FeiYuCRM\FeiYu;

class FeiYuTest extends TestCase
{

  public function getInstanc()
  {
    return new FeiYu([
      'host' => 'https://feiyu.oceanengine.com',
      'pull_route' => '/crm/v2/openapi/pull-clues/',
      'push_route' => '/crm/v2/openapi/clue/callback/',
      'signature_key' => 'ABCDEFGHIGKLMNOP',
      'token' => '01234567890123abcdefghijklmnopqrstuvwxyz',
    ]);
  }

  // 测试拉取方法
  public function testPullData()
  {
    try {
      $feiyu = $this->getInstanc();
      // 拉取数据方法案例
      $res = $feiyu->pullData('2019-08-01', '2019-09-01', 10)->run(function($customers){
        
      });
      $this->assertTrue($res);
    } catch (\Exception $e) {
      $this->assertTrue($e instanceof \FeiYuCRM\FeiYuException);
    }
  }

  // 测试回传方法
  public function testPushData()
  {
    try {
      $feiyu = $this->getInstanc();
      $res = $feiyu->pushData([
        'clue_id' => '1234567891234567891',
        'clue_convert_state' => 3,
      ]);
      $this->assertTrue($res);
    } catch (\Exception $e) {
      $this->assertTrue($e instanceof \FeiYuCRM\FeiYuException);
    }
  }
}