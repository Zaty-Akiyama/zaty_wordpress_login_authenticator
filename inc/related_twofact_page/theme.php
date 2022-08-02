<?php
  do_action( 'twofact_page_init' );

  $fact_post = $_POST["twofact"];
  $send_value = '';
  $verified = false;

  include_once( PLUGIN_PATH . '/inc/IP_Control/ip_control.php' );

  $ip_setting = array(
    'id' => 'twofactAuth',
    'server' => $_SERVER,
    'options' => array(
      'time_interval' => 5*60,
      'mistake_count' => 3
    )
  );
  $ip_control = new IP_Control( $ip_setting );

  if( $fact_post !== null )
  {
    $send_value = $fact_post;
    $ip_control->ip_recording();
    $verified = apply_filters( 'twofact_check_number', false , $send_value );
  }
  if( $verified )
  {
    $ip_control->ip_accomplished();
    wp_safe_redirect( home_url() . '/wp-admin/about.php' );
  }
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex,nofollow">
  <title><?php bloginfo('name'); ?>2段階認証</title>
  <link rel="stylesheet" href="<?php echo PLUGIN_URL; ?>/src/css/twofact-page.css">
</head>
<body>
  <div class="twofact-page">
    <?php if( $send_value !== '' ) : ?>
    <div class="twofact-page__error">
      &#10005; 二段階認証コードが正しくありません。
    </div>
    <?php endif; ?>
    <h1 class="twofact-page__title"><?php bloginfo('name'); ?></h1>
    <form class="twofact-page__content jsForm">
      <label for="twoFactInput" class="twofact-page__label">二段階認証コード</label>
      <input id="twoFactInput" class="twofact-page__input jsTwoFactInput" name="twofact" type="text" maxlength="6" pattern="^\d{6}$" value="<?php echo $send_value; ?>">
    </form>
    <p class="twofact-page__link">←<a href="<?php echo home_url(); ?>/wp-admin" rel="noopener noreferrer"><?php bloginfo("name"); ?>に移動</a></p>
    <p class="twofact-page__copy">&copy <a href="https://zaty.jp" target="_blank" rel="noopener noreferrer">ZATY</a></p>
  </div>
  <script>
    const twoFactInput = document.querySelector('.jsTwoFactInput')
    const twoFactForm = document.querySelector('.jsForm')
    twoFactForm.method = 'post'
    twoFactForm.action = ''

    twoFactInput.addEventListener( 'input', e => {
      const value = e.target.value
      const removeSpaceValue = value.replace( /[^\d]+/g, "" )
      e.target.value = removeSpaceValue
      if( removeSpaceValue.length === 6 && !Number.isNaN( parseInt( removeSpaceValue ) ) )
      {
        twoFactForm.submit()
      }
    })
 </script>
</body>
</html>