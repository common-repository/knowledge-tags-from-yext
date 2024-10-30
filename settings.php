<?php
/**
 * @internal never define functions inside callbacks.
 * these functions could be run multiple times; this would result in a fatal error.
 */

/**
 * custom option and settings
 */

function KTFY_yext_settings_init()
{
    // register a new setting for "yext" page
    register_setting('KTFY_yext_schema', 'KTFY_yext_key_options');
    register_setting('KTFY_yext_schema', 'KTFY_yext_sub_account_options');

    add_settings_section(
       'KTFY_yext_section_developers',
       __('Your Site Settings', 'KTFY-yext-schema'),
       'KTFY_yext_section_developers_cb',
       'KTFY_yext_schema'
     );

    add_settings_field(
       'KTFY_yext_key', // as of WP 4.6 this value is used only internally
       __('Key', 'KTFY-yext-schema'),
       'KTFY_yext_key_cb',
       'KTFY_yext_schema',
       'KTFY_yext_section_developers',
       [
         'label_for' => 'KTFY_yext_key',
         'class' => 'KTFY_yext_row',
         'KTFY_yext_custom_data' => 'custom',
       ]
     );

    add_settings_field(
       'KTFY_yext_sub_account', // as of WP 4.6 this value is used only internally
       __('Sub-Account ID (Optional)', 'KTFY-yext-schema'),
       'KTFY_yext_sub_account_cb',
       'KTFY_yext_schema',
       'KTFY_yext_section_developers',
       [
         'label_for' => 'KTFY_yext_sub_account',
         'class' => 'KTFY_yext_row',
         'KTFY_yext_custom_data' => 'custom',
       ]
     );
    // adding css
    wp_enqueue_style('KTFY_yext-options', plugin_dir_url(__FILE__)  . 'public/css/yext.css');
}

function KTFY_yext_shortcode_settings_init()
{
    add_settings_section(
  'KTFY_yext_section_shortcodes',
  __('Example Shortcode Usage:', 'KTFY_yext-shortcode'),
  'KTFY_yext_section_shortcodes_cb',
  'KTFY_yext_schema_shortcodes'
  );
}

function KTFY_yext_custom_field_settings_init()
{
    add_settings_section(
  'KTFY_yext_section_custom_field',
  __('Add Location ID to Custom Fields:', 'KTFY-yext-custom-field'),
  'KTFY_yext_section_custom_field_cb',
  'KTFY_yext_schema_custom_field'
  );
}
/**
 * register our KTFY_yext_settings_init to the admin_init action hook
 */
add_action('admin_init', 'KTFY_yext_settings_init');
add_action('admin_init', 'KTFY_yext_custom_field_settings_init');
add_action('admin_init', 'KTFY_yext_shortcode_settings_init');



/**
 * custom option and settings:
 * callback functions
 */

// developers section cb

// adds the user input variables
function KTFY_yext_section_developers_cb($args)
{
    ?>
   <p id="<?php echo esc_attr($args['id']); ?>"><?php esc_html_e('Use shortcodes to embed your Yext content into your pages and posts', 'KTFY-yext-schema'); ?></p>
   <?php
   $location_error = get_option("KTFY_yext_location_error_option");
   $schema_error = get_option("KTFY_yext_schema_error_option");
   if (gettype($location_error)=="string") {
  ?>
    <div class="notice notice-error is-dismissible">
      <p><?php esc_html_e("ERROR: ". $location_error, 'KTFY-yext-schema'); ?></p>
    </div>
  <?php
   }
  if (gettype($schema_error)=="string") {
  ?>
    <div class="notice notice-error is-dismissible">
      <p><?php esc_html_e("ERROR: ". $schema_error, 'KTFY-yext-schema'); ?></p>
    </div>
  <?php
  }
}

/**
* populates the custom fields
*/
function KTFY_yext_section_custom_field_cb($args)
{
    ?>
  <p id="<?php echo esc_attr($args['id']); ?>"><?php esc_html_e("Name: yext-knowledge-tags-location-id", 'KTFY_yext_schema_custom_field'); ?></p>
  <p id="<?php echo esc_attr($args['id']); ?>"><?php esc_html_e("Value: {your location ids}", 'KTFY_yext_schema_custom_field'); ?></p>
  <br/>
  <p id="<?php echo esc_attr($args['id']); ?>"><?php esc_html_e("To Enable Customs Fields:", 'KTFY_yext_schema_custom_field'); ?></p>
  <p id="<?php echo esc_attr($args['id']); ?>"><?php esc_html_e("1. Navigate to the 'Edit' page of your Wordpress page.", 'KTFY_yext_schema_custom_field'); ?></p>
  <p id="<?php echo esc_attr($args['id']); ?>"><?php esc_html_e("2. Select 'Screen Options' from the upper right hand corner of the screen.", 'KTFY_yext_schema_custom_field'); ?></p>
  <p id="<?php echo esc_attr($args['id']); ?>"><?php esc_html_e("3. Make sure 'Custom Fields' is checked.", 'KTFY_yext_schema_custom_field'); ?></p>
  <p id="<?php echo esc_attr($args['id']); ?>"><?php esc_html_e("4. Navigate to the bottom of the page and add your custom fields.", 'KTFY_yext_schema_custom_field'); ?></p>
  <br/>
  <p id="<?php echo esc_attr($args['id']); ?>"><?php esc_html_e("Note: each location id needs its own custom field entry. Only supply location id if it is being used on the page.", 'KTFY_yext_schema_custom_field'); ?></p>
  <?php
}

/**
* populates the shortcode section
*/
function KTFY_yext_section_shortcodes_cb($args)
{
    ?>
 <p id="<?php echo esc_attr($args['id']); ?>"><?php esc_html_e('[yext-data yext-location-id=1001 yext-field=locationName]', 'KTFY_yext_schema_shortcodes'); ?></p>
 <?php
 $host = KTFY_get_yext_host_name();
 $supported_field_url = sprintf("%s/supportedfields", $host);
 $account_options = get_option('KTFY_yext_sub_account_options');
 $supported_fields = @file_get_contents($supported_field_url);
 $KTFY_knowlege_tag_supported_fields = json_decode($supported_fields);
 $fields = array();
 foreach ($KTFY_knowlege_tag_supported_fields as $key => $value) {
    $fields[$key] = $value;
 }
 ?>

 <p class="">
   <?php esc_html_e('Accepted Shortcode Fields', 'KTFY_yext_schema_shortcodes'); ?>
</p>
<table id="yext-shortcode-table">
  <tr>
    <th>Yext Field Name</th>
    <th>Description</th>
    <th>Yext Admin Name</th>
  </tr>

  <?php
  $array = array_values($fields);
    for ($tr=0; $tr<count($fields); $tr++) {
        ?>
     <tr>
       <td> <?php echo $array[$tr][0] ?> </td>
       <td> <?php echo $array[$tr][1] ?> </td>
       <td> <?php echo $array[$tr][2] ?> </td>
     </tr>
     <?php
    } ?>
</table>
 <?php
}

// key field cb
function KTFY_yext_key_cb($args)
{
    // get the value of the setting we've registered with register_setting()
    $options = get_option('KTFY_yext_key_options');
    // output the field?>
 <input id="<?php echo esc_attr($args['label_for']); ?>"
 data-custom="<?php echo esc_attr($args['KTFY_yext_custom_data']); ?>"
 name="KTFY_yext_key_options[<?php echo esc_attr($args['label_for']); ?>]"
 type="text"
 value="<?php echo esc_attr($options[ $args['label_for']]); ?>"
 >
 <p class="description">
   <?php esc_html_e('Read-Only Live API Key', 'KTFY-yext-schema'); ?>
</p>
 <?php
}

// sub account cb
function KTFY_yext_sub_account_cb($args)
{
    $account_options = get_option('KTFY_yext_sub_account_options'); ?>
  <input id="<?php echo esc_attr($args['label_for']); ?>"
  data-custom="<?php echo esc_attr($args['KTFY_yext_custom_data']); ?>"
  name="KTFY_yext_sub_account_options[<?php echo esc_attr($args['label_for']); ?>]"
  type="text"
  value="<?php echo esc_attr($account_options[ $args['label_for']]); ?>"
  >
  <p class="description">
    <?php esc_html_e('Sub-Account ID for the account containing these location(s).', 'KTFY-yext-schema'); ?>
 </p>
  <?php
}

/**
 * top level menu
 */
function KTFY_yext_options_page()
{
    // add top level menu page
    add_menu_page(
 'Knowledge Tags from Yext',
 'Knowledge Tags from Yext',
 'manage_options',
 'knowlege_tags_from_yext',
 'KTFY_yext_options_page_html',
 plugin_dir_url(__FILE__) . 'public/images/yext-wp-icon-light.png'
 );
}

/**
 * register our KTFY_yext_options_page to the admin_menu action hook
 */
add_action('admin_menu', 'KTFY_yext_options_page');

/**
 * top level menu:
 * callback functions
 */
function KTFY_yext_options_page_html()
{
    // check user capabilities
    if (! current_user_can('manage_options')) {
        return;
    }
    // check if the user have submitted the settings
    // wordpress will add the "settings-updated" $_GET parameter to the url
    if (isset($_GET['settings-updated'])) {
        // add settings saved message with the class of "updated"
        add_settings_error('KTFY_yext_messages', 'yext_message', __('Settings Saved', 'KTFY_yext_schema'), 'updated');
    }
    // show error/update messages
 settings_errors('KTFY_yext_messages'); ?>
 <div class="wrap">
 <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
 <form action="options.php" method="post">
 <?php
 // output security fields for the registered setting "KTFY_yext_schema"
    settings_fields('KTFY_yext_schema');
    do_settings_sections('KTFY_yext_schema');
    submit_button('Save Settings'); ?>
 </form>
 <h2>Custom Fields </h2>
 <?php
    settings_fields('KTFY_yext_schema_custom_field');
    do_settings_sections('KTFY_yext_schema_custom_field'); ?>
 <h2>Shortcodes</h2>

 <?php
    settings_fields('KTFY_yext_schema_shortcodes');
    do_settings_sections('KTFY_yext_schema_shortcodes'); ?>
 </div>
 <?php
}
