<?php
/**
 * @package gravatar-or-default
 * @version 0.1
 */
 /*
 Plugin Name: Gravatar or Default
 Plugin URI: https://github.com/ryanvade/wp-gravatar-or-default
 Description: Allow users to choose either Gravatar or a default avatar.
 Version: 0.1.0
 Author: Ryan Owens
 Author URI: https://github.com/ryanvade
 License: GPLv3 or later
 Text Domain: ryan
 */
 register_activation_hook( __FILE__, 'gravatar_or_default_add_table' );
 add_filter( 'get_avatar' , 'gravatar_or_default_get_avatar' , 1 , 5 );

function gravatar_or_default_add_table()
{
  global $wpdb;
$table_name = $wpdb->prefix . "user_avatars";
$charset_collate = $wpdb->get_charset_collate();

$sql = "CREATE TABLE $table_name (
  id mediumint(9) NOT NULL AUTO_INCREMENT,
  user_id mediumint(9) NOT NULL UNIQUE,
  avatar_url varchar(150),
  PRIMARY KEY  (id)
) $charset_collate;";

require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
dbDelta( $sql );
}

function gravatar_or_default_get_avatar($avatar, $id_or_email, $size, $default, $alt)
{
  $user = false;
  $avatar = "";
  global $wpdb;

    if ( is_numeric( $id_or_email ) ) {

        $id = (int) $id_or_email;
        $user = get_user_by( 'id' , $id );

    } elseif ( is_object( $id_or_email ) ) {

        if ( ! empty( $id_or_email->user_id ) ) {
            $id = (int) $id_or_email->user_id;
            $user = get_user_by( 'id' , $id );
        }

    } else {
        $user = get_user_by( 'email', $id_or_email );
    }

    if ( $user && is_object( $user ) ) {
      $table = $wpdb->prefix . "user_avatars";
      $url = $wpdb->get_var('SELECT avatar_url FROM ' . $table . ' WHERE user_id="' . $user->ID . '"');
      if($url != NULL && $url != '')
      {
        $avatar = "<img alt='{$alt}' src='{$url}' class='avatar avatar-{$size} photo' height='{$size}' width='{$size}' />";
      }else {
        $default = str_replace('/x', '/images/profiles/profile_default.png', site_url());
        $avatar = "<img alt='{$alt}' src='{$default}' class='avatar avatar-{$size} photo' height='{$size}' width='{$size}' />";
      }
    }
    return $avatar;
}
