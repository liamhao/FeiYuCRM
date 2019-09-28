<?php

namespace FeiYuCRM;

/**
 * FeiYuCRM system class
 * @author haosijia <haosijia@zhufaner.com>
 */
class FeiYu
{
  // Server address
  public $host;
  // Data fetch route
  public $pull_route;
  // Upload data route
  public $push_route;
  // Encryption key
  public $signature_key;
  // Token
  public $token;

  // Timestamp
  private $timestamp;
  // Signature
  private $signature;
  // Start time
  private $start_time;
  // End time
  private $end_time;
  // Page size
  private $page_size;
  // Run route
  private $fetch_route;
  // Data from host
  private $res_data;

  public function __construct($options)
  {
    $this->host = isset($options['host'])?$options['host']:'';
    $this->pull_route = isset($options['pull_route'])?$options['pull_route']:'';
    $this->push_route = isset($options['push_route'])?$options['push_route']:'';
    $this->signature_key = isset($options['signature_key'])?$options['signature_key']:'';
    $this->token = isset($options['token'])?$options['token']:'';
    $this->timestamp = time();
  }

  /**
   * pull data
   * @param string $start_time ['Y-m-d']
   * @param string $end_time ['Y-m-d']
   * @param int $page_size
   * @return $this
   */
  public function pullData($start_time, $end_time, $page_size)
  {
    $this->start_time = strtotime($start_time);
    $this->end_time = strtotime($end_time);
    $this->page_size = $page_size;
    $this->fetch_route = $this->pull_route;
    return $this;
  }

  /**
   * push data
   * @param string $start_time ['Y-m-d']
   * @param string $end_time ['Y-m-d']
   * @param int $page_size
   * @return $this
   */
  public function pushData($start_time, $end_time, $page_size)
  {
    $this->start_time = strtotime($start_time);
    $this->end_time = strtotime($end_time);
    $this->page_size = $page_size;
    $this->fetch_route = $this->push_route;
    return $this;
  }

  /**
   * get result data from curl
   * @return string
   */
  public function getResData()
  {
    return $this->res_data;
  }

  /**
   * get all page data and run callback function in every page
   * @return string
   */
  public function run($callback)
  {
    $page = 1;

    do {
      $this->fetchCurl($this->fetch_route, $page, $this->page_size);

      if (call_user_func($callback, $this->res_data['data']) === false) {
        return false;
      }

      $page++;
    } while (($page-1)*($this->page_size) < $this->res_data['count']);

    return true;
  }

  /**
   * encrypt url and start_time and end_time to signature
   * @param string $route
   * @param string $start_time ['Y-m-d']
   * @param string $end_time ['Y-m-d']
   * @return $this
   */
  private function encryptData($route, $start_time, $end_time)
  {
    // 这个空格很重要
    $data = $route . '?start_time='.$start_time.'&end_time='.$end_time.' '.$this->timestamp;
    $this->signature = base64_encode(hash_hmac('sha256', $data, $this->signature_key));
    return $this;
  }

  /**
   * fetch data by curl
   * @param string $route
   * @return string
   */
  private function fetchCurl($route, $page, $page_size)
  {
    $this->encryptData($route, $this->start_time, $this->end_time);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $this->host.$route.'?page='.$page.'&page_size='.$page_size.'&start_time='.$this->start_time.'&end_time='.$this->end_time);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json;charset=UTF-8',
        'Signature: ' . $this->signature,
        'Timestamp: ' . $this->timestamp,
        'Access-Token: ' . $this->token,
    ]);
    $output = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    if($error != ''){
      throw new \Exception($error, 1);
    }
    $this->res_data = json_decode($output, true);
    if($this->res_data['status'] != 'success'){
      throw new \Exception($this->res_data['msg'], 1);
    }
    return $this;
  }
}