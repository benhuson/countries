<?php
function countries_importer_page(){ 

include_once WP_CONTENT_DIR.'/plugins/countries/includes/xml-parser.php';
$xml = new Countries_XML_Parser();
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