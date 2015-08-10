<?php
/*
 * There are several ways that sequences can be displayed.  They can come from the 
 * feature.residues column,  they can come from an alignment with another feature,
 * they can come from a protein sequence that has relationship with this sequence,
 * or they can come from sub children (e.g. CDS coding sequences).
 *   
 * This template will show all types depending on the data available.
 *
 */

$feature = $variables['node']->feature;
$gene_var = array('gene', 'pseudogene');
if(in_array($node->feature->feature_relationship->object_id[0]->object_id->type_id->name, $gene_var)) {  
  $all_relationships = $feature->all_relationships;
  $object_rels = $all_relationships['object'];
  $subject_rels = $all_relationships['subject'];

  //VIJAYA - generating sequence as per new ppt
if (count($object_rels) > 0 or count($subject_rels) > 0) { ?>
  <div class="tripal_feature-data-block-desc tripal-data-block-desc"></div> <?php
  
  // first add in the subject relationships.  
  foreach ($subject_rels as $rel_type => $rels){
    foreach ($rels as $obj_type => $objects){ 
     
      // the $headers array is an array of fields to use as the colum headers.
      // additional documentation can be found here
      // https://api.drupal.org/api/drupal/includes%21theme.inc/function/theme_table/7
	  $headers = array('Name', 'Genome', 'Transcript', 'CDS', 'Protein');      
      
      // the $rows array contains an array of rows where each row is an array
      // of values for each column of the table in that row.  Additional documentation
      // can be found here:
      // https://api.drupal.org/api/drupal/includes%21theme.inc/function/theme_table/7
      $rows = array();
  
      foreach ($objects as $object){	  	    
	    // Below tripal function is used to get seqlen from featureloc relationship
	    $obj_feature = chado_expand_var($object, 'table', 'featureloc');  		
		
        // Genomic sequence - nothing but co-ordinates sequence 		 
	    $genomic_link = "<a href='#' onclick=\"popup_message_display_popup(".$object->record->object_id->feature_id.", '".$object->record->object_id->type_id->name."', 680, 300);\">Genomic Fasta</a>";  
		  
	    // cDNA sequence
        $type_id_cdna = CDNA_TYPE_ID; //'585';
        $cdna_args = array(':feature_id' => $object->record->subject_id->feature_id);
        $cdna_sql = "select * from chado.feature where uniquename=(select uniquename from chado.feature where feature_id=:feature_id) and type_id=".$type_id_cdna;
        $cdna_sequence = chado_query($cdna_sql, $cdna_args)->fetchObject();
        $cDNA_link = "-";

        if(!empty($cdna_sequence->residues))
          $cDNA_link = "<a href='#' onclick=\"popup_message_display_popup(".$object->record->subject_id->feature_id.", '".$object->record->subject_id->type_id->name."', 680, 300);\">cDNA Fasta</a>";

	    // CDS sequence			
	    $type_id_cds = CDS_TYPE_ID; //'325';
	    $cds_args = array(':feature_id' => $object->record->subject_id->feature_id, ':type_id_cds' => $type_id_cds); 
        $cds_sql = "select f.feature_id, c.name, f.residues from chado.feature f, chado.cvterm  c where f.uniquename=(select uniquename from chado.feature where feature_id=:feature_id) and f.type_id=c.cvterm_id and f.type_id=:type_id_cds";	
	    $cds_sequence = chado_query($cds_sql, $cds_args)->fetchObject();		

        $cds_link = '-';
        if(!empty($cds_sequence->residues))
          $cds_link = "<a href='#' onclick=\"popup_message_display_popup(".$cds_sequence->subject_id.", '".$cds_sequence->name."', 680, 300);\">CDS Fasta</a>";
		
        // Polypeptide sequence
	    $fid = $object->record->subject_id->feature_id;
	    $pep_link = '-';		
	    $type_id_pep = PEP_TYPE_ID; //324;
	
        $query = db_query("select fr.subject_id, fr.object_id, fr.type_id, c.name as typename from  chado.feature_relationship fr, chado.feature f, chado.featureloc fc, chado.cvterm  c where f.feature_id=fr.subject_id and fc.feature_id=f.feature_id and f.type_id=c.cvterm_id and fr.object_id=:fid and c.cvterm_id=".$type_id_pep."", array(':fid' => $fid));    
        foreach($query as $result) {                  
	      $pep_link = "<a href='#' onclick=\"popup_message_display_popup(".$result->subject_id.", '".$result->typename."', 680, 300);\">Peptide Fasta</a>"; 		
        } 		
		  
        $rows[] = array(
          array('data' =>$object->record->subject_id->uniquename, 'width' => '30%'),          
          $genomic_link,
		  $cDNA_link,  
          $cds_link,
          $pep_link	  
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
           'id' => 'tripal_feature-table-sequence-object',
           'class' => 'tripal-data-table'
         ),
         'sticky' => FALSE,
         'caption' => '',
         'colgroups' => array(),
         'empty' => '',
       );
       
       // once we have our table array structure defined, we call Drupal's theme_table()
       // function to generate the table.
       print theme_table($table); ?>
       </p>
       <br><?php
     }
  }
  
  // second add in the object relationships.  
  foreach ($object_rels as $rel_type => $rels){
    foreach ($rels as $subject_type => $subjects){     
      // the $headers array is an array of fields to use as the colum headers.
      // additional documentation can be found here
      // https://api.drupal.org/api/drupal/includes%21theme.inc/function/theme_table/7
      $headers = array('Name', 'Genome', 'Transcript', 'CDS', 'Protein');
      
      // the $rows array contains an array of rows where each row is an array
      // of values for each column of the table in that row.  Additional documentation
      // can be found here:
      // https://api.drupal.org/api/drupal/includes%21theme.inc/function/theme_table/7
      $rows = array();

      foreach ($subjects as $subject){		  
	    // Below tripal function is used to get seqlen from featureloc relationship
	    $obj_feature = chado_expand_var($subject, 'table', 'featureloc');
        //echo "<pre>"; print_r($subject);echo "</pre>";		
        // Genomic sequence - nothing but co-ordinates sequence 		 		  
  	    $genomic_link = "<a href='#' onclick=\"popup_message_display_popup(".$subject->record->object_id->feature_id.", '".$subject->record->object_id->type_id->name."', 680, 300, '0', '".$subject->record->object_id->featureloc->feature_id->strand."');\">Genomic Fasta</a>";  
 
	    // cDNA sequence
 	    $type_id_cdna = CDNA_TYPE_ID; //'585';	
 	    $cdna_args = array(':feature_id' => $subject->record->subject_id->feature_id);
        $cdna_sql = "select * from chado.feature where uniquename=(select uniquename from chado.feature where feature_id=:feature_id) and type_id=".$type_id_cdna;
        $cdna_sequence = chado_query($cdna_sql, $cdna_args)->fetchObject();
	    $cDNA_link = "-";
		
        if(!empty($cdna_sequence->residues))	
	      $cDNA_link = "<a href='#' onclick=\"popup_message_display_popup(".$subject->record->subject_id->feature_id.", '".$subject->record->subject_id->type_id->name."', 680, 300, '0', '".$subject->record->subject_id->featureloc->feature_id->strand."');\">cDNA Fasta</a>"; 
       
	      // CDS sequence		
	      $type_id_cds = CDS_TYPE_ID; //'325';
	      $cds_args = array(':feature_id' => $subject->record->subject_id->feature_id, ':type_id_cds' => $type_id_cds); 
          $cds_sql = "select distinct fr.subject_id, c.name, f.feature_id, fc.strand, f.residues from  chado.feature_relationship fr, chado.feature f, chado.featureloc fc, chado.cvterm  c where f.feature_id=fr.subject_id and fc.feature_id=f.feature_id and f.type_id=c.cvterm_id and fr.object_id=:feature_id and cvterm_id=:type_id_cds";
		  
          $cds_sequence = chado_query($cds_sql, $cds_args)->fetchObject();		
		
	      $cds_link = '-';
	      if(!empty($cds_sequence->residues))
	        $cds_link = "<a href='#' onclick=\"popup_message_display_popup(".$cds_sequence->feature_id.", '".$cds_sequence->name."', 680, 300, '1', '".$cds_sequence->strand."');\">CDS Fasta</a>";
	   
	      // Polypeptide sequence
	      $fid = $subject->record->subject_id->feature_id;
	      $pep_link = '-';
		
	      $type_id_pep = PEP_TYPE_ID; //'324';
		
          $query = db_query("select fr.subject_id, fr.object_id, fr.type_id, c.name as typename, fc.strand from  chado.feature_relationship fr, chado.feature f, chado.featureloc fc, chado.cvterm  c where f.feature_id=fr.subject_id and fc.feature_id=f.feature_id and f.type_id=c.cvterm_id and fr.object_id=:fid and c.cvterm_id=".$type_id_pep." order by c.name", array(':fid' => $fid));
		
          foreach($query as $result) {                  
	        $pep_link = "<a href='#' onclick=\"popup_message_display_popup(".$result->subject_id.", '".$result->typename."', 680, 300, '0', '".$result->strand."');\">Peptide Fasta</a>"; 		
          }			
		
          $rows[] = array(
            array('data' =>$subject->record->subject_id->uniquename, 'width' => '30%'),     $genomic_link,
		    $cDNA_link,  
            $cds_link,
            $pep_link	  
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
           'id' => 'tripal_feature-table-sequence-subject',
           'class' => 'tripal-data-table'
         ),
         'sticky' => FALSE,
         'caption' => '',
         'colgroups' => array(),
         'empty' => '',
       );
       
       // once we have our table array structure defined, we call Drupal's theme_table()
       // function to generate the table.
       print theme_table($table); ?>
       </p>
       <br><?php
     }
  }
}

} // if condition close - checkong the if condition for gene and pseudogene

