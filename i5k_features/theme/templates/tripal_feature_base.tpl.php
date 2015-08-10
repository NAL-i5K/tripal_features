<?php
$feature  = $variables['node']->feature; 
//echo "<pre>"; print_r($feature);echo "</pre>";

//VIJAYA - To retrieve synonym  data
$options = array('return_array' => 1);
$synonym = chado_expand_var($feature, 'table', 'feature_synonym', $options);

/* Building feature Synonym  */
$feature_synonym = "NA";
if(isset($synonym->feature_synonym) && !empty($synonym->feature_synonym)) {
  $feature_synonym = "";
  foreach($synonym->feature_synonym as $synonym_obj) {  
    $feature_synonym .= $synonym_obj->synonym_id->name."<br>"; 
  }
} 

// Location featureloc sequences
$featureloc_sequences = i5k_features_feature_alignments($variables);
$location = '';
if(count($featureloc_sequences) > 0){
  foreach($featureloc_sequences as $src => $attrs){
    $location = $attrs['location'];
  }
}  
//VIJAYA - To display the comments (Note) of all mRNA's, rRNA etc., and transcripts
$relationship = tripal_feature_get_feature_relationships($feature);

//Display transcript feature(s) count like rRNA, mRNA etc., data dynamically..
$transcript_count = '';
if(isset($relationship['object']['part of'])) {
foreach($relationship['object']['part of'] as $key => $relationship_obj) {
 // if(isset($relationship_obj)) {
    $transcript_count = "<div id='transcript_item'><div id='transcript_value'>This gene contains&nbsp;</div><div class='tripal_toc_list_item'><a id='transcripts' class='tripal_toc_list_item_link' href='?pane=transcripts' >".count($relationship_obj);
    $transcript_count .= (count($relationship_obj) > 1)?" ".$key."s</a></div></div>":" ".$key."</a></div></div>";
 // }

  //Comment(s)    
  $comment = "None";  
  //type_id = 85 means a comment. "Note" value in field "name".
  $gene_select = array('feature_id' => $feature->feature_id, 'type_id' => 85);
  $gene_columns = array('value', 'feature_id');
  $gene_featureprop = chado_select_record('featureprop', $gene_columns, $gene_select);  
  if(isset($gene_featureprop) && !empty($gene_featureprop)) {
	$i = 0; $comment = "";
	foreach($gene_featureprop as $key => $gene_propobj) {
	  ($i == 0)? $user_comment .= 'Note: ':'';
	  $comment .= $gene_propobj->value."; ";
	  $i++; 
	}
    $comment = rtrim($comment, '; ');
  }

}
} // if closing

//VIJAYA gene_var variable is used to differentiate the gene and mRNA pages
$gene_var = array('gene', 'pseudogene');

 ?>

<div class="tripal_feature-data-block-desc tripal-data-block-desc" style='color:red;'></div>
 <?php
 
// the $headers array is an array of fields to use as the colum headers. 
// additional documentation can be found here 
// https://api.drupal.org/api/drupal/includes%21theme.inc/function/theme_table/7
// This table for the analysis has a vertical header (down the first column)
// so we do not provide headers here, but specify them in the $rows array below.
$headers = array();

// the $rows array contains an array of rows where each row is an array
// of values for each column of the table in that row.  Additional documentation
// can be found here:
// https://api.drupal.org/api/drupal/includes%21theme.inc/function/theme_table/7 
$rows = array();

// Organism scientific name row
$target = array(
    'attributes' => array(
      'target' => '_blank'
    ),
  );
$organism_name = l($feature->organism_id->genus.' '.$feature->organism_id->species, "node/" . $feature->organism_id->nid, $target);

$rows[] = array(
  array(
    'data' => 'Organism',
    'header' => TRUE,
    'width' => '20%',
  ),
  $organism_name
);

// Name row
$rows[] = array(
  array(
    'data' => 'Gene ID',
    'header' => TRUE,
    'width' => '20%',
  ),
  $feature->uniquename
);
// Unique Name row
$rows[] = array(
  array(
    'data' => 'Gene Name',
    'header' => TRUE
  ),
  $feature->name
);
if(in_array($feature->type_id->name, $gene_var)) {
// Synonyms row
 $rows[] = array(
    array(
      'data' => 'Synonyms',
      'header' => TRUE
    ),
    $feature_synonym
  );
	
// Location row
  $rows[] = array(
    array(
      'data' => 'Location',
      'header' => TRUE
    ),
   $location
  );    

// Transcript row
$rows[] = array(
   array(
     'data' => 'Transcripts',
     'header' => TRUE
   ),
   $transcript_count
);

// Analyses  
$feature = chado_expand_var($feature, 'table', 'analysisfeature', $options);
$analyses = $feature->analysisfeature[0];
	  
$src_values = array('feature_id' => $feature->featureloc->feature_id[0]->srcfeature_id->feature_id);
$srcfeature = chado_generate_var('analysisfeature', $src_values, $options);
	  
// Analyses - if and else statements cause the anaylses having different array formats.
$analysis_name = '';
if(count($analyses) == 2) {          
  foreach($analyses as $analysis) {  
    $a_name = $analysis->analysis_id->name;
    if (property_exists($analysis->analysis_id, 'nid')) {
	  //$options['target'] = '_blank';
      $analysis_name .= l($a_name, "node/" . $analysis->analysis_id->nid)."<bR>";  
    }   
  }   
} else {
  $a_name = $analyses->analysis_id->name;
  if (property_exists($analyses->analysis_id, 'nid')) {
	//$options['target'] = '_blank';
    $analysis_name .= l($a_name, "node/" . $analyses->analysis_id->nid)."<bR>";  
  }   
}
  
// Source name comes under analysis 
if(isset($srcfeature[0]->analysis_id) && count($srcfeature[0]->analysis_id)) {
  $src_analysis = $srcfeature[0]->analysis_id;
  $a_name = $src_analysis->name;
  $analysis_name .= "Source: ". l($a_name, "node/" . $src_analysis->nid);
} 
  
// Analysis row
$rows[] = array(
         array(
          'data' => 'Analysis',
          'header' => TRUE
        ),
        $analysis_name
);
	  
//Comment row
$rows[] = array(
   array(
     'data' => 'Annotator Comments',
     'header' => TRUE
   ),
   $comment
);
  
}  //END of if condition for gene page
else { 
// Type row
$rows[] = array(
  array(
    'data' => 'Type',
    'header' => TRUE
  ),
  $feature->type_id->name
);

 // Organism row
 $organism = $feature->organism_id->genus ." " . $feature->organism_id->species ." (" . $feature->organism_id->common_name .")";
 if (property_exists($feature->organism_id, 'nid')) {
   $organism = l("<i>" . $feature->organism_id->genus . " " . $feature->organism_id->species . "</i> (" . $feature->organism_id->common_name .")", "node/".$feature->organism_id->nid, array('html' => TRUE));
 } 
 $rows[] = array(
   array(
     'data' => 'Organism',
     'header' => TRUE,
   ),
   $organism
 );
} // gene if condition ends

// Seqlen row
if($feature->seqlen > 0) {
  $rows[] = array(
    array(
      'data' => 'Sequence length',
      'header' => TRUE,
    ),
    $feature->seqlen
  );
}
// allow site admins to see the feature ID
/* VIJAYA - As per the new design 2014 commenting Feature ID row
if (user_access('view ids')) { 
  // Feature ID
  $rows[] = array(
    array(
      'data' => 'Feature ID**',
      'header' => TRUE,
      'class' => 'tripal-site-admin-only-table-row',
    ),
    array(
      'data' => $feature->feature_id,
      'class' => 'tripal-site-admin-only-table-row',
    ),
  );
}
*/
// Is Obsolete Row
if($feature->is_obsolete == TRUE){
  $rows[] = array(
    array(
      'data' => '<div class="tripal_feature-obsolete">This feature is obsolete</div>',
      'colspan' => 2
    ),
  );
}

// the $table array contains the headers and rows array as well as other
// options for controlling the display of the table.  Additional
// documentation can be found here:
// https://api.drupal.org/api/drupal/includes%21theme.inc/function/theme_table/7
$table = array(
  'header' => $headers,
  'rows' => $rows,
  'attributes' => array(
    'id' => 'tripal_feature-table-base',
    'class' => 'tripal-data-table'
  ),
  'sticky' => FALSE,
  'caption' => '',
  'colgroups' => array(),
  'empty' => '',
);

// once we have our table array structure defined, we call Drupal's theme_table()
// function to generate the table.
print theme_table($table); 

 $iframe_src = "https://apollo.nal.usda.gov/"; //"http://gmod-dev.nal.usda.gov:8080/";
     
  // nal abbreviation = first 3 letters of genus + first 3 letters of species
  $nal_abbreviation = strtolower(substr($feature->organism_id->genus, 0, 3) .  substr($feature->organism_id->species, 0, 3));
     
  $current_model = $nal_abbreviation."_current_models";

  $iframe_location = !empty($location)?$location:'';	 
  
  $nav = '&tracklist=1&overview=0';

  $iframe_src = $iframe_src . $nal_abbreviation . "/jbrowse/?loc=" . $iframe_location .$nav. "&tracks=DNA%2CAnnotations%2C" . $current_model."&hightlight";  
  //print $iframe_src;
?>    
<!--
   <div class="organism-iframe">
   <iframe src="<?php print $iframe_src; ?>" style="width: 100%; height: 650px" marginwidth="0" marginheight="0" frameborder="0" vspace="0" hspace="0" ></iframe>    
   </div>-->


