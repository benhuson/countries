<?php

/*
Plugin Name: Countries
Plugin URI: http://wordpress.org/extend/plugins/countries/
Description: Import and manage a list of countries into your WordPress site as Custom Post Types.
Version: 1.0
Author: Instinct
Author URI: http://getshopped.org
License: GPL2
*/


/* add importer admin pages */
if(is_admin()){
	//Add XML Importer admin page
	function country_admin_pages(){
		require_once("admin-pages/display-country-admin.php");
		$base_page = 'countries-importer';
		if (function_exists('add_object_page')) {

			add_object_page(__('Countries Importer', 'countries-importer'), __('Countries Importer', 'countries-importer'), 2, 'countries_importer_page');
			
		} 
		add_submenu_page($base_page, __('Countries Importer','countries-importer'),  __('Countries Importer','countries-importer'), 7, 'countries_importer_page', 'countries_importer_page');
	
	}
	add_action('admin_menu', 'country_admin_pages');
	
	
	
	/**
	 * countries_posts_orderby
	 *
	 * Forces countries to default to order alphabetically in the admin rather
	 * than by publish date. Does this as priority 0 so that by default any other
	 * filters will override this.
	 *
	 * Should we do this by default elsewhere, not just the admin?
	 * 
	 * @since 1.0.x
	 *
	 * @param $order (string) The SQL order statement.
	 */
	function countries_posts_orderby( $order ) {
	
		global $wpdb;
		
		if ( get_query_var( 'post_type' ) == 'countries' ) {
			return "$wpdb->posts.post_title ASC";
		}
		
		return $order;
		
	}
	add_filter( 'posts_orderby', 'countries_posts_orderby', 0, 1 );
	
	
	
}


/* create custompost type and custom taxonomy */
function country_init() {	

	$labels = array(
		'name' => _x( 'Countries', 'post type general name' ),
		'singular_name' => _x( 'Country', 'post type singular name' ),
		'add_new' => _x( 'Add New', 'Country' ),
		'add_new_item' => __( 'Add New Country' ),
		'edit_item' => __( 'Edit Country' ),
		'new_item' => __( 'New Country' ),
		'view_item' => __( 'View Country' ),
		'search_items' => __( 'Search Countries' ),
		'not_found' =>  __( 'No Countries found' ),
		'not_found_in_trash' => __( 'No Countries found in Trash' ), 
		'parent_item_colon' => ''
	);

	$args = array(
		'labels' => $labels,
		'public' => true,
		'publicly_queryable' => true,
		'show_ui' => true,  
		'query_var' => true,
		'rewrite' => true,
		'capability_type' => 'post',
		'hierarchical' => false,
		'show_in_nav_menus'=>false,
		'menu_position' => null,
		'supports' => array('title'),
		'visible' =>true,
		'register_meta_box_cb' => 'country_meta_boxes'
	);
	
	// http://codex.wordpress.org/Function_Reference/register_post_type
	register_post_type('countries', $args);
}


////Function to customise table headings
//function custompostColumns($columns){
//    
//$columns = array(
//        "cb"          => "<input type=\"checkbox\" />",
//        "title"       => "Country",
//        //"countrycode" => "Country Code"
//		//"countryinfo" => "Country Details",
//        //"flag"   => "Flag",
//    );
//    return $columns;
//}
//
////Function to customise table data
//function custonpostRowValues($column){
//	global $post;
//	
//	switch ($column) {
//        case 'ID':
//            // Print post title.
//            print $post->ID;
//            break;
//      //  case 'countrycode':
//            // Print the post content
//         //   print $post->post_content;
//         //   break;
//     //    case 'countryinfo':
//            // Print the post content
//          //  print $post->post_content;
//          //  break;
//      //  case 'metavalue':
//            // Extract the metadata field and print value here.
//          //  echo get_metadata('post', $post->ID, 'custompost_subtitle', true);
//           // break;
//    }
//
//
//}
//creates custom meta boxes
function country_meta_boxes() {	

	add_meta_box("countries_meta", "Quick link - All Countries", "render_countries_meta", "countries", "side", "low");
	add_meta_box("countrycode_meta", "Country code", "render_countrycode_meta", "countries", "normal", "low");
	add_meta_box("flags_meta", "Country Flag", "render_flags_meta", "countries", "normal", "low");
	add_meta_box("currency_meta", "Country Currency", "render_currency", "countries", "normal", "low");
	add_meta_box("cities_meta", "Country Cities", "render_cities", "countries", "normal", "low");
	add_meta_box("details_meta", "Country Notes", "render_details", "countries", "normal", "low");
}

 function loadCountriesFromXML()
	{
		$doc = new DOMDocument();
		$doc->load(WP_CONTENT_DIR.'/plugins/country/xml/countrylist.xml' );
		
		$rootnode = $doc->getElementsByTagName('countries')->item(0); 
		
		$arrayofcountries = array(); 
		$arrayposition = 0;
		foreach ($rootnode->getElementsByTagName('country') as $countryitem) {
		
			
		
			$arrayattributes = array();
            if($countryitem->hasAttributes()){
		          $xmlattributes = $countryitem->attributes;
		          if(!is_null($xmlattributes)){
		            
		              foreach ($xmlattributes as $index=>$attr) {
		                  $arrayattributes[$attr->name] = $attr->value;
		              }
		          }
		      }
		      $arrayofcountries[$arrayposition][0] = $arrayattributes;
		
			
		     $citysubnodes = array();
		        if($countryitem->childNodes->length) {
		        $cityarrpos = 0;
		            foreach($countryitem->childNodes as $city) {

		            //echo count($city->attributes) . ',';
		            $cityarrayattributes = array();
				            if($city->hasAttributes()){
				          //  echo 'has attributes'; 
				            
						          $xmlattributes = $city->attributes;
						          if(!is_null($xmlattributes)){
						            
						              foreach ($xmlattributes as $index=>$attr) {
						              	//echo $attr->value;
						                  $cityarrayattributes[$attr->name] = $attr->value;
						                 // echo $cityarrayattributes[$attr->name] . 'xx';
						                 // echo $attr->value;
						              }
						          }
						          
						          $citysubnodes[$cityarrpos] = $cityarrayattributes;
		   						$cityarrpos++;
						      }
				
		            }
		        }
		        
		        
		        
		        $arrayofcountries[$arrayposition][1] = $citysubnodes;  
		       // echo count($arrayofcountries[$arrayposition][1]);
		       $arrayposition++;
		}
		
	
		return $arrayofcountries;
	}
	
	

///Displays the Country code
function render_countrycode_meta() {
  global $post;

  $custom = get_post_custom($post->ID); 
    $country_code = $custom['country_code'][0];
	?>
	<input name="country_code" value="<?php echo $country_code ?>"/></p>
	<?php 
}

///Displays the Country currency
function render_currency() {
  global $post;

  $custom = get_post_custom($post->ID); 
    $currency = $custom['country_currency'][0];
    $currencyCode= $custom['currency_code'][0];
    $currencySymbol= $custom['currency_symbol'][0];
    $currencySymbolhtml= $custom['currency_html'][0];
    //$currency = loadCurrencyFromXML();

	?>
	<p><label for="country_symbol" style="width:145px; margin-top:6px; float:left; display: block"><strong>Currency: </strong></label><input class="regular-text" name="country_currency" value="<?php echo $currency?>" /></p>
	<p><label for="country_symbol" style="width:145px; margin-top:6px; float:left; display: block"><strong>Currency Code: </strong></label><input name="currency_code" value="<?php echo $currencyCode?>" /></p>
	<p><label for="country_symbol" style="width:145px; margin-top:6px; float:left; display: block"><strong>Currency Symbol: </strong></label><input name="currency_symbol" value="<?php echo $currencySymbol?>"/></p>
	<p><label for="country_html" style=" width:145px; margin-top:6px; float:left; display: block""><strong>Currency Symbol (HTML): </strong></label><input name="currency_html" style=" -moz-border-radius:4px 4px 4px 4px;" value="<?php echo htmlentities($currencySymbolhtml)?>" /></p>
	
	
	<?php 
}

////Displays the DDL of countries


////Displays the DDL of countries
function render_cities() {
global $post;
$custom = get_post_custom($post->ID); 
$countrycode = $custom["country_code"][0];
$citycode = $custom["city_list"][0];

$arrayofcountries = loadCountriesFromXML();


for ($numerofcountries = 0; $numerofcountries <= count($arrayofcountries) -1 ; $numerofcountries++)
{
	if ($countrycode == $arrayofcountries[$numerofcountries][0]['countrycode'] && count($arrayofcountries[$numerofcountries][1]) > 0) //if the country has cities
	{

		?>
		<select name="city_list" style="width:265px;">
		<?php
	
		for ($nocities = 0; $nocities <= count($arrayofcountries[$numerofcountries][1]) -1; $nocities++)
		{
			if ($citycode == $arrayofcountries[$numerofcountries][1][$nocities]['code'])
			{
				echo '<option selected="selected" value="' . $arrayofcountries[$numerofcountries][1][$nocities]['code'] . '">' . $arrayofcountries[$numerofcountries][1][$nocities]['name'] . '</option>';
			}
			else
			{
				echo '<option value="' . $arrayofcountries[$numerofcountries][1][$nocities]['code'] . '">' . $arrayofcountries[$numerofcountries][1][$nocities]['name'] . '</option>';
			}
		}
	
		?>
		</select>
		<?php
		//echo "<p>If you would like to add more cities for this country follow the template in the xml folder</p>";
	}



}

echo "<p>To add cities to a country please follow the template in the xml folder</p>";
}

	
////Displays the DDL of countries
function render_countries_meta() {
	global $post;
	
	$custom = get_post_custom($post->ID); 
	$countrylist = $custom["country_list"][0];
	$arrayofcountries = loadCountriesFromXML();
	
	?>
	 <select name="country_list" style="width:265px;">
	<?php 
	
	for ($numerofcountries = 0; $numerofcountries <= count($arrayofcountries) -1 ; $numerofcountries++)
	{
		
		if ($countrycode == $arrayofcountries[$numerofcountries][0]['countrycode'])
		{
		 echo '<option selected="selected" value="' . $arrayofcountries[$numerofcountries][0]['countrycode'] . '">' . $arrayofcountries[$numerofcountries][0]['countryname'] . '</option>';
		}
		else
		{
			echo '<option value="' . $arrayofcountries[$numerofcountries][0]['countrycode'] . '">' . $arrayofcountries[$numerofcountries][0]['countryname'] . '</option>';
		}
	} ?>
	 </select>
	<?php
	echo "<p>Quick links to countries are in progress!</p>";
}

//Displays the flags
function render_flags_meta() {
	global $post;

	$custom = get_post_custom($post->ID); 
	$flag = $custom["country_code"][0];
		
	?><img name="flags" src=" <?php echo WP_CONTENT_URL.'/plugins/country/flags/'.strtolower($flag).'.gif' ?>" />
	<?php 
	}

///Displays the details box
function render_details() {
  global $post;

  $custom = get_post_custom($post->ID); 
  $details = $custom["country_details"][0];
	?>
	<textarea cols="50" rows="5" name="country_details"><?php echo $details;?></textarea>
	<?php 

  
}

//saves metabox updates
function save_details(){
global $post;
 
	update_post_meta($post->ID, "country_code", $_POST["country_code"]);
	update_post_meta($post->ID, "country_list", $_POST["country_list"]);
	update_post_meta($post->ID, "flags", $_POST["flags"]);
	update_post_meta($post->ID, "country_details", $_POST["country_details"]);
	update_post_meta($post->ID, "country_currency", $_POST["country_currency"]);
	update_post_meta($post->ID, "currency_symbol", $_POST["currency_symbol"]);
	update_post_meta($post->ID, "currency_html", $_POST["currency_html"]);
	update_post_meta($post->ID, "currency_code", $_POST["currency_code"]);
	update_post_meta($post->ID, "city_list", $_POST["city_list"]);

}

//hides posts on main page
function hide_posts()
{
	global $query_string;
	if (!is_admin()){
		query_posts($query_string . "&post_type!=countries");
	}
}

///this function deletes all countries from DB when plug in is deactivated xml query correct in php my admin but not in wp... temp table not supported maybe?
//function flushDB(){
//    global $wpdb;
////    
// $query="CREATE TEMPORARY TABLE posts_with_metadata
//SELECT ID
//FROM $wpdb->posts
//LEFT JOIN $wpdb->postmeta ON wp_postmeta.post_id = wp_posts.ID
////WHERE meta_key = 'country_details';
////
////CREATE TEMPORARY TABLE posts_to_delete
////SELECT ID FROM $wpdb->posts where post_type='countries' and ID not in (SELECT ID from posts_with_metadata);
////
////DELETE FROM $wpdb->posts where post_type='countries' and ID not in (SELECT ID from posts_with_metadata);
////
////DELETE FROM $wpdb->postmeta where post_id in (SELECT ID from posts_to_delete);";
////
////	$wpdb->query($wpdb->prepare( $query));
//}


//Action hooks, attaching functions to application events
	add_action('init', 'country_init');
	add_action('save_post', 'save_details');
	add_action("parse_request", "hide_posts");
	//register_deactivation_hook( __FILE__, 'flushDB' );
	//add_filter("manage_edit-custompost_columns", "custompostColumns");
	//add_action("manage_posts_custom_column", "custonpostRowValues");
	
	
///futher version ideas:
//1.Fix pagintation
//2.Hook into plugin activation and load the xml file from instinct servers instead of delivering it with the plugin
// and then copy the images locally and insert the xml contents to their local db, 
//so we can update the countries on an adhoc basis.
//3.Fix DB query to work with wpress
//4.Update flag file name when country code is changed IMPORTANT
		
?>
