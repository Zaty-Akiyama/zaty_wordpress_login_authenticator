<?php

if( !class_exists( "Two_Fact_Page_Routing" ) ) :

class Two_Fact_Page_Routing
{
  public function __construct ()
  {
    add_filter( 'rewrite_rules_array', array( __CLASS__, 'google_auth_two_fact_rule' ) );

    add_filter( 'query_vars', array( __CLASS__, 'two_fact_routing_vars' ) );

    add_action( 'template_redirect', array( __CLASS__, 'google_auth_redirect' ) );

    register_activation_hook( PLUGIN_FILE, array( __CLASS__, 'flush_rewrite_twofact_rules' ) );
  }

  public static function google_auth_two_fact_rule ( $rules )
  {
    $new_rule = array(
      'loginAuthenticator/([_\-a-z]+)' => 'index.php?loginAuthenticator=$matches[1]'
    );
    return $new_rule + $rules;
  }

  public static function two_fact_routing_vars ( $vars )
  {
    $vars[] = 'loginAuthenticator';
    return $vars;
  }

  public static function google_auth_redirect ()
  {
    $param = get_query_var('loginAuthenticator');
    if( $param === 'google_authenticator' )
    {
      include_once( PLUGIN_PATH . '/inc/related_twofact_page/theme.php' );
      exit;
    }
  }

  public function flush_rewrite_twofact_rules ()
  {
    flush_rewrite_rules();
  }
}

new Two_Fact_Page_Routing;
endif;