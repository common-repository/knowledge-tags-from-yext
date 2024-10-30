<?php
/**
 * @package Yext_Schema
 * @version 1.0.5
 */
/*
Plugin Name: Knowledge Tags from Yext
Plugin URI: http://www.wordpress.org/plugins/knowledge-tags-from-yext
Description: Publish your knowledge tags.
Author: Yext Engineering
Version: 1.0.5
*/
include "settings.php";
global $KTFY_page_location_Ids;
$KTFY_page_location_Ids=array();

/**
* gets the user defined variables and queries against api
* gets the location and schema data.
*/
function KTFY_get_kt_data()
{
    global $KTFY_knowlege_tag_data;
    global $KTFY_yext_schema_data;
    global $KTFY_page_location_Ids;
    // get user variables
    $locationIds = @array_values(get_post_custom_values('yext-knowledge-tags-location-id'));
    $apikey = trim(@array_values(get_option('KTFY_yext_key_options'))[0]);
    $account_Id = trim(@array_values(get_option('KTFY_yext_sub_account_options'))[0]);
    $KTFY_page_location_Ids = $locationIds;
    $KTFY_yext_schema_data = new StdClass;
    $KTFY_knowlege_tag_data = new StdClass;
    delete_option('KTFY_yext_location_error_option');

    if (is_null($account_Id) || $account_Id == ""){
      $account_Id = "me";
    }

    if (!is_null($locationIds) && count($locationIds) >= 1) {
        foreach ($locationIds as $locationId) {
            if (!is_null($locationId) && !is_null($apikey) && !is_null($account_Id)) {
                $locationId = trim($locationId);
                $KTFY_knowlege_tag_data->{$locationId} = new StdClass;
                $data = KTFY_get_yext_data($apikey, $account_Id, $locationId);
                if ($data) {
                    $KTFY_yext_schema_data->{$locationId} = $data->{"locations"}[0]->{"schema"};
                    $KTFY_knowlege_tag_data->{$locationId} = $data->{"locations"}[0]->{"location"};
                }
            }
        }
    }
}

// querying for data
function KTFY_get_yext_data($apiKey, $account_Id, $locationId)
{
    $host = KTFY_get_yext_host_name();
    $schema_url = sprintf("%s/jsondata?account_id=%s&location_id=%s&key=%s", $host, urlencode($account_Id), urlencode($locationId), urlencode($apiKey));
    $schema_data = @file_get_contents($schema_url);
    if (!$schema_data){
      //display error here
      echo "<h3 style='color:red;'> ERROR: Unable to retrieve Knowledge Tags - Please check your plugin configuration and your server's network configuration. If this issue persists please contact the plugin authors and/or your network admin.</h3>";
    }
    $schema_data = json_decode($schema_data);
    return $schema_data;
}

function KTFY_get_yext_host_name(){
    $host = @$_GET['host'];
    if (!$host) {
        $host = @$_ENV['YEXT_KTAGS_SERVING_HOST'];
    }
    if (!$host) {
        return "https://knowledgetags.yextpages.net";
    }
    return $host;
}

// add shortcode
function KTFY_yext_shortcode($atts, $content, $tag)
{
    KTFY_get_kt_data();
    global $KTFY_knowlege_tag_data;

    if (is_string ($atts) or !array_key_exists("yext-field", $atts) or !array_key_exists("yext-location-id", $atts)) {
      update_option('KTFY_yext_location_error_option', "parameters not found, please check all inputs.");
      return "";
    }

    $field_name = $atts["yext-field"];
    $locationId = $atts["yext-location-id"];

    if ($locationId == NULL) {
      $locationId = explode('=', $atts[0])[1];
    }
    if ($field_name == NULL) {
      $field_name = explode('=', $atts[1])[1];
    }

    if ($field_name == NULL or $locationId == NULL) {
      $option_value = get_option('KTFY_yext_location_error_option');
      update_option('KTFY_yext_location_error_option', "Unable to parse arguments, please double check all inputs. Otherwise, contact engineering.");
      return "";
    }

    if ($KTFY_knowlege_tag_data == NULL or $KTFY_knowlege_tag_data->{$locationId} == NULL) {
      add_option('KTFY_yext_location_error_option', 'Location Data is Empty');
      return "";
    }
    if (array_key_exists($field_name, $KTFY_knowlege_tag_data->{$locationId})){
      $response = @$KTFY_knowlege_tag_data->{$locationId}->{$field_name};
    } else {
      update_option("KTFY_yext_location_error_option", $field_name . " is not present in this location, please double check if this field is still supported or contact engineering.");
      return "";
    }
    return $response;
}

// add schema
function KTFY_yext_schema_org_json()
{
    KTFY_get_kt_data();
    global $KTFY_yext_schema_data;
    global $KTFY_page_location_Ids;

    if (!is_null($KTFY_yext_schema_data) && !is_null($KTFY_page_location_Ids) && count($KTFY_page_location_Ids) >= 1) {
        echo '<script type="application/ld+json">'."\r\n";
        foreach ($KTFY_page_location_Ids as $locationId) {
            $ld_json = @json_encode($KTFY_yext_schema_data->{$locationId}, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            // only write to html if we have data
            if (!is_null($ld_json) && $ld_json !="null") {
                echo $ld_json;
            }
        }
        echo "\r\n".'</script>';
        delete_option('KTFY_yext_schema_error_option');
    } elseif (is_null($KTFY_yext_schema_data) && !is_null($KTFY_page_location_Ids)) {
        add_option('KTFY_yext_schema_error_option', 'Schema Data is Empty for '+ get_the_title());
    }
}

//action hooks
add_shortcode('yext-data', 'KTFY_yext_shortcode');
add_action('get_footer', 'KTFY_yext_schema_org_json');
