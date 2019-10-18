<?php

namespace FeiYuCRM;

/**
 * FeiYuCRM system class
 * @author haosijia <haosijia@zhufaner.com>
 */
class FeiYu
{
  // Server address
  public $host = '';
  // Data fetch route
  public $pull_route = '';
  // Upload data route
  public $push_route = '';
  // Encryption key
  public $signature_key = '';
  // Token
  public $token = '';

  // Timestamp
  protected $timestamp = '';
  // Signature
  protected $signature = '';
  // Start time
  protected $start_time = '';
  // End time
  protected $end_time = '';
  // Page size
  protected $page_size = '';
  // Run route
  protected $fetch_route = '';
  // Data from host
  protected $res_data = '';
  // Push data source
  protected $push_data = '';

  public function __construct($options)
  {
    $this->host = isset($options['host'])?$options['host']:$this->host;
    $this->pull_route = isset($options['pull_route'])?$options['pull_route']:$this->pull_route;
    $this->push_route = isset($options['push_route'])?$options['push_route']:$this->push_route;
    $this->signature_key = isset($options['signature_key'])?$options['signature_key']:$this->signature_key;
    $this->token = isset($options['token'])?$options['token']:$this->token;
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
   * @param array $data
   * @return bool
   */
  public function pushData($data)
  {
    if(!isset($data['clue_convert_state']) || !isset($data['clue_id'])){
      throw new FeiYuException("Upload data is missing the necessary parameters", 1);
    }
    if(!is_numeric($data['clue_convert_state'])){
      throw new FeiYuException("'clue_convert_state' must be a numeric type", 1);
    }
    $data['clue_convert_state'] = (int)$data['clue_convert_state'];
    $this->push_data = json_encode([
      'source' => 0,
      'data' => [
        'clue_id' => $data['clue_id'],
        'clue_convert_state' => $data['clue_convert_state'],
      ],
    ]);
    $this->fetch_route = $this->push_route;
    $this->fetchCurl();
    return !$this->getResData()['status'];
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
      $this->fetchCurl($page);

      if (call_user_func($callback, $this->res_data['data']) === false) {
        return false;
      }

      $page++;
    } while (($page-1)*($this->page_size) < $this->res_data['count']);

    return true;
  }

  /**
   * encrypt url and start_time and end_time to signature
   * @return $this
   */
  protected function encryptData()
  {
    // 拼接中的空格很重要
    if($this->fetch_route == $this->pull_route){
      $data = $this->fetch_route.'?start_time='.$this->start_time.'&end_time='.$this->end_time.' '.$this->timestamp;
    } else {
      $data = $this->fetch_route.' '.$this->timestamp;
    }
    $this->signature = base64_encode(hash_hmac('sha256', $data, $this->signature_key));
    return $this;
  }

  /**
   * fetch data by curl
   * @param string $page
   * @return $this
   */
  protected function fetchCurl($page = 1)
  {
    $this->encryptData();
    $ch = curl_init();
    if(!$ch){
      throw new FeiYuException('cURL init failed', 1);
    }
    curl_setopt($ch, CURLOPT_URL, $this->host.$this->fetch_route.'?page='.$page.'&page_size='.$this->page_size.'&start_time='.$this->start_time.'&end_time='.$this->end_time);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json;charset=UTF-8',
        'Signature: ' . $this->signature,
        'Timestamp: ' . $this->timestamp,
        'Access-Token: ' . $this->token,
    ]);
    if($this->fetch_route == $this->push_route){
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $this->push_data);
    }
    $output = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    if($error != ''){
      throw new FeiYuException($error, 1);
    }
    $this->res_data = json_decode($output, true);
    if($this->res_data['status'] != 'success'){
      if(is_array($this->res_data['msg'])){
        throw new FeiYuException(json_encode($this->res_data['msg']), 1);
      }
      throw new FeiYuException($this->res_data['msg'], 1);
    }
    return $this;
  }
}

class FeiYuException extends \Exception
{
  
}