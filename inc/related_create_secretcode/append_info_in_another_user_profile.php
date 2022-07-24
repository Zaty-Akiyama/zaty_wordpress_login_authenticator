<h3>Google Authenticator</h3>
<p>ログイン時に必要になる2段階認証システムです。</p>
<style>
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
  $id = $profile_user->ID;

// シークレットコードの取得
  $is_generated_code = get_user_option( 'authenticator_code', $id );
  $secret_code = $is_generated_code ? $is_generated_code : null;
?>

<?php
// 二段階認証の有効化無効化の処理
  $activate = $_POST['activate'];
  $activate_option = get_user_option( 'google_authenticator_activate', $id );

  if( $activate !== null )
  {
    $activate_check = $activate === 'true' ? 'checked' : '';
    update_user_option( $id, 'google_authenticator_activate', $activate );
  }else if( $activate_option )
  {
    $activate_check = $activate_option === 'true' ? 'checked' : '';
  }else
  {
    $activate_check = 'checked';
    update_user_option( $id, 'google_authenticator_activate', 'true' );
  }
?>

<div class="qr-area" id="qrArea">
<div class="qr-area__another-user"></div>
  <?php if( $is_generated_code ): ?>
  <div class="qr-area__activate-area">
    <label for="qrActivateCheckbox">二段階認証を有効化する
    </label>
    <input class="qr-area__activate-input" id="qrActivateCheckbox" type="checkbox" <?php echo $activate_check; ?>>
  </div>
  <?php else: ?>
  <p>このユーザーは二段階認証のシークレットキーを生成していません。</p>
  <?php endif; ?>

</div>
<script src="<?php echo PLUGIN_URL . '/src/js/profileScript.js';?>"></script>
