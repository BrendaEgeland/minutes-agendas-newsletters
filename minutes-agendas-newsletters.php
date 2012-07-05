<?php

/*
Plugin Name: Minutes, Agendas and Newsletters
Plugin URI: http://www.redletterdesign.net/wp/minutes_agendas_newsletters
Description: Easily upload monthly minutes and agendas that are in pdf format. Provides shortcodes for individual documents and creating an overall listing.
Version: 1.0.1
Author: Brenda Egeland
Author URI: http://www.redletterdesign.net/
*/

/*
Installation

1. Download and unzip the latest release zip file
2. Upload the entire minutes-agendas-newsletters directory to your `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
*/

/*
Using

1. Activate the plugin
2. Click on "Manage"
3. Click on "Upload Files"
*/

/*
Version History

0.1 2011-1-7 Initial development
0.2 2011-10-11 Add shortcode options, fix bugs
0.2.1 2011-10-11 Fixes to readme file.
1.0.0 2012-07-03 Added new options to shortcodes. Some general code cleanup.
1.0.1 2012-07-04 Fixed error in omit_empty=true case where only some document types were being displayed.

/*

/*
Copyright 2011-2  Brenda Egeland (email: brenda@redletterdesign.net)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


if ( !class_exists( 'MinutesAgendasNewsletters' ) ) {
	class MinutesAgendasNewsletters {
	  var $adminOptionsName = 'MinAgNewsAdminOptions';


    // Constructor
    function __construct() {
      
      // Setup
      add_action('plugins_loaded',      array( $this, 'init' ), -10 );      
	    add_action('admin_print_scripts', array( $this,'adminjavascript' ));
	    add_action('edit_form_advanced',  array( $this,'quicktags' ));
	    add_action('edit_page_form',      array( $this,'quicktags' ));	

	    // Actions
      // -- display admin settings page
      add_action('admin_menu', array( $this, 'adminPanel' ));
      // -- display uploads page
      add_action('admin_menu', array( $this, 'uploadPanel' ));
	
	    // Shortcodes
	    add_shortcode('minagnews-table', array( $this, 'table_shortcode' ));
	    add_shortcode('minagnews-link',  array( $this, 'link_shortcode'  ));  		   

    }


    // Initialize plugin options
    function init() {

      $this->getAdminOptions();
  		$this->addbuttons();

    } // end function init
    

    // Initialize admin settings panel
    function adminPanel() {
      $minagnews_admin_menu = add_options_page(
        'Minutes, Agendas, Newsletters Settings',
        'Minutes, Agendas, Newsletters',
        'manage_options',
        basename(__FILE__),
        array($this, 'displayAdminSettings')
      );
      add_action('admin_print_styles-' . $minagnews_admin_menu, array( $this, 'admin_css_custom_page') );
    }  

    	
    // Initialize uploads page
    function uploadPanel() {
      $minagnews_admin_menu = add_media_page(
        'Manage Minutes, Agendas, Newsletters',
        'Minutes, Agendas, Newsletters',
        'upload_files',
        basename(__FILE__),
        array($this, 'uploadMinutesAgendasNewsletters')
      );
      add_action('admin_print_styles-' . $minagnews_admin_menu, array( $this, 'admin_css_custom_page') );
    } // end function minAgNews_uploadPanel 	

    function admin_css_custom_page() {
      /** Register */
      wp_register_style('minagnews-plugin-page-css', plugins_url('css/minagnews-admin.css', __FILE__), array(), '1.0.0', 'all');
      /** Enqueue */
      wp_enqueue_style('minagnews-plugin-page-css');
    }

    
    // Returns an array of admin options. Uses defaults unless alternative
    // values are found in the database.
    function getAdminOptions() {
      
      // Set the default options
      $minagnewsDefaultOptions = array(
        'uploads_dir' => 'minutes-agendas-newsletters',
        'document_types' => array( 
            'agendas' => array('title' => 'Agendas', 
                               'file_prefix' => 'Agenda',
                               'display' => 'Agenda for the [[[date]]] Meeting', 
                               'date_format' => 'F j, Y'), 
            'minutes' => array('title' => 'Minutes', 
                               'file_prefix' => 'Minutes',
                               'display' => 'Minutes from the [[[date]]] Meeting', 
                               'date_format' => 'F j, Y'), 
            'newsletters' => array('title' => 'Newsletters', 
                               'file_prefix' => 'Newsletter',
                               'display' => 'Newsletter dated [[[date]]]', 
                               'date_format' => 'F j, Y'), 
            'foo' => array('title' => 'Foo',
                               'file_prefix' => 'Foo',
                               'display' => 'Foo dated [[[date]]]', 
                               'date_format' => 'F j, Y')
          )
      );
      
      // Get any stored options and add/overwrite them to the defaults
      $storedOptions = get_option($this->adminOptionsName);
      if (!empty($storedOptions)) {
        foreach ($storedOptions as $key => $option) {
          $minagnewsDefaultOptions[$key] = $option;
        }
      }
      
      // Store the now updated set of options
      update_option($this->adminOptionsName, $minagnewsDefaultOptions);
      
      // Return the options
      return $minagnewsDefaultOptions;
      
    } // end function getAdminOptions
    

    // Display admin settings page
    function displayAdminSettings() {
          
      $upload_dir_results = array();
      $doc_type_results = array();
    
      $minAgNewsOptions = $this->getAdminOptions();
      $uploads_dir = $minAgNewsOptions['uploads_dir'];
      $document_types = $minAgNewsOptions['document_types'];
      $file_prefixes = array();
      foreach ($document_types as $docType=>$docDef) { 
        $file_prefixes[] = strtolower($docDef['file_prefix']);
      }
      
      // $display mimicks the $_POST array to prefill values and track errors
      $display = array();
      $display['uploads_dir'] = array('value' => $uploads_dir, 'error' => false);
      foreach ($document_types as $docType=>$docDef) {
        $display['doc'][$docType]['slug']        = array('value' => $docType, 'error' => false);
        $display['doc'][$docType]['title']       = array('value' => $docDef['title'], 'error' => false);
        $display['doc'][$docType]['display']     = array('value' => $docDef['display'], 'error' => false);
        $display['doc'][$docType]['date_format'] = array('value' => $docDef['date_format'], 'error' => false);
        $display['doc'][$docType]['file_prefix'] = array('value' => $docDef['file_prefix'], 'error' => false);
      }
      $display['_ADD']['slug']        = array('value' => '', 'error' => false);
      $display['_ADD']['title']       = array('value' => '', 'error' => false);
      $display['_ADD']['display']     = array('value' => '', 'error' => false);
      $display['_ADD']['date_format'] = array('value' => '', 'error' => false);
      $display['_ADD']['file_prefix'] = array('value' => '', 'error' => false);

    
      // If the uploads_dir form has been submitted, check and update values         
      if (isset($_POST['update_uploadsDir'])) {
        
        // Upload directory
        if (isset($_POST['uploads_dir']) && $_POST['uploads_dir'] != $uploads_dir) {
          // the directory name must be a valid directory name
          if (preg_match('/^[a-zA-Z0-9_\-]+$/',$_POST['uploads_dir'])) {
            $minAgNewsOptions['uploads_dir'] = $_POST['uploads_dir'];
            if (update_option($this->adminOptionsName, $minAgNewsOptions)) {
              $uploads_dir = $_POST['uploads_dir'];
              $display['uploads_dir']['value'] = $uploads_dir;
              $upload_dir_results[] = "Uploads directory changed to $uploads_dir.";
              // if the directory does not exist, create it.
              $uploads = wp_upload_dir();
              $uploadsPath = $uploads['basedir'] . "/$uploads_dir";
              if (!is_dir($uploadsPath)) {
                if (mkdir($uploadsPath)) {
                  $upload_dir_results[] = 'The directory did not exist so it was created.';
                } else {
                  $upload_dir_results[] = 'The directory could not be created.';
                }
              } else {
                $file_glob = glob($uploadsPath . '/*.*');
                if ($file_glob != false) {
                  $filecount = count($file_glob);
                } else {
                  $filecount = 0;
                }
                $upload_dir_results[] = "The directory already exists and contains $filecount files. (This is okay if you were expecting the directory to already be there and contain files.)";
              }
            } else {
              $upload_dir_results[] = 'Uploads folder setting was not changed.';
            }
          } else {
            $display['uploads_dir']['value'] = htmlspecialchars($_POST['uploads_dir']);
            $display['uploads_dir']['error'] = true;
            $upload_dir_results[] = '"' . $display['uploads_dir']['value'] . '" is not a valid directory name.';
          }
        }
      } // end if uploads_dir submitted
      
      
      if (isset($_POST['update_docTypes'])) {
        
        // Move POST values to $display
        foreach ($_POST['doc'] as $docType=>$settings) {
          $display['doc'][$docType]['slug']['value']        = $settings['slug'];
          $display['doc'][$docType]['title']['value']       = $settings['title'];
          $display['doc'][$docType]['display'] ['value']    = $settings['display'];
          $display['doc'][$docType]['date_format']['value'] = $settings['date_format'];
          $display['doc'][$docType]['file_prefix']['value'] = $settings['file_prefix'];
        }
        $display['_ADD']['slug']['value']        = $_POST['_ADD']['slug'];
        $display['_ADD']['title']['value']       = $_POST['_ADD']['title'];
        $display['_ADD']['display']['value']     = $_POST['_ADD']['display'];
        $display['_ADD']['date_format']['value'] = $_POST['_ADD']['date_format'];
        $display['_ADD']['file_prefix']['value'] = $_POST['_ADD']['file_prefix'];
        

      
        // We'll check for any errors, and only update if no errors are found anywhere
        $errors = false;
        // check for errors in changed data
        foreach ($_POST['doc'] as $docType=>$settings) {
          // is slug okay?
          if (!preg_match('/^[a-z0-9_\-]+$/',$settings['slug'])) {
            $errors = true;
            $doc_type_results[] = "{$settings['slug']} is an invalid value. Must be lowercase alphanumeric; may include - or _.";
            $display['doc'][$docType]['slug']['error'] = true;
          }
          if ( ($settings['slug'] != $docType)
            && array_key_exists($settings['slug'], $document_types)) {
            $errors = true;
            $doc_type_results[] = "{$settings['slug']} already exists.";
            $display['doc'][$docType]['slug']['error'] = true;            
          }
          // is file prefix okay?
          if (!preg_match('/^[a-zA-Z0-9_\-]+$/',$settings['file_prefix'])) {
            $errors = true;
            $doc_type_results[] = "{$settings['file_prefix']} is an invalid value. Must be alphanumeric; may include - or _.";
            $display['doc'][$docType]['file_prefix']['value'] = $settings['file_prefix'];
            $display['doc'][$docType]['file_prefix']['error'] = true;
          }
          if ( ($settings['file_prefix'] != $document_types[$docType]['file_prefix'])
            && in_array(strtolower($settings['file_prefix']), $file_prefixes)) {
            $errors = true;
            $doc_type_results[] = "{$settings['file_prefix']} already exists.";
            $display['doc'][$docType]['file_prefix']['error'] = true;            
          }          
        }
        
        // check added type for errors 
        if (isset($_POST['_ADD']) && !empty($_POST['_ADD']['slug'])) {
          // is slug okay?
          if (!preg_match('/^[a-z0-9_\-]+$/',$_POST['_ADD']['slug'])) {
            $errors = true;
            $doc_type_results[] = "{$_POST['_ADD']['slug']} is an invalid value. Must be lowercase alphanumeric; may include - or _.";
            $display['_ADD']['slug']['error'] = true;
          }
          if (array_key_exists($_POST['_ADD']['slug'], $document_types)) {
            $errors = true;
            $doc_type_results[] = "{$_POST['_ADD']['slug']} already exists.";
            $display['_ADD']['slug']['error'] = true;            
          }
          // is file prefix okay?
          if (empty($_POST['_ADD']['file_prefix'])) {
            $errors = true;
            $doc_type_results[] = 'Missing file_prefix for added doc type.';
            $display['_ADD']['file_prefix']['error'] = true;
          }
          if (!preg_match('/^[a-zA-Z0-9_\-]+$/',$_POST['_ADD']['file_prefix'])) {
            $errors = true;
            $doc_type_results[] = "{$_POST['_ADD']['file_prefix']} is an invalid value. Must be alphanumeric; may include - or _.";
            $display['_ADD']['file_prefix']['error'] = true;
          }
          if (in_array(strtolower($_POST['_ADD']['file_prefix']), $file_prefixes)) {
            $errors = true;
            $doc_type_results[] = "{$_POST['_ADD']['file_prefix']} already exists.";
            $display['_ADD']['file_prefix']['error'] = true;            
          }          
          // does title exist?
          if (empty($_POST['_ADD']['title'])) {
            $errors = true;
            $doc_type_results[] = 'Missing title for added doc type.';
            $display['_ADD']['title']['error'] = true;
          }
          // does display exist?
          if (empty($_POST['_ADD']['display'])) {
            $errors = true;
            $doc_type_results[] = 'Missing display for added doc type.';
            $display['_ADD']['display']['error'] = true;
          }          
          // Don't bother with date ... a default will be set
        }
          
        // Don't proceed if errors
        if ($errors) {
          $doc_type_results[] = 'Updates were not made.';
        } else {
          // Delete document type?
          if (isset($_POST['deleteDocumentType'])) {
            foreach ($_POST['deleteDocumentType'] as $docType) {
              unset($document_types[$docType]);
              unset($display['doc'][$docType]);
              $doc_type_results[] = "Removed document type $docType.";
            }
          }
          // Add document type
          if (isset($_POST['_ADD']) && !empty($_POST['_ADD']['slug'])) {
            $new_slug = $_POST['_ADD']['slug'];
            $display['doc'][$new_slug]['slug']['value']    = $new_slug;
            $display['doc'][$new_slug]['slug']['error']    = false;
            $document_types[$new_slug]['title']            = htmlspecialchars($_POST['_ADD']['title']);
            $display['doc'][$new_slug]['title']['value']   = $document_types[$new_slug]['title'];
            $display['doc'][$new_slug]['title']['error']   = false;
            $document_types[$new_slug]['display']          = htmlspecialchars($_POST['_ADD']['display']);
            $display['doc'][$new_slug]['display']['value'] = $document_types[$new_slug]['display'];
            $display['doc'][$new_slug]['display']['error'] = false;

            if (empty($_POST['_ADD']['date_format'])) {
              $document_types[$new_slug]['date_format']          = "F j, Y";
              $display['doc'][$new_slug]['date_format']['value'] = $document_types[$new_slug]['date_format'];
              $display['doc'][$new_slug]['date_format']['error'] = false;
            } else {
              $document_types[$new_slug]['date_format']          = htmlspecialchars($_POST['_ADD']['date_format']);
              $display['doc'][$new_slug]['date_format']['value'] = $document_types[$new_slug]['date_format'];
              $display['doc'][$new_slug]['date_format']['error'] = false;
            }
            $display['doc'][$new_slug]['display']['value']     = $document_types[$new_slug]['display'];
            $display['doc'][$new_slug]['display']['error']     = false;
            $document_types[$new_slug]['file_prefix']          = $_POST['_ADD']['file_prefix'];
            $display['doc'][$new_slug]['file_prefix']['value'] = $document_types[$new_slug]['file_prefix'];
            $display['doc'][$new_slug]['file_prefix']['error'] = false;
            $doc_type_results[] = "Added document type $new_slug";
            // Clear out $display['_ADD']
            $display['_ADD']['slug']        = array('value' => '', 'error' => false);
            $display['_ADD']['title']       = array('value' => '', 'error' => false);
            $display['_ADD']['display']     = array('value' => '', 'error' => false);
            $display['_ADD']['date_format'] = array('value' => '', 'error' => false) ;
            $display['_ADD']['file_prefix'] = array('value' => '', 'error' => false);
          }
          // Change document types
          foreach ($_POST['doc'] as $docType=>$settings) {
            $docTypeChanges = false;
            // make sure it wasn't deleted already
            if (array_key_exists($docType,$document_types)) {
              
              if ($settings['title'] != $document_types[$docType]['title']) {
                $document_types[$docType]['title']          = htmlspecialchars($settings['title']);
                $display['doc'][$docType]['title']['value'] = $document_types[$docType]['title'];
                $docTypeChanges = true;
              }
              if ($settings['display'] != $document_types[$docType]['display']) {
                $document_types[$docType]['display']          = htmlspecialchars($settings['display']);
                $display['doc'][$docType]['display']['value'] = $document_types[$docType]['display'];
                $docTypeChanges = true;
              }
              if ($settings['date_format'] != $document_types[$docType]['date_format']) {
                if (empty($settings['date_format'])) {
                  $document_types[$docType]['date_format'] = 'F j, Y';
                } else {
                  $document_types[$docType]['date_format'] = htmlspecialchars($settings['date_format']);
                }
                $display['doc'][$docType]['date_format']['value'] = $document_types[$docType]['date_format'];
                $docTypeChanges = true;
              }
              if ($settings['file_prefix'] != $document_types[$docType]['file_prefix']) {
                $document_types[$docType]['file_prefix']          = $settings['file_prefix'];
                $display['doc'][$docType]['file_prefix']['value'] = $document_types[$docType]['file_prefix'];
                $docTypeChanges = true;
              }
              // check slug last as it rekeys the arrays
              if ($settings['slug'] != $docType) {
                $new_slug = $settings['slug'];
                $document_types[$new_slug] = $document_types[$docType];
                unset($document_types[$docType]);
                $display['doc'][$new_slug] = $display['doc'][$docType]; 
                unset($display['doc'][$docType]);
                $display['doc'][$new_slug]['slug']['value'] = $new_slug;
                $display['doc'][$new_slug]['slug']['error'] = false;
                $results[] = "Changed slug for $docType to $new_slug.";
                $docTypeChanges = true;
              }
            } // endif array_key_exists
            if ($docTypeChanges) {
              $doc_type_results[] = "Changes made to $docType";
            }
          } // end foreach

        } // end if not errors
        
        // Update options
        $minAgNewsOptions['document_types'] = $document_types;
        update_option($this->adminOptionsName, $minAgNewsOptions);
      
        if (empty($doc_type_results)) {
          $doc_type_results[] = 'No changes were made.';
        }
      
      } // end if docTypes changes were submitted
      
      function err($flag) {
        echo ($flag) ? ' class="form-invalid"' : '';
        return;
      }

      // Display form
      ?>
      <div class=wrap>
        
        <h2 class="minagnews32">Minutes, Agendas, Newsletters Settings</h2>
        
        <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">

        <h3>Documents Directory</h3>
        <?php
        if (!empty($upload_dir_results)) {
          ?>
          <div class="updated"><p><strong><?php echo implode('<br />',$upload_dir_results); ?></strong></p></div>
          <?php
        }
        ?>
        
        <p<?php err($display['uploads_dir']['error']);?>><label for="uploads_dir">Directory in uploads folder to store document:</label> 
          <input type="text" id="uploads_dir" name="uploads_dir" value="<?php echo $display['uploads_dir']['value'];?>"  /> <em>Currently set to <strong><?php echo $uploads_dir;?></strong>.</em> </p>
        
        <div class="submit">
          <input class="button-primary" type="submit" name="update_uploadsDir" value="Update Directory" />
        </div>
        
        
        <h3>Document Types</h3>
        <?php
        if (!empty($doc_type_results)) {
          ?>
          <div class="updated"><p><strong><?php echo implode('<br />',$doc_type_results); ?></strong></p></div>
          <?php
        }
        ?>
        <table width="100%" cellpadding="3" cellspacing="0" class="widefat">
          <thead>
          <tr>
            <th>Del</th>
            <th>Slug</th>
            <th>Title used in table displays</th>
            <th>Link format (see codes below)</th>
            <th>Date format</th>
            <th>File prefix</th>
          </tr>
          </thead>
          <tbody>
          <?php
          foreach ($display['doc'] as $docType=>$docDef) {
            ?>
            <tr>
            <td><input type="checkbox" name="deleteDocumentType[]" value="<?php echo $docType?>" /></td>
            <td <?php err($docDef['slug']['error']);?>>
                <input type="text" name="doc[<?php echo $docType;?>][slug]" value="<?php echo $docDef['slug']['value'];?>" /></td>
            <td <?php err($docDef['slug']['error']);?>>
                <input type="text" name="doc[<?php echo $docType;?>][title]" value="<?php echo $docDef['title']['value'];?>" /></td>
            <td <?php err($docDef['slug']['error']);?>>
                <input type="text" name="doc[<?php echo $docType;?>][display]" value="<?php echo $docDef['display']['value'];?>" size="30"/></td>
            <td <?php err($docDef['slug']['error']);?>>
                <input type="text" name="doc[<?php echo $docType;?>][date_format]" value="<?php echo $docDef['date_format']['value'];?>" /></td>
            <td <?php err($docDef['slug']['error']);?>>
                <input type="text" name="doc[<?php echo $docType;?>][file_prefix]" value="<?php echo $docDef['file_prefix']['value'];?>" /></td>
            </tr>                        
            <?php
          }
          ?>
            <tr>
            <td>Add: </td>
            <td <?php err($display['_ADD']['slug']['error']);?>><input type="text" name="_ADD[slug]" value="<?php echo $display['_ADD']['slug']['value'];?>" /></td>
            <td <?php err($display['_ADD']['title']['error']);?>><input type="text" name="_ADD[title]" value="<?php echo $display['_ADD']['title']['value'];?>" /></td>
            <td <?php err($display['_ADD']['display']['error']);?>><input type="text" name="_ADD[display]" value="<?php echo $display['_ADD']['display']['value'];?>" size="30"/></td>
            <td <?php err($display['_ADD']['date_format']['error']);?>><input type="text" name="_ADD[date_format]" value="<?php echo $display['_ADD']['date_format']['value'];?>" /></td>
            <td <?php err($display['_ADD']['file_prefix']['error']);?>><input type="text" name="_ADD[file_prefix]" value="<?php echo $display['_ADD']['file_prefix']['value'];?>" /></td>
            </tr>
        </tbody>
        </table>
        
        <div class="submit">
          <input class="button-primary" type="submit" name="update_docTypes" value="Update Document Types" />
        </div>
        
        </form>
  
        <div class="explanation">
          
          <h4>About Document Types</h4>
          
          <p>The <strong>Minutes-Agendas-Newsletters</strong> plugin can be
          set to handle multiple types of documents, not just minutes,
          agendas, and newsletters. You also have the option of omitting
          document types that you don't use. For example, perhaps you only
          need minutes and newsletters.</p>
          
          <p>Each document needs the following information:</p>
          <ul>

            <li><strong>slug</strong> - used internally to identify the
            document type. Must be lowercase alphanumeric (may include -and _
            ) and must be unique.</li>

            <li><strong>title</strong> - when displaying a table or a form
            option, the title is used to represent the document type (e.g., a
            column heading).</li>

            <li><strong>link format</strong> - when a link to a document is
            made, it is formatted with this text. Certain codes will be
            replaced with the values appropriate to that document:

                <ul style="margin: 10px;">
                  <li><strong>[[[date]]]</strong> - the date in the format specified by 'date format'</li>
                  <li><strong>[[[filename]]]</strong> - the actual filename for the pdf document</li>
                  <li><strong>[[[doctype]]]</strong> - the document type, using the 'title' value</li>
                  <li><strong>[[[year]]]</strong> - the document year</li>
                  <li><strong>[[[[month]]]]</strong> - the document month</li>
                  <li><strong>[[[date]]]</strong> - the document day</li>
                </ul></li>

            <li><strong>date format</strong> - if [[[date]]] is used in the
            link format, it will be formatted according to this format. Valid
            values are the php date formats (see ). The default is 'F j, Y'
            which formats like January 2, 2011.</li>
            
            <li><strong>file prefix</strong> - when you upload a document, it
            is renamed to a standard format using the file prefix. Must be
            alphanumeric (may include -and _ ) and must be unique. The format
            is fileprefix_YYYY-MM-DD.pdf.</li>

         </ul>
            
          <p>To delete a document type, check the box next to that document
          type and then Update Document Types. If you already had some of that
          type of document uploaded in your uploads directory, deleting the
          document type will <strong>not</strong> delete those documents. So
          if you add the document type back in at a later date, those
          documents will reappear. (Make sure that you match the existing file
          prefix when you add the document type back, or they won't be
          recognized.)</p>
            
        </div>
      </div><!-- end .wrap -->
      
      <?php
    } //End function displayAdminSettings()    
    
    
    // Upload Minutes, Agendas and Newsletters
    function uploadMinutesAgendasNewsletters() {
      
      $minAgNewsOptions = $this->getAdminOptions();
      $uploadsDir       = $minAgNewsOptions['uploads_dir'];
      $document_types   = $minAgNewsOptions['document_types'];
      $uploads          = wp_upload_dir();
      $uploadsPath      = $uploads['basedir'] . "/$uploadsDir";
      $uploadsURL       = $uploads['baseurl'] . "/$uploadsDir";
      $docDate          = '';
      $docType          = 'Minutes';
      
      require_once('minagnews-parse-uploads.php');
      $u = new MinAgNewsParseUploads($uploadsPath, $document_types);
      
      // Upload document submitted?
      if (isset($_POST['upload_document'])) {
        
        $results = array();
        
        // Is document of the correct type? Note this is File Type, which is the
        // correct prefix for the file, not the document_type used in the object
        $validType = false;
        if ($u->is_valid_fileType($_POST['docType'])) {
          $validType = true;
          $docType = $_POST['docType'];
        } else {
          $results[] = 'Please select a document type ';
        }
        
        // Is date valid?
        $validDate = false;
        $enteredDate = strtotime($_POST['docDate']);
        if ($enteredDate) {
          $validDate = true;
          $docDate = date('Y-m-d', $enteredDate);
        } else {
          $results[] = 'Date value is empty or invalid.';
        }
        
        // Upload document
        if ($validDate && $validType) {
          // create file name
          $uploadName = $docType . '_' . $docDate . '.pdf';
                
          // upload file
          $uploadResults = minagnews_handle_upload($_FILES['uploadFile'],false,null,$uploadsDir, $uploadName);
          if ($uploadResults['error']) {
            $results[] = $uploadResults['error'];
          } else {
            $results[] = $uploadName . ' uploaded successfully.';
          }
        }
        
        // Display results message
        ?>
        <div class="updated"><p><strong><?php echo implode('<br />',$results); ?></strong></p></div>
        <?php
        
      } // end upload document
      
      // Delete documents?
      if (isset($_POST['delete_documents'])) {
        $results = array();
        if (isset($_POST['delete'])) {
          $files_to_delete = $_POST['delete'];
          foreach ($files_to_delete as $delfile) {
            $filepath = $uploadsPath . '/' . $delfile;
            if (unlink($filepath)) {
              $results[] = 'Deleted ' . $delfile;
            } else {
              $results[] = 'Could not delete ' . $delfile;
            }
          }
        } else {
          $results[] = 'No files were selected to be deleted.';
        }
        // Display results message
        ?>
        <div class="updated"><p><strong><?php echo implode('<br />',$results); ?></strong></p></div>
        <?php
      
      } // end delete documents

      // Display form
      
      // Parse the directory of existing documents
      $u->parse();
      
      ?>
      <div class=wrap>
        <h2 class="minagnews32">Manage Minutes, Agendas and Newsletters</h2>
        
        <h3>Add document</h3>
        
        <form enctype="multipart/form-data" method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
                  
        <p>Note: Your document must be in pdf format. It will be renamed using the meeting/newsletter date, e.g., Minutes_2011-03-15.pdf.</p>
        
        <p><label for="docDate">Meeting (or Newsletter) date: </label><input type="text" name="docDate" value="<?php echo $docDate;?>" /></p>
        
        <p><label for="docType">Document type:</label>
          <?php
          $radio_group = array();
          foreach ($u->document_types as $d=>$docdef) {
            $value = $docdef['file_prefix'];
            $title = $docdef['title'];
            $checked = ($docType==$title)?' checked="checked" ' : '';
            $radio_group[] = '<input type="radio" name="docType" value="'.$value.'"' . $checked . '/> '.$title;
          }
          echo implode('&nbsp;&nbsp;&nbsp',$radio_group);
          ?>
          </p>

        <p><label for="uploadFile">Select file: <input type="file" name="uploadFile" id="uploadFile" /></label> (must be a pdf file)</p>

        <input type="hidden" name="action" value="wp_handle_upload" />

        <div class="submit">
          <input type="submit" name="upload_document" value="<?php echo 'Upload document'; ?>" />
        </div>
        
        </form>
        
        <h3>Uploaded Documents</h3>
        
        <?php
        
        if (!$u->documents_exist()) {
          ?>
          <p>There are no uploaded minutes, agendas or newsletters.</p>
          <?php
        } else {
          ?>
        
           <p>To delete documents, check the box next to the name and then
          click the <strong>Delete checked documents</strong> button at the
          bottom of the table. Careful &mdash; deleted documents can not be
          recovered.</p>
          
          <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
          <?php
          $td_content = '<div><input type="checkbox" name="delete[]" value="[[[filename]]]" /> <a href="'. $uploadsURL. '/[[[filename]]]">[[[filename]]]</a></div>';
          echo $u->createTable($td_content,'all','all',array('class'=>'widefat'));
          ?>  
          <div class="submit">
            <input class="button-primary" type="submit" name="delete_documents" value="<?php echo 'Delete checked documents'; ?>" />
          </div>
          </form>
        </div>
      <?php   
      
      } // end if minutes, agendas or newsletters exist   
      
    } // end uploadMinutesAgendasNewsletters
    
    // Shortcodes
    
    // [minagnews-table show="show-value" id="id" class="class" attr="attr"]
    // Creates a table of the minutes/agendas/newsletters found in the uploads directory.
    // 'show' controls which document types are displayed
    function table_shortcode($atts) {
      
      $attributes = shortcode_atts(array(
      	  'show' => 'all',
      	  'year' => 'all',
      	  'omit_empty' => false,
      	  'months_order' => 'ASC',
      	  'id' => '',
      	  'class' => 'minagnews_tbl',
      	  'width' => '100%',
      	  'cellspacing' => '0',
      	  'cellpadding' => '4',
      	  'attr' => ''
          ), $atts);
      $show = $attributes['show'];
      $year = $attributes['year'];
      $months_order = ('ASC' == strtoupper($attributes['months_order'])) ? 'ASC' : 'DESC';
      $omit_empty   = ('true' === strtolower($attributes['omit_empty']) || true === $attributes['omit_empty'] );
          
       // Parse the directory of existing documents
      require_once('minagnews-parse-uploads.php');
			$minAgNewsOptions  = $this->getAdminOptions();
      $uploadsDir     = $minAgNewsOptions['uploads_dir'];
      $document_types = $minAgNewsOptions['document_types'];
      $uploads        = wp_upload_dir();
      $uploadsPath    = $uploads['basedir'] . "/$uploadsDir";
      $uploadsURL     = $uploads['baseurl'] . "/$uploadsDir";
      $u              = new MinAgNewsParseUploads($uploadsPath, $document_types);

      $u->parse();     
      $td_content = '<a href="'. $uploadsURL. '/[[[filename]]]">[[[display]]]</a>';  

      return $u->createTable($td_content, $show, $year, $attributes, $months_order, $omit_empty);
      
    } // end function minagnews_table_shortcode

    // [minagnews-link doctype="doctype" year="year" month="month" day="day" id="id" class="class" ]
    // Creates a link to a document, or nothing if invalid data was received
    // The shortcode does NOT require the document to actually exist
    function link_shortcode($atts) {
      extract(shortcode_atts(array(
      	  'doctype' => '',
      	  'date' => '',
      	  'id' => '',
      	  'class' => 'minagnews_link'
          ), $atts));
          
      $id = ($id) ? " id='{$id}'" : '';
      $class = ($class) ? " class='{$class}'" : '';
          
      $minAgNewsOptions = $this->getAdminOptions();
      $document_types = $minAgNewsOptions['document_types'];
      // If valid doctype
      if (array_key_exists($doctype, $document_types )) {
        $file_prefix = $document_types[$doctype]['file_prefix'];
        
        $date_value = strtotime($date);
        
        if ( $date == 'latest' || ($date_value !== false) )
        {
          require_once('minagnews-parse-uploads.php');
          $uploadsDir  = $minAgNewsOptions['uploads_dir'];
          $uploads     = wp_upload_dir();
          $uploadsPath = $uploads['basedir'] . "/$uploadsDir";
          $uploadsURL  = $uploads['baseurl'] . "/$uploadsDir";
          $u           = new MinAgNewsParseUploads($uploadsPath, $document_types);
          if ($date == 'latest') {
            $u->parse();
            $date = $u->latestDocumentDate($doctype);
            $date_value = strtotime($date);
          }
          $year        = date('Y', $date_value);
          $month       = date('m', $date_value);
          $day         = date('d', $date_value);
          $filename    = $u->createFilename($doctype, $date);
          $pattern     = $document_types[$doctype]['display'];

          $link_text   = $u->interpret($pattern, $filename, $doctype, $year, $month, $day);
          
          return "<a href='{$uploadsURL}/{$filename}'{$id}{$class}>{$link_text}</a>";
          
        } else {return 'invalid date' . $date; }     
      }
          
      return 'array key did not exist '.$doctype ; // fail gracefully
      
    } // end function minagnews_link_shortcode

    // Add buttons to TinyMCE editor
  	function addbuttons() {
  	   // Don't bother doing this stuff if the current user lacks permissions
  	   if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') )
  		 return;
	 
  	   // Add only in Rich Editor mode
  	   if ( get_user_option('rich_editing') == 'true') {
  			add_filter("mce_external_plugins", array(&$this,"add_TinyMCE_plugin"));
  			add_filter('mce_buttons', array(&$this,'register_TinyMCE_button'));
  	   }
  	}
 
  	function register_TinyMCE_button($buttons) {
  	   array_push($buttons, "separator", "minAgNews");
  	   return $buttons;
  	}
 
  	function add_TinyMCE_plugin($plugin_array) {
  	   $plugin_array['minAgNews'] = plugins_url('/tinymce/editor_plugin.js?ver='.rand(), __FILE__);
  	   return $plugin_array;
  	}

  	function adminjavascript(){
  		?>
  		<script type="text/javascript" src="<?php echo plugins_url('/nonvisual/minagnews.js?ver='.rand(), __FILE__);?>"></script>
  		<script type="text/javascript">
  		//<![CDATA[
  		function edminagnews() {
  			var content = getContentSelection(window);
  		  tb_show("<?php echo 'Link to minutes/agendas/newsletters'; ?>","<?php echo plugins_url('/nonvisual/minagnews-list.php?ver='.rand(), __FILE__)?>?TB_iframe=true",false);
  		}
  		//]]>
  		</script>	
  		<?php
  	}

  	function quicktags(){
  		$buttonshtml = '<input type="button" class="ed_button" onclick="edminagnews(); return false;" title="' . 'Link minutes/agendas/newsletters' . '" value="' . 'Min/Agnda/News' . '" />';
  		?>
  		<script type="text/javascript" charset="utf-8">
  		// <![CDATA[
  		   (function(){
  			  if (typeof jQuery === 'undefined') {
  				 return;
  			  }
  			  jQuery(document).ready(function(){
  				 jQuery("#ed_toolbar").append('<?php echo $buttonshtml; ?>');
  			  });
  		   }());
  		// ]]>
  		</script>
  		<?php
  	}

  
  } // end class MinutesAgendasNewsletters
		
} // if class MinutesAgendasNewsletters does not exist

if ( class_exists( 'MinutesAgendasNewsletters' ) ) {
	$minutesAgendasNewsletters = new MinutesAgendasNewsletters();
}

// our own version of wp_handle_upload that handles our special directory
include('minagnews-handle-upload.php');
?>