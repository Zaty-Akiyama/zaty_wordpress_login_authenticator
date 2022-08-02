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
      &#10005; 現在ログインロックされています。時間経過後もう一度アクセスしてください。
    </div>
    <?php endif; ?>
    <h1 class="twofact-page__title"><?php bloginfo('name'); ?></h1>
    <p class="twofact-page__link">←<a href="<?php echo home_url(); ?>/wp-admin" rel="noopener noreferrer"><?php bloginfo("name"); ?>に移動</a></p>
    <p class="twofact-page__copy">&copy <a href="https://zaty.jp" target="_blank" rel="noopener noreferrer">ZATY</a></p>
  </div>
</body>
</html>