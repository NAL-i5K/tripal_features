<?php
$feature = $node->feature;

//VIJAYA gene_var variable is used to differentiate the gene and mRNA pages
$gene_var = array('gene', 'pseudogene');

if(in_array($feature->type_id->name, $gene_var)) {
$all_relationships = $feature->all_relationships;

$object_rels = $all_relationships['object'];
$subject_rels = $all_relationships['subject'];

if (count($object_rels) > 0 or count($subject_rels) > 0) { ?>
  <div class="tripal_feature-data-block-desc tripal-data-block-desc"></div> <?php  
  if(count($subject_rels) > 0) {
  // first add in the subject relationships.  
  foreach ($subject_rels as $rel_type => $rels){
    foreach ($rels as $obj_type => $objects){ ?>

      <p>This <?php print $feature->type_id->name;?> is <?php print $rel_type ?> the following <b><?php print $obj_type ?></b> feature(s): <?php
       
      // the $headers array is an array of fields to use as the column headers.
      $headers = array('Name', 'Type', 'Transcript Length', 'Protein Length', 'Detailed View');
      
      // the $rows array contains an array of rows where each row is an array
      // of values for each column of the table in that row.
      $rows = array();
  
      foreach ($objects as $object){	  
		// The $headers array is an array of fields to use as the colum headers.
      $headers = array();  $rows = array(); $table = ''; 
	  $feature = $object->record;	  
      // Name row
      $rows[] = array(
        array(
          'data' => 'Name',
          'header' => TRUE,
          'width' => '20%',
        ),
        $feature->subject_id->name
      );
      // Unique Name row
      $rows[] = array(
        array(
          'data' => 'ID',
          'header' => TRUE
        ),
        $feature->subject_id->uniquename
      );

      // Type row
      $rows[] = array(
        array(
          'data' => 'Type',
          'header' => TRUE
        ),
        $feature->subject_id->type_id->name
      );

	  // Dbxref, expand the feature object to include the records from the feature_dbxref table
      $options = array('return_array' => 1);
      $dx_feature = chado_expand_var($feature, 'table', 'feature_dbxref', $options);  
      $feature_dbxrefs = $dx_feature->subject_id->feature_dbxref;  
      
      $references = '';	
      if (count($feature_dbxrefs) > 0 ) {    
        foreach ($feature_dbxrefs as $feature_dbxref) {    
          if(!empty($feature_dbxref->dbxref_id->db_id->name) && ($feature_dbxref->dbxref_id->db_id->name != 'GFF_source')){
          // check to see if the reference 'GFF_source' is there.  This reference is
          // used to if the Chado Perl GFF loader was use
          $references .= $feature_dbxref->dbxref_id->db_id->name.":".$feature_dbxref->dbxref_id->accession."<Br>";
          }
        }
      }
  
      // Dbxref row
      $rows[] = array(
        array(
          'data' => 'Dbxref',
          'header' => TRUE
        ),
        $references
      );
	  
	  $feature = chado_expand_var($feature, 'table', 'analysisfeature', $options);
      $analyses = $feature->subject_id->analysisfeature;
	  
	  $src_values = array('feature_id' => $feature->subject_id->featureloc->feature_id->srcfeature_id->feature_id);
      $srcfeature = chado_generate_var('analysisfeature', $src_values, $options);
	  
	  // Analyses - if and else statements cause the anaylses having different array formats.
      if(count($analyses) == 2) {  
        $analysis_name = '';
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
  
      //User comment - //type_id = 85 - note
	  $featureprop_id = $feature->subject_id->feature_id;  
      $select = array('feature_id' => $featureprop_id, 'type_id' => 85);
      $columns = array('value', 'feature_id');
      $featureprop = chado_select_record('featureprop', $columns, $select);  
	  $featureprop = array_reverse($featureprop);
  
      $user_comment = ''; $user_i = 0;
      foreach($featureprop as $prop_obj) {
        if(!empty($prop_obj->value)) {
          ($user_i == 0)? $user_comment .= 'Note: ':'';
		  $user_comment .= $prop_obj->value."; ";
		  $user_i++;
		}	  
      }
      $user_comment = rtrim($user_comment, '; ');
      // User Submitted info row
      $rows[] = array(
        array(
          'data' => 'Annotator Comments',
          'header' => TRUE
        ),
        $user_comment
      );
      $table = array(
        'header' => $headers,
        'rows' => $rows,
		'attributes' => array(
		  'id' => 'tripal_feature-detail-transcript',
          'class' => 'tripal-data-table'
		),
		'sticky' => FALSE,
		'caption' => '',
		'colgroups' => array(),
		'empty' => '',
      );
	  
	   //Details 
	   $output .= '<h2 class="accordionClose">'.$subject->record->subject_id->uniquename.'<span></span> </h2>';		 
	   $output .= '<div>';
	   $output .= '<h3 class="accordionClose">Details<span></span></h3>
		           <div>';
	   $output .= theme_table($table). "</div>";	
	   
	   //Annotated Terms
	   $output .= '<h3 class="accordionClose">Annotated Terms<span></span></h3>';	   
	   $feature_terms_text = theme('tripal_feature_terms', array('node' => $object->record));	      
	   $output .= '<div>'.$feature_terms_text.'</div>';
	   
	   //Feature details
	   $feature_headers = array('Type', 'Reference Sequence ID', 'start', 'End', 'Strand', 'View sequence');   
	   
	   
       $feature_rows = array();	 
       $fid = $object->record->subject_id->feature_id;
	   $query = db_query("select fr.subject_id, fr.object_id, fr.type_id, c.name as typename, f.residues, f.feature_id, f.seqlen, fc.srcfeature_id, fc.feature_id as fcfeature_id, fc.fmin, fc.fmax, fc.strand from  chado.feature_relationship fr, chado.feature f, chado.featureloc fc, chado.cvterm  c where f.feature_id=fr.subject_id and fc.feature_id=f.feature_id and f.type_id=c.cvterm_id and fr.object_id=:fid order by c.name, fc.fmin", array(':fid' => $fid)); 
   
       $data = array();  $i = 0;
       foreach($query as $result) {
         if($result->cvterm_id == CDS_TYPE_ID) {  
           // [CDS] 1 - unspliced means sequence from scaffold co-ordinates
	       $unspliced = 1;	   
		   $fr_data = push_cds_data($result, $unspliced);   	
	       array_unshift($data, $fr_data);	   
		   
		   $unspliced = 0;	   
		   $fr_data = push_cds_data($result, $unspliced);   	
	       array_unshift($data, $fr_data);	   
		   $i++;
		 } else {		 
	       $fmin = $result->fmin + 1;
	       $fmax = $result->fmax;	
	
	       if($result->strand == 1)
	         $strand = '[+]';
           else if($result->strand == -1)
	         $strand = '[-]';
	
	       // View Fasta 
	       if(!empty($result->residues))	
	         $view_sequence = "<a href='#' onclick=\"popup_message_display_popup(".$result->subject_id.", '".$result->typename."', 680, 300, '0', '".$result->strand."');\">Fasta</a>";
	       else 
             $view_sequence = "-";	
		   
		   $seq_query = db_query("select f.name from chado.featureloc fc, chado.feature f where fc.srcfeature_id=f.feature_id and fc.srcfeature_id=:srcfid", array(':srcfid' => $result->srcfeature_id));
           foreach($seq_query as $seq_result) {		 
		     $seq_name = $seq_result->name;
           } 
		 
		   $data[$i][0] = array('data' =>$result->typename, 'width' => '10%');  
		   $data[$i][1] = array('data' =>$seq_name, 'width' => '10%');  
		   $data[$i][2] = array('data' =>$fmin, 'width' => '10%');  
		   $data[$i][3] = array('data' =>$fmax, 'width' => '10%');  
		   $data[$i][4] = array('data' =>$strand, 'width' => '10%');  
		   $data[$i][5] = array('data' =>$view_sequence, 'width' => '10%'); 
         }  		   
		 $i++;
       }   
	
	   // [mRNA] 1 - unspliced means sequence from scaffold co-ordinates
	   $unspliced = 1;	   
	   $fr_data = push_data($object->record->subject_id, $unspliced);   	
	   array_unshift($data, $fr_data);
	   
	   // Here mRNA 0 - spliced sequence means cdna residues
	   $unspliced = 0;
	   $fr_data = push_data($object->record->subject_id, $unspliced);   	
	   array_unshift($data, $fr_data); 
	   
	   //pushing gene data to array first position using array_unshift	  
	   $unspliced = 0;	   
	   $fr_data = push_data($object->record->object_id, $unspliced); 
	   array_unshift($data, $fr_data); 

	   $feature_table = array(
		 'header' => $feature_headers,
		 'rows' => $data,
		 'attributes' => array(
		   'id' => 'tripal_feature-fasta-detail-transcript',
		   'class' => 'tripal-data-table1'
		  ),
         'sticky' => FALSE,
         'caption' => '',
         'colgroups' => array(),
         'empty' => '',
       );
	    
	   $output .= '<h3 class="accordionClose">Feature Details<span></span></h3>';
	   $output .= '<div>'.theme_table($feature_table).'</div>';
	   $output .= '</div>';	
	   $i++;
     } 
	 $output .= '</div>';
	 print $output;   
     
     // the $table array contains the headers and rows array as well as other
     // options for controlling the display of the table.  Additional
     // documentation can be found here:
     // https://api.drupal.org/api/drupal/includes%21theme.inc/function/theme_table/7
     $table = array(
       'header' => $headers,
       'rows' => $rows,
       'attributes' => array(
         'id' => 'tripal_feature-table-relationship-object',
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
 } //end if
  
  // second add in the object relationships.  
  foreach ($object_rels as $rel_type => $rels){
    foreach ($rels as $subject_type => $subjects){?>
      <p>The following features are <?php print $rel_type ?> this <?php print $feature->type_id->name;?>: <?php 
      
      // the $rows array contains an array of rows where each row is an array
      // of values for each column of the table in that row. 	        
	  $output = '<div id="multiAccordion">';	  
      foreach ($subjects as $subject){	 
      // The $headers array is an array of fields to use as the colum headers.
      $headers = array();  $rows = array(); $table = ''; 
	  $feature = $subject->record;	  
      // Name row
      $rows[] = array(
        array(
          'data' => 'Name',
          'header' => TRUE,
          'width' => '20%',
        ),
        $feature->subject_id->name
      );
      // Unique Name row
      $rows[] = array(
        array(
          'data' => 'ID',
          'header' => TRUE
        ),
        $feature->subject_id->uniquename
      );

      // Type row
      $rows[] = array(
        array(
          'data' => 'Type',
          'header' => TRUE
        ),
        $feature->subject_id->type_id->name
      );

	  // Dbxref, expand the feature object to include the records from the feature_dbxref table
      $options = array('return_array' => 1);
      $dx_feature = chado_expand_var($feature, 'table', 'feature_dbxref', $options);  
      $feature_dbxrefs = $dx_feature->subject_id->feature_dbxref;  
      $references = '';	
      if (count($feature_dbxrefs) > 0 ) {    
        foreach ($feature_dbxrefs as $feature_dbxref) {   
          // skip the GFF_source entry as this is just needed for the GBrowse chado adapter 
          if($feature_dbxref->dbxref_id->db_id->name == 'GFF_source') {
            continue; 
          }          
 
          // check to see if the reference 'GFF_source' is there.  This reference is
          // used to if the Chado Perl GFF loader was use
          if(!empty($feature_dbxref->dbxref_id->db_id->name) ) { 
            $dbname_accession = $feature_dbxref->dbxref_id->db_id->name.":".$feature_dbxref->dbxref_id->accession;
            if($feature_dbxref->dbxref_id->db_id->urlprefix) {
              $references .= l($dbname_accession, $feature_dbxref->dbxref_id->db_id->urlprefix.  $feature_dbxref->dbxref_id->accession, array('attributes' => array('target' => '_blank')) )."<br>";
            } else 
               $references .=  $dbname_accession."<Br>";

          } // if closes 

        } // foreach closes
      } // if cound closes
  
      // Dbxref row
	  $references = !empty($references)?$references: 'None';
      $rows[] = array(
        array(
          'data' => 'Dbxref',
          'header' => TRUE
        ),
        $references
      );
	  
     $feature = chado_expand_var($feature, 'table', 'analysisfeature', $options);
     $analyses = $feature->subject_id->analysisfeature;
	  
     $src_values = array('feature_id' => $feature->subject_id->featureloc->feature_id->srcfeature_id->feature_id);
     $srcfeature = chado_generate_var('analysisfeature', $src_values, $options);
//echo "<prE>"; print_r($analyses);	   echo "</prE>";
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
  
      //User comment - //type_id = 85 - note
	  $featureprop_id = $feature->subject_id->feature_id;  
      $select = array('feature_id' => $featureprop_id, 'type_id' => 85);
      $columns = array('value', 'feature_id');
      $featureprop = chado_select_record('featureprop', $columns, $select);  
	  $featureprop = array_reverse($featureprop);
  
      $user_comment = ''; $user_i = 0;
      foreach($featureprop as $prop_obj) {
        if(!empty($prop_obj->value)) {
          ($user_i == 0)? $user_comment .= 'Note: ':'';
		  $user_comment .= $prop_obj->value."; ";
		  $user_i++;
		}	  
      }
      $user_comment = rtrim($user_comment, '; ');
      // User Submitted info row
      $rows[] = array(
        array(
          'data' => 'Annotator Comments',
          'header' => TRUE
        ),
        $user_comment
      );
      $table = array(
        'header' => $headers,
        'rows' => $rows,
		'attributes' => array(
		  'id' => 'tripal_feature-detail-transcript',
          'class' => 'tripal-data-table'
		),
		'sticky' => FALSE,
		'caption' => '',
		'colgroups' => array(),
		'empty' => '',
      );
	  
	   //Details 
	   $output .= '<h2 class="accordionClose">'.$subject->record->subject_id->uniquename.'<span></span> </h2>';		 
	   $output .= '<div>';
	   $output .= '<h3 class="accordionClose">Details<span></span></h3>
		           <div>';
	   $output .= theme_table($table). "</div>";	
	   
	   
	   //Annotated Terms
	   $output .= '<h3 class="accordionClose">Annotated Terms<span></span></h3>';	   
	   $feature_terms_text = theme('tripal_feature_terms', array('node' => $subject->record));	      
	   $output .= '<div>'.$feature_terms_text.'</div>';
	   
	   //Feature details
	   $feature_headers = array('Type', 'Reference Sequence ID', 'start', 'End', 'Strand', 'View sequence');
	   
	   //echo "<pre>"; print_r($subject->record->subject_id); echo "</pre>";	   	
	   
       $feature_rows = array();	 
       $fid = $subject->record->subject_id->feature_id;
	   $query = db_query("select fr.subject_id, fr.object_id, fr.type_id, c.cvterm_id, c.name as typename, f.residues, f.feature_id, f.seqlen, fc.srcfeature_id, fc.feature_id as fcfeature_id, fc.fmin, fc.fmax, fc.strand from  chado.feature_relationship fr, chado.feature f, chado.featureloc fc, chado.cvterm  c where f.feature_id=fr.subject_id and fc.feature_id=f.feature_id and f.type_id=c.cvterm_id and fr.object_id=:fid order by c.name, fc.fmin", array(':fid' => $fid)); 
   
       $data = array();  $i = 0; $cds_i = 0;
       foreach($query as $result) {  
	  //echo "<pre>result "; print_r($result);echo "</prE>";
	  if($result->cvterm_id == CDS_TYPE_ID) {		 
	     // [CDS] 1 - unspliced means sequence from scaffold co-ordinates
	     if($cds_i == 0) {
	        $unspliced = 1;	 //merged cds=0 from residues 
                // The coordinates for the CDS (merged) row should be the lowest and highest of // the individual CDS lines			 
		$cds_result = db_query("select c.cvterm_id, f.residues, fc.strand, c.name as typename, fc.srcfeature_id, f.feature_id,  min(fmin) as fmin, max(fmax) as fmax from  chado.feature f, chado.featureloc fc, chado.cvterm  c where fc.feature_id=f.feature_id and f.type_id=c.cvterm_id and fc.feature_id=:fid and cvterm_id=:cds_type group by c.cvterm_id, fc.strand, c.name, fc.srcfeature_id,f.feature_id,f.residues", array(':fid' => $result->feature_id, ':cds_type' => CDS_TYPE_ID))->fetchObject(); 
		//echo "<pre>"; print_r($cds_result); echo "</pre>";
		$fr_data = push_cds_data($cds_result, $unspliced);   	
	        array_push($data, $fr_data);	   
	     }
	     $unspliced = 0;	 // 1 - residues from co-ordinates  
	     $fr_data = push_cds_data($result, $unspliced);   	
	     array_push($data, $fr_data);	   
	     $i++; $cds_i++;
	 } else {
	     $fmin = $result->fmin + 1;
	     $fmax = $result->fmax;	
	
	     if($result->strand == 1)
	       $strand = '[+]';
             else if($result->strand == -1)
	       $strand = '[-]';
	
	     /*
		   Below if condition sequence are polypeptide, exon.
		         else condition sequence are three_prime_UTR, five_prime_UTR etc.,
                 in else condition adding 1 to the unspliced parameter to differentiate. 				 
  		   View Fasta - popup_message_display_popup(feature_id, type, width, height, unspliced, strand)
		 */
	     if(!empty($result->residues))	
	       $view_sequence = "<a href='#' onclick=\"popup_message_display_popup(".$result->subject_id.", '".$result->typename."', 680, 300, '0', '".$result->strand."');\">Fasta</a>";
	     else {
		   $unspliced = 1;
           $view_sequence = "<a href='#' onclick=\"popup_message_display_popup(".$result->subject_id.", '".$result->typename."', 680, 300, '".$unspliced."', '".$result->strand."');\">Fasta</a>";	
		 }
		 
		 $seq_query = db_query("select f.name from chado.featureloc fc, chado.feature f where fc.srcfeature_id=f.feature_id and fc.srcfeature_id=:srcfid", array(':srcfid' => $result->srcfeature_id));
         foreach($seq_query as $seq_result) {		 
		   $seq_name = $seq_result->name;
         } 
		 
		 $data[$i][0] = array('data' =>$result->typename, 'width' => '10%');  
		 $data[$i][1] = array('data' =>$seq_name, 'width' => '10%');  
		 $data[$i][2] = array('data' =>$fmin, 'width' => '10%');  
		 $data[$i][3] = array('data' =>$fmax, 'width' => '10%');  
		 $data[$i][4] = array('data' =>$strand, 'width' => '10%');  
		 $data[$i][5] = array('data' =>$view_sequence, 'width' => '10%'); 	  	
		 
		 }
		 $i++;
       }   
	
	   // [mRNA] 1 - unspliced means sequence from scaffold co-ordinates
	   $unspliced = 1;	   
	   $fr_data = push_data($subject->record->subject_id, $unspliced);   	
	   array_unshift($data, $fr_data);
	   
	   // Here mRNA 0 - spliced sequence means cdna residues
	   $unspliced = 0;
	   $fr_data = push_data($subject->record->subject_id,$unspliced);   	
	   array_unshift($data, $fr_data); 
	   
	   //pushing gene data to array first position using array_unshift	  
	   $unspliced = 0;	   
	   $fr_data = push_data($subject->record->object_id,$unspliced); 
	   array_unshift($data, $fr_data); 

	   $feature_table = array(
		 'header' => $feature_headers,
		 'rows' => $data,
		 'attributes' => array(
		   'id' => 'tripal_feature-fasta-detail-transcript',
		   'class' => 'tripal-data-table1'
		  ),
         'sticky' => FALSE,
         'caption' => '',
         'colgroups' => array(),
         'empty' => '',
       );
	    
	   $output .= '<h3 class="accordionClose">Feature Details<span></span></h3>';
	   $output .= '<div>'.theme_table($feature_table).'</div>';
	   $output .= '</div>';	
	   $i++;
     } 
	 $output .= '</div>';
	 print $output;     
     ?>
     </p>
     <br><?php
    }
  }
}

} // gene if condition close
