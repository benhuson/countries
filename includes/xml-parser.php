<?php
class xmlParser 
{

	//Loads countries from xml file
 function loadCountriesFromXML()
	{
		$doc = new DOMDocument();
		$doc->load(WP_CONTENT_DIR.'/plugins/countries/xml/countrylist.xml' );
		
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
	
	
function get_post_by_title($page_title, $output = OBJECT) {
    global $wpdb;
        $post = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_title = %s AND post_type='countries' AND post_status='publish'", $page_title ));
        if ( $post )
            return get_post($post, $output);
 
    return null;
}
	
	///Writing countires to the DB
	function SaveInitialCountries(){
	
		$arrayofcountries = $this->loadCountriesFromXML();
		for ($numerofcountries = 0; $numerofcountries <= count($arrayofcountries) -1 ; $numerofcountries++)
		{
			
			$post = $this->get_post_by_title($arrayofcountries[$numerofcountries][0]['countryname'], ARRAY_A);
			
			if(empty($post))
			{
				$insert = array();
				$insert['post_title'] = $arrayofcountries[$numerofcountries][0]['countryname'];
				$insert['post_content'] = $arrayofcountries[$numerofcountries][0]['countrycode'];
				$insert['post_status'] = 'publish';
				$insert['post_author'] = 1;
				$insert['post_type'] = 'countries';
				
				$the_content .= "<h1>IMPORTED</h1><p>Country Name: " . $arrayofcountries[$numerofcountries][0]['countryname'] . "<br/>Country Code: " . $arrayofcountries[$numerofcountries][0]['countrycode'] . "</p><hr/>";

				$insertedPostId = wp_insert_post( $insert ); 
				update_post_meta($insertedPostId, "country_code", $arrayofcountries[$numerofcountries][0]['countrycode']);	
				update_post_meta($insertedPostId, "country_currency", $arrayofcountries[$numerofcountries][0]['monetarylongname']);	
				update_post_meta($insertedPostId, "currency_symbol", $arrayofcountries[$numerofcountries][0]['monetarysymbol']);	
				update_post_meta($insertedPostId, "currency_html", $arrayofcountries[$numerofcountries][0]['monetaryhtmlcode']);	
				update_post_meta($insertedPostId, "currency_code", $arrayofcountries[$numerofcountries][0]['monetarycode']);									
			}		
		  
		}
		
		echo "COUNTRIES IMPORTER" .  print_r($the_content,true);
	}
	    
}
?>