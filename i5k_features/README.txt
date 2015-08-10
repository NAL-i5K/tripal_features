This module provides an alternative to the default Tripal gene pages
Notable features:
  1. Additional information about gene features in Overview tab
  2. New layout for Sequence tab
     a. Each sequence opens up in a popup window.
     b. The sequence can be copied to the clipboard.
     c. Each transcript has 4 types of sequences
        i) Genomic Fasta: Sequence residues is co-ordinates from featureloc table.
	   ii) cDNA Fasta: Residues from feature table.
      iii) CDS Fasta: Residues from feature table.
	   iv) Peptide Fasta: Residues is the co-ordinates from featureloc table .
	
  3. New tab "Transcripts" was added to display details of mRNA.     
  Each transcript has 3 collapsible sections
     a. Details: This section contains information about the transcript.
     b. Annotated Terms: Annotated terms related to the transcript
     c. Feature Details: This section contains a table of data with 6 columns: 
	 i) Type, Reference Sequence Id, Start, End, Strand and View sequence.    
	ii)Sequences are calculated in one of two ways depending on the feature type:
              - mRNA(unspliced) sequence is the co-ordinates (residues) from the featureloc table
              - mRNA(spliced) sequence is from the feature table.
              - CDS (merged) sequence is the co-ordinates(residues) from the featureloc table.
              - CDS sequence is from the feature table.
              - Other sequences are from the feature table.
        NOTE: The sequence is reverse complemented if the strand is -1.
		
		

Installation instructions
-------------------------
1. Download the i5k_features module into the modules directory (usually
   "sites/all/modules").   

2. Go to "Administer" -> "Modules" and enable the module.
    or 
   drush en i5k_features
   
IMORTANT NOTE: If there are templates "tripal_feature_base.tpl.php" and "tripal_feature_sequence.tpl.php" anywhere in theme. Please backup and remove them so that this module templates "base" and "sequence" will override.

Clear the cache