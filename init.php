<?php
/**
 * ZATY Set Google Authenticator
 * 
 * Plugin name: Zaty Login Security
 * 
 * Description: WordPressのログインセキュリティを高めるプラグインです。
 * Version: 1.0.0
 * Author: ZATY
 * Author URI: https://zaty.jp
 * 
 * Text Domain zaty_auth
 */
if( !class_exists( "ZATY_Login_Security" ) ) :

class ZATY_Login_Security
{
  public function __construct ()
  {
    self::init();
  }

  private static function init ()
  {
    self::define_constants();

    self::include_files();

    new Append_User_Profile;

    new Two_fact_login_page;
  }

  private static function define_constants ()
  {

    define( 'PLUGIN_FILE', __FILE__ );
    define( 'PLUGIN_PATH', dirname( __FILE__ ) );
    define( 'PLUGIN_URL', plugin_dir_url( __FILE__ ) );

  }

  private static function include_files ()
  {
    include_once( PLUGIN_PATH . '/inc/related_create_secretcode/main.php' );
    include_once( PLUGIN_PATH . '/inc/related_twofact_page/main.php' );
  }
}
new ZATY_Login_Security;

endif;