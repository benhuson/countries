<?php
function countries_importer_page(){ 

include_once WP_CONTENT_DIR.'/plugins/countries/includes/xml-parser.php';
$xml = new xmlParser();
?>
<div class='wrap'>
<h2>Country Importer</h2>
<?php 

$xml->SaveInitialCountries();


?>


</div>

<?php
}
?>