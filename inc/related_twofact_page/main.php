<?php
require_once PLUGIN_PATH . '/vendor/autoload.php';
use PHPGangsta\GoogleAuthenticator;


if( !class_exists( "Two_fact_login_page" ) ):

class Two_fact_login_page
{
  public function __construct ()
  {
    self::ip_control();

    self::init();
  }

  public function ip_control ()
  {
    include_once( PLUGIN_PATH . '/inc/IP_Control/ip_control.php' );

    $ip_setting = array(
      'id' => 'twofactAuth',
      'server' => $_SERVER,
      'options' => array(
        'time_interval' => 5*60,
        'mistake_count' => 3
      )
    );
    $this->ip_control = new IP_Control( $ip_setting );

    if( $this->ip_control->ip_lock_check() )
    {
      add_action( 'twofact_page_init', array( __CLASS__, 'block_ip_theme' ), 1 );
    }
  }

  public static function block_ip_theme ()
  {
    include_once( PLUGIN_PATH . '/inc/related_twofact_page/blocked_theme.php' );
    exit;
  }

  private static function init ()
  {
    //ログイン後に移動するページを作成する
    include_once( PLUGIN_PATH . '/inc/related_twofact_page/routing.php' );

    self::two_fact_hooks();
  }

  private static function two_fact_hooks ()
  {
    add_filter( 'twofact_check_number', array( __CLASS__, 'secret_code_verify' ), 0, 2 );
    add_action( 'twofact_page_init', array( __CLASS__, 'check_regular_order' ) );

    add_action( 'wp_login', array( __CLASS__, 'login_redirect_twofact_page' ), 1, 2 );
    add_action( 'wp_logout', array( __CLASS__, 'reset_auth_status_at_logout' ), 1, 1 );

    add_action( 'admin_init', array( __CLASS__, 'check_verified_google_auth' ), 0 );
  }

  // ログイン完了後に二段階認証ページにリダイレクトする
  public static function login_redirect_twofact_page ( $user_login, $user )
  {
    $id = $user->ID;

    $author_has_secret_code = get_user_option( 'authenticator_code', $id );

    update_user_option( $id, 'google_auth_status', 'logined' );
    wp_redirect( home_url(). '/falsdkjf' );

    $activate_google_auth = get_user_option( 'google_authenticator_activate', $id );
    if ( $activate_google_auth === 'false' )
    {
      update_user_option( $id, 'google_auth_status', 'verified' );
    }else if( $author_has_secret_code )
    {
      wp_redirect( home_url() . '/loginAuthenticator/google_authenticator' );
      exit();
    }
    wp_redirect( home_url() );
    exit();
  }

  // 二段階認証画面はログイン後にしかアクセスできない
  public static function check_regular_order ()
  {
    if( !is_user_logged_in() )
    {
      wp_safe_redirect( home_url() );
    }

    $id = wp_get_current_user()->ID;

    $google_auth_status = get_user_option( 'google_auth_status', $id );
    $activate_google_auth = get_user_option( 'google_authenticator_activate', $id );

    if( $google_auth_status !== 'logined' || $activate_google_auth === 'false' )
    {
      $this->ip_control->ip_accomplished();
      wp_safe_redirect( home_url() );
    }
  }

  public static function secret_code_verify ( $boolean, $secret_code )
  {
    $id = wp_get_current_user()->ID;

    $author_has_secret_code = get_user_option( 'authenticator_code', $id );

    $ga = new PHPGangsta_GoogleAuthenticator();

    $verify = $ga->verifyCode( $author_has_secret_code, $secret_code ); 

    if( $verify )
    {
      update_user_option( $id, 'google_auth_status', 'verified' );
    }
    
    return $verify;
  }

  public static function reset_auth_status_at_logout ( $id )
  {
    delete_user_option( 'google_authenticator_activate', $id );
    delete_user_option( 'google_auth_status', $id );
  }

  // 二段階認証せずにページ移動したら強制ログアウト
  public static function check_verified_google_auth ()
  {
    $id = wp_get_current_user()->ID;

    $author_has_secret_code = get_user_option( 'authenticator_code', $id );
    $google_auth_status = get_user_option( 'google_auth_status', $id );
    $activate_google_auth = get_user_option( 'google_authenticator_activate', $id );

    if( $author_has_secret_code && $google_auth_status !== 'verified' && $activate_google_auth === 'true' )
    {
      wp_logout();
    }
  }
}

endif;