<?php
// if uninstall.php is not called by WordPress, die
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

delete_option('KTFY_yext_key_options');
delete_site_option('KTFY_yext_key_options');

delete_option('KTFY_yext_sub_account_options');
delete_site_option('KTFY_yext_sub_account_options');

?>
