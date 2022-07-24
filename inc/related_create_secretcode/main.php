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

  private static function is_generated_code ()
  {
    $id = wp_get_current_user()->ID;

    return get_user_option( 'authenticator_code', $id );
  }

  private static function update_secret_code ( $code )
  {
    $id = wp_get_current_user()->ID;
    $update = update_user_option( $id, 'authenticator_code', $code );

    update_user_option( $id, 'google_auth_status', 'verified' );

    return $update;
  }
  
  private static function get_user_activate ()
  {
    $id = wp_get_current_user()->ID;
    return get_user_option( 'google_authenticator_activate', $id );
  }

  private static function update_user_activate ( $boolean )
  {
    $id = wp_get_current_user()->ID;
    return update_user_option( $id, 'google_authenticator_activate', $boolean );
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
  }

  private static function create_secret_code ()
  {
    $ga = new PHPGangsta_GoogleAuthenticator();

    $secret = $ga->createSecret();
        
    return $secret;
  }

  public static function appended_profile_area ()
  { ?>
    <h3>Google Authenticator</h3>
    <p>ログイン時に必要になる2段階認証システムです。</p>
    <style>
      .qr-area__image
      {
        width: 150px;
        height: 150px;
        margin-top: 12px;
      }
      .qr-area__image img
      {
        width: 100%;
        height: 100%;
      }
      .qr-area__secret-text
      {
        width: 150px;
        margin-top: 8px;
        font-size: 12px;
        text-align: center;
      }
      .qr-area__display
      {
        width: 150px;
        margin-top: 0;
      }
      .qr-area__switch-wrapper
      {
        display: block;
        width: 100px;
        height: 20px;
        margin: auto;
        position: relative;
      }
      .qr-area__switch
      {
        display: block;
        width: 100px;
        height: 20px;
        background-color: gray;
        border-radius: 100vw;
        position: relative;
        transition: .1s background-color;
      }
      .qr-area__switch--on
      {
        background-color: #2afa30;
      }
      .qr-area__switch-wrapper input[type="checkbox"]:checked::before
      {
        content: "";
      }
      .qr-area__switch-wrapper input[type="checkbox"]:focus
      {
        outline: none;
        box-shadow: none;
      }
      .qr-area__switch-wrapper input[type="checkbox"]
      {
        margin: 0;
        -webkit-appearance: none;
        border: none;
        padding: 0;
        outline: 0;
        z-index: 1;
      }
      input.qr-area__checkbox
      {
        width: 30px;
        height: 16px;
        top: 2px;
        left: 2px;
        position: absolute;
        border-radius: 100vw;
        transition: .1s left;
      }
      input.qr-area__checkbox:checked
      {
        left: calc(100% - 32px);
      }
      input.qr-area__bg
      {
        width: 100px;
        height: 20px;
        position: absolute;
        top: 0;
        left: 0;
        background-color: gray;
        transition: .1s background-color;
      }
      input.qr-area__bg:checked
      {
        background-color: #2afa30;
      }
      .qr-area__generator
      {
        display: flex;
        width: 180px;
        flex-direction: column;
      }
      .qr-area__generate-button,.qr-area__reset-button
      {
        cursor: pointer;
      }
      .qr-area__reset-button-area
      {
        text-align: center;
        margin: 0;
      }
      .qr-area__reset-button
      {
        color: #ff0000;
        text-decoration: underline;
      }
      .qr-area__reset-button:hover
      {
        color: #aa0000;
      }
      .qr-area__activate-area
      {
        margin-top: 10px;
      }
      .qr-area__activate-input
      {
        position: relative;
        top: 1px;
      }
    </style>
    <?php
    // シークレットコードの生成やリセットの処理
      $operation = $_POST['operation'];
      if( $operation === 'generate' )
      {
        $secret_code = self::create_secret_code();
        self::update_secret_code( $secret_code );
      } else if( $operation === 'reset' )
      {
        self::update_user_activate(null);
        self::update_secret_code('');
      }
      $is_generated_code = self::is_generated_code();
      $secret_code = $is_generated_code ? $is_generated_code : '****************';

      $blog_name = get_bloginfo('name');
      $qr_img_src_url = "otpauth://totp/WordPress:"
        . $blog_name
        . "?secret=" . $secret_code;

      $image_src = $is_generated_code ? self::get_qr_image_src( $qr_img_src_url ) : null;
      $no_image_src = PLUGIN_URL . "/src/image/nodisplay.jpg"
    ?>

    <?php
    // 二段階認証の有効化無効化の処理
      $activate = $_POST['activate'];
      $activate_option = self::get_user_activate();

      if( $activate !== null)
      {
        $activate_check = $activate === 'true' ? 'checked' : '';
        self::update_user_activate( $activate );
      }else if( $activate_option )
      {
        $activate_check = $activate_option === 'true' ? 'checked' : '';
      }else
      {
        $activate_check = 'checked';
        self::update_user_activate( 'true' );
      }
    ?>

    <div class="qr-area" id="qrArea">
      <div class="qr-area__generator">

        <?php if( $is_generated_code ): ?>
        <p class="qr-area__reset-button-area"><span class="qr-area__reset-button">リセット</span></p>
        <?php else : ?>
        <button class="qr-area__generate-button">
          シークレットコードを生成
        </button>
       <?php endif;?>

      </div>

      <?php if( $is_generated_code ): ?>
      <div class="qr-area__image">
        <img class="jsTruthImage" src="<?php echo $image_src; ?>" style="display: none;" alt="">
        <img class="jsDummyImage" src="<?php echo $no_image_src; ?>" alt="">
      </div>
      <div class="qr-area__secret-text">
        <span class="jsTruthText" style="display: none;"><?php echo $secret_code; ?></span>
        <span class="jsDummyText">****************</span>
      </div>
      <div class="qr-area__display">
        <div class="qr-area__switch-wrapper">
          <label class="qr-area__switch" for="qrCheckBox"></label>
          <input id="qrCheckBox" class="qr-area__checkbox" type="checkbox">
        </div>
      </div>
      <div class="qr-area__activate-area">
        <label for="qrActivateCheckbox">二段階認証を有効化する
        </label>
        <input class="qr-area__activate-input" id="qrActivateCheckbox" type="checkbox" <?php echo $activate_check; ?>>
      </div>
      <?php endif; ?>

    </div>
    <script src="<?php echo PLUGIN_URL . '/src/js/profileScript.js';?>"></script>
    <?php
  }

}
endif;