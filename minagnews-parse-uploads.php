<?php
// 2011-01-25 in year_has_documents, missing braces after if statement caused errors
// 2011-10-11 Use scandir instead of readdir in function parse (thanks Ethan Piliavin)

class MinAgNewsParseUploads {

  // These are set as options in a WP admin panel
  var $uploadsPath;  // path to the directory containing the documents
  var $document_types = array(); // the allowed document types
  
  // holds the array of documents parse from the upload direcgtory
  var $documents = array();
  
  // computed values
  var $file_types = array();
  
  var $docs_exist = array(); // array of booleans by docType
  var $any_documents_exist; // boolean, if there are documents of any type
  
  var $start_year;
  var $end_year;
  
  var $month_names = array(
       '01' =>'January', '02' =>'February', '03'=>'March',
       '04' =>'April',   '05' =>'May',      '06'=>'June',
       '07' =>'July',    '08' =>'August',   '09'=>'September',
       '10' =>'October', '11' =>'November', '12'=>'December'
      );
  
  var $reverse_month_names = array(
      '12'=>'December',  '11' =>'November', '10' =>'October',           
      '09'=>'September', '08' =>'August',   '07' =>'July',              
      '06'=>'June',      '05' =>'May',      '04' =>'April',             
      '03'=>'March',     '02' =>'February', '01' =>'January'
      );  
  
  function __construct($uploadsPath = null, $document_types = array() ) {
    
      if ($uploadsPath) {
        $this->uploadsPath = $uploadsPath;
      }
      
      $this->document_types = $document_types;
      
      // Compute the file_types array
      foreach ($this->document_types as $docType=>$docdef) {
        $this->file_types[$docdef['file_prefix']] = $docType;
      }
      
      return;
  }
  
  function is_valid_fileType($f) {
    foreach ($this->file_types as $fileType=>$docType) {
      if ($f == $fileType) {
        return true;
      }
    }
    return false;
    
  }
  
  function is_valid_document_type($d) {
    if (array_key_exists($d, $this->document_types)) {
      return true;
    } else {
      return false;
    }
  }
  
  // analyze contents of uploads directory
  function parse() {
        
    // 2011-10-11 Thanks to Ethan Piliavin for suggestion to use scandir instead of readdir.
    // Scandir automatically sorts alphabetically, which is needed here, whereas readdir was 
    // sorting by file date, which was necessarily the correct order for us if the files were not
    // uploaded in chronological order
    
		if ( $allfiles = @scandir($this->uploadsPath) ) { 
		  foreach ($allfiles as $file) {
				// add filename to array if it matches the naming pattern
				$fileNameOptions = implode('|',array_keys($this->file_types));
				if (preg_match("/^({$fileNameOptions})_[0-9]{4}\-[0-9]{2}\-[0-9]{2}.pdf$/",$file)) {
				  $s = strpos($file,'_');
				  $fileType = substr($file,0,$s);
				  $docType = $this->file_types[$fileType];
				  $year = substr($file,$s+1,4);
				  $month = substr($file,$s+6,2);
				  $day = substr($file,$s+9,2);
				  $this->documents[$docType][$year][$month][$day] = $file;
				}
		  } 
		} else {
		  echo 'Warning: documents folder could not be read. Check permissions.';
		  
		}

    // Compute if any of each type of document exist, and if any exist at all
    // docs_exist($docType) and any_docs_exist
    $this->any_documents_exist = false;
    foreach ($this->document_types as $docType=>$docdef) {
      if ($this->docs_exist[$docType] = !empty($this->documents[$docType])) {
        $this->any_documents_exist = true;
      }
    }

    // Find the earliest and latest start/end years
    if ($this->any_documents_exist) {
      $this->start_year = 999999;
      $this->end_year = 0;
      foreach ($this->docs_exist as $docType=>$exists) {
        if ($exists) {
          $this->start_year = min( min(array_keys($this->documents[$docType])), $this->start_year);
          $this->end_year   = max( max(array_keys($this->documents[$docType])), $this->end_year);
        }
      }
    }
    
    return;

  } // end function parse

  function documents_exist($docType = 'all') {
    
    if ($docType == 'all') {
      return ($this->any_documents_exist);
    } else {
      if (array_key_exists($docType, $this->docs_exist)) {
        return ($this->docs_exist[$docType]);
      } else {
        return false;
      }
    }

  } // end function documents_exist
  
  function year_has_documents($year, $show) {
    $myarray = $this->documents;
    $has_documents = false;
    foreach ($this->docs_exist as $docType=>$exists) {
      if ( $exists && ($show=='all' || in_array($docType, $show)) ) {
        if (array_key_exists($year,$myarray[$docType])) {
          $has_documents = true;
          break;
        }
      }
    }
    
    return $has_documents;
            
  } // end function year_has_documents
  
  
  function month_has_documents($docType, $month, $year) {
    $myarray = $this->documents[$docType];
    $has_documents = false;
    if (!empty($myarray)) {
      if (array_key_exists($year,$myarray)) {
        if (array_key_exists($month,$myarray[$year])) {
          $has_documents = true;
        }
      }
    }

    return $has_documents;
  
  } // end function month_has_documents
  
  
  // Create a table. The passed in string is parsed and used for the td content
  // available parsings:
  // filename
  function createTable($td_content, $show = 'all', $year = 'all', $attributes = array(), $months_order='ASC', $omit_empty=false) {

    $table = '';
    
    $valid_attributes = array(
      'id'           => '',
      'class'        => 'minagnews_tbl',
      'width'        => '100%',
      'cellpadding'  => '4',
      'cellspacing'  => '0',
      'attr'         => ''

    );
    

    if (!$this->any_documents_exist) {
      $table = '<p>No documents exist.</p>';
      return $table;
    }

    // Parse the html attributed for the table and create strings as needed
    $attribute_string = '';
    foreach ($valid_attributes as $vattr=>$default) {
      if (array_key_exists($vattr, $attributes)) {
        $$vattr = $attributes[$vattr];
      } else {
        $$vattr = $default;
      }
      if ($vattr != 'attr' && $$vattr != '') {
        $$vattr = $vattr . "='" . $$vattr . "' ";
      }
      $attribute_string .= $$vattr;
    }

    $docs_to_display = array();    
    if ($show == 'all') {
    	foreach ($this->docs_exist as $docType=>$exists) {
    		if ($exists) {
    			$docs_to_display[] = $docType;
    		}
    	}    	
    } else {
     	$show = array_map('trim', explode(',', $show) );
     	foreach ($show as $s) {
     		// if the document type exists and has documents...
     		if (array_key_exists($s, $this->docs_exist) && $this->docs_exist[$s]) {
     			// add to display array
     			$docs_to_display[] = $s;
     		}	
     	}
    }
    $docs_to_display = array_unique($docs_to_display);
    
    $years_to_display = array();
    $all_years = range($this->start_year, $this->end_year);
    $current_year = date('Y');
    $previous_year = $current_year - 1;
    if ($year == 'all') {
    	$years_to_display = $all_years;
    } else { 
			$year = array_map('trim', explode(',', $year) );
			foreach ($year as $y) {
				// each element of the array could be a year or a range of years
				if (substr_count($y,'-') == 1) {
					list($range_start, $range_end) = explode('-', $y);
					$range_start = (in_array($range_start, $all_years)) ? $range_start : $this->start_year;
					$range_end = (in_array($range_end, $all_years)) ? $range_end : $this->end_year;
					if ($range_end >= $range_start) {
						for ($i=$range_start; $i<=$range_end; $i++) {
							$years_to_display[] = $i;
						}
					}
				} elseif ('current' == strtolower($y)) {
					if (in_array($current_year, $all_years)) {
						$years_to_display[] = $current_year;
					}
				} elseif ('previous' == strtolower($y)) {
					if (in_array($previous_year, $all_years)) {
						$years_to_display[] = $previous_year;
					}						
				} elseif (in_array($y, $all_years)) {
					$years_to_display[] = $y;
				}
			}    	
    }
    // clean up in case of duplicates, and order
    $years_to_display = array_unique($years_to_display);
    rsort($years_to_display); // put years in descending order
    
		$documents_listed_count = 0;
    

    ob_start();
    ?>  

    <table <?php echo $attribute_string;?>>
        <?php
        foreach ($years_to_display as $year) {
          if ($this->year_has_documents($year, $docs_to_display)) {
            ?>
            <thead>
              <tr>
                <th><?php echo $year;?></th>
                <?php
                foreach ($docs_to_display as $docType) {
									?>
                  <th><?php echo $this->document_types[$docType]['title'];?></th>
                  <?php
                }
                ?>
              </tr>
            </thead>
            <tbody>

            <?php
            $months_in_order = ($months_order == 'ASC') ? $this->month_names : $this->reverse_month_names;
            foreach ($months_in_order as $month => $month_name) {
              $month_has = array();
              $has_any = false;
              foreach ($docs_to_display as $docType) {
                if ($month_has[$docType] = $this->month_has_documents($docType, $month, $year)) {
                  $has_any = true;
                }
              }
              $past = strtotime($year.'-'.$month.'-01') <= strtotime('today');
              $show_this_month_row = ($omit_empty) ? $has_any : ($past || $has_any);
              if ($show_this_month_row) {
                ?>
                <tr>
                  <td class="row-title"><?php echo $month_name; ?></td>
                  <?php
                  foreach ($docs_to_display as $docType) {
                    ?>
                    <td><?php
                      if ($month_has[$docType]) {
                      	$documents_listed_count++;
                        foreach ($this->documents[$docType][$year][$month] as $day=>$filename) {
                          echo $this->interpret($td_content, $filename, $docType, $year, $month, $day);
                        }   
                      } else {
                        echo '&nbsp;';
                      }
                    ?>
                    </td>
                    <?php
                  } // for each docType
                  ?>
                </tr>
                <?php
              } // end if there are documents for this month
            } // end month
          } // if there are documents for this year
        } // end year
    
        ?>
      </tbody>
    </table>    
    <?php

    $table = ob_get_contents();
    ob_end_clean();
      
    if ($documents_listed_count == 0) {
      $table = "<p>No documents found.</p>" ;
    }
    
    return $table;
  }
  
  
  function interpret ($pattern, $filename, $docType, $year, $month, $day) {
    // Interprets the possible codes in the pattern
    // We interpret 'display' first as it may contain the other strings
    

    $pattern = str_ireplace('[[[display]]]', $this->document_types[$docType]['display'], $pattern);
    
    $pattern =  str_ireplace('[[[filename]]]', $filename, $pattern);
    
    $pattern =  str_ireplace('[[[docType]]]',  $this->document_types[$docType]['title'], $pattern);
    
    $pattern =  str_ireplace('[[[date]]]', 
                date($this->document_types[$docType]['date_format'],
                     strtotime($year.'-'.$month.'-'.$day)),
                $pattern);
    
    $pattern =  str_ireplace('[[[year]]]', $year, $pattern);
    
    $pattern =  str_ireplace('[[[month]]]', $month, $pattern);
    
    $pattern =  str_ireplace('[[[day]]]', $day, $pattern);
    
    return $pattern;
    
  }
  
  // utility function
  function createFilename($docType, $date) {
    $filename = '';
    if ($this->is_valid_document_type($docType)) {
      $file_prefix = $this->document_types[$docType]['file_prefix'];
      $filename = $file_prefix . '_' . date('Y-m-d', strtotime($date)) . '.pdf';
    }
    return $filename;
  }
  
  // find latest document date for a given doctype
  function latestDocumentDate($docType) {
    $date = false;
    if (array_key_exists($docType, $this->docs_exist) && $this->docs_exist[$docType]) {
      $latest_year  = max(array_keys($this->documents[$docType]));
      $latest_month = max(array_keys($this->documents[$docType][$latest_year]));
      $latest_day   = max(array_keys($this->documents[$docType][$latest_year][$latest_month]));
      $date = $latest_year . '-' . $latest_month . '-' . $latest_day;
    }
    return $date;

  }

  
} // end class MinAgNewsParseUploads