<?php
require_once PLUGIN_PATH . '/vendor/autoload.php';

use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelLow;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;
use PHPGangsta\GoogleAuthenticator;

if ( !class_exists( "Append_User_Profile" ) ) :

class Append_User_Profile
{
  private $user_id;
  private $page_user_id;
  private $user_authen_code;

  public function __construct ()
  {
    if( !is_admin() ) return;

    self::init();

  }

  private static function get_qr_image_src ( $uri )
  {
    $qr_code = QrCode::create( $uri )
    -> setEncoding( new Encoding( 'UTF-8' ) )
    -> setErrorCorrectionLevel( new ErrorCorrectionLevelLow() )
    -> setSize( 300 )
    -> setMargin( 30 )
    -> setRoundBlockSizeMode( new RoundBlockSizeModeMargin() )
    -> setForegroundColor( new Color( 0, 0, 0 ) )
    -> setBackgroundColor( new Color( 255, 255, 255 ) );

    $writer = new PngWriter();
    $result = $writer->write( $qr_code );

    $img_src = $result->getDataUri();

    return $img_src;
  }

  private static function init ()
  {
    add_action( 'show_user_profile', array( __CLASS__, 'appended_profile_area' ), 50 );
    add_action( 'edit_user_profile', array( __CLASS__, 'appended_profile_another_user' ), 50, 1 );
  }

  private static function create_secret_code ()
  {
    $ga = new PHPGangsta_GoogleAuthenticator();

    $secret = $ga->createSecret();
        
    return $secret;
  }

  public static function appended_profile_area ()
  { 
    include( PLUGIN_PATH . '/inc/related_create_secretcode/append_info_in_user_profile.php' );
  }

  public static function appended_profile_another_user ( $profile_user )
  { 
    include( PLUGIN_PATH . '/inc/related_create_secretcode/append_info_in_another_user_profile.php' );
  }


}
endif;