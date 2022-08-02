<?php

/**
 * @param Array $setting
 *          id      => {{String}} 複数のページでIPチェックをするために任意IDを設定
 *          server  => $_SERVERを指定
 *          options => {{Array}
 *            time_interval => {{Int}} 連続でIPアドレスを入力された時のロック時間
 *            save          => {{Boolean}} ログイン成功の後にIPアドレスを記録したままにするかどうか
 *                    
 */
class IP_Control
{
  public function __construct( array $setting )
  {
    date_default_timezone_set("Asia/Tokyo");

    $error_check = self::error( $setting );
    if ( !$error_check ) return;
  
    self::constant_define();
    $id = $setting['id'];
    $server = $setting['server'];

    $this->hash_id = hash( 'sha256', $id );
    $this->options = self::initial_options( $setting['options'] );

    self::id_check( $this->hash_id );

    $this->ip_address = $server['REMOTE_ADDR'];
    $this->timestamp = $server['REQUEST_TIME'];
    $this->ip_path = LIB_PATH . "/database/$this->hash_id/ip$this->ip_address.json";

  }

  /**
   * IPアドレスのチェック
   */
  public function ip_lock_check ()
  {
    $locked = false;
    self::create_ip_database( $this->ip_address );
    $is_mistake = self::ip_mistake_check( $this->ip_address );

    if( $is_mistake )
    {
      $locked = self::lock_time_check( $this->ip_address, $this->timestamp );
    }
    return $locked;
  }

  /**
   * IPアドレスの記録
   */
  public function ip_recording ()
  {
    if( !self::ip_lock_check() )
    {
      self::ip_unshift( $this->ip_address, $this->timestamp);
    }
  }

  /**
   * IPアドレスの記録を全てロック解除状態にする
   */
  public function ip_accomplished ()
  {
    $ip_data_array = self::get_ip_json( $this->hash_id, $this->ip_address );
    if( !$ip_data_array ) return false;

    foreach( $ip_data_array as $key => $data )
    {
      if($key === 'locked')
      {
        $ip_data_array['locked'] = false;
        break;
      }
      $ip_data_array[$key]['checked'] = true;
    }
    file_put_contents( $this->ip_path, json_encode($ip_data_array) );

    return true;
  }

  private static function constant_define ()
  {
    define( 'LIB_PATH', __DIR__ );
  }

  /**
   * IPアドレスの保存についての設定の初期値設定
   */
  private static function initial_options ( $options )
  {
    $return_options = array();
    $return_options['time_interval'] = $options['time_interval'] ?? 5 * 60;
    $return_options['save'] = $options['save'] ?? true;
    $return_options['mistake_count'] = $options['mistake_count'] ?? 3;

    return $return_options;
  }

  /**
   * 初期設定のエラーを確認
   */
  private static function error ( array $setting )
  {
    $msg = null;
    if( !$setting['id'] )
    {
      $msg = '任意のIDを設定してください。';

    }elseif( !is_integer($setting['server']['REQUEST_TIME']) )
    {
      $msg = 'timestampが設定されていません';

    }elseif( isset($setting['options']) && !is_integer($setting['options']['time_interval']) )
    {
      $msg = 'IPアドレスのインターバルの形式が不適切です。';
    }

    if( $msg )
    {
      echo $msg;
      return false;
    }
    return true;
  }
  
  /**
   * 対象のIPアドレスのJSONを取得
   */
  private function get_ip_json ( string $hash_id, string $ip_address )
  {
    if( !file_exists( $this->ip_path ) ) return;

    $ip_json = file_get_contents( $this->ip_path );

    $ip_data_array = json_decode( $ip_json, true );
    if( empty($ip_data_array) ) return false;
    return $ip_data_array;
  }

  /**
   * ハッシュ化したIDに対応するディレクトリを作成
   */
  private static function id_check ( string $hash_id )
  {
    $folder_path = LIB_PATH . "/database/" .$hash_id;
    if( file_exists( $folder_path ) ) return;

    mkdir( $folder_path, 0700 );
  }

  /**
   * IPアドレスを保存するデータベースを作成
   */
  private function create_ip_database( string $ip_address )
  {
    if( !file_exists( $this->ip_path ) )
    {
      $ip_json = '{"locked": false}';
      file_put_contents( $this->ip_path, $ip_json );
    }
  }
  /**
   * 取得したIPアドレスをデータベースに保存
   */
  private function ip_unshift( string $ip_address, int $timestamp )
  {
    if( !file_exists( $this->ip_path ) ) return;

    $ip_json = file_get_contents( $this->ip_path );
    $ip_json = $ip_json === '' || $ip_json === 'null' ? '{"locked": false}' : $ip_json;

    $ip_data_array = json_decode( $ip_json, true );

    $new_ip_data = array(
      'ip'        => $ip_address,
      'timestamp' => $timestamp,
      'checked'   => false
    );

    array_unshift( $ip_data_array, $new_ip_data );

    file_put_contents( $this->ip_path, json_encode($ip_data_array) );
  }

  /**
   * IPアドレスのミス回数チェック
   */
  private function ip_mistake_check ( string $ip_address )
  {

    $ip_data_array = self::get_ip_json( $this->hash_id, $ip_address );
    if( !$ip_data_array ) return;

    $false_count = 0;

    foreach( $ip_data_array as $key => $data )
    {
      if($key === 'locked') break;
      if( $data['checked'] === true ) break;
      $false_count++;
    }
    if( $false_count >= $this->options['mistake_count'] )
    {
      $ip_data_array['locked'] = true;
      file_put_contents( $this->ip_path, json_encode($ip_data_array) );
    }

    return $ip_data_array['locked'];
  }

  /**
   * ロックタイムの時間管理
   */
  private function lock_time_check ( string $ip_address, int $timestamp )
  {
    $ip_data_array = self::get_ip_json( $this->hash_id, $ip_address );
    if( !$ip_data_array ) return;

    if( $ip_data_array['locked'] === false ) return false;

    $beforeTimestamp = $ip_data_array[0]["timestamp"];
    // 指定した時間の経過チェック
    if( $timestamp - $beforeTimestamp < $this->options['time_interval'] ) return true;

    if( $this->options['save'] )
    {
      foreach( $ip_data_array as $key => $data )
      {
        if($key === 'locked')
        {
          $ip_data_array['locked'] = false;
          break;
        }
        $ip_data_array[$key]['checked'] = true;
      }
    }else
    {
      $ip_data_array = array( 'locked' => false );
    }
    file_put_contents( $this->ip_path, json_encode($ip_data_array) );

    return $ip_data_array['locked'];
  }
}
