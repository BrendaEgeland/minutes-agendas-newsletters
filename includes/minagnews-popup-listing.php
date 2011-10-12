<?php
// The content of the pop-up window in the editor for adding a link to minutes/agendas/newsletters

// 2011-01-25: Corrected radio group values displayed at top of listing. Was using file prefix rather than the document type, as required by the shortcode
// 2011-10-11: Clarify text in <h3>*or*, Insert Link to Document

$minAgNewsOptions = get_option('MinAgNewsAdminOptions');
$uploadsDir = $minAgNewsOptions['uploads_dir'];
$document_types = $minAgNewsOptions['document_types'];
$uploads = wp_upload_dir();
$uploadsPath = $uploads['basedir'] . "/$uploadsDir";
$uploadsURL =  $uploads['baseurl'] . "/$uploadsDir";

require_once('../minagnews-parse-uploads.php');
$u = new MinAgNewsParseUploads($uploadsPath, $document_types);
$u->parse();     

      
?>
<div id="minagnews_list">        

<h2><img src="../images/minagnews20.png" width="20" height="20" alt="Minutes, Agendas, Newsletters" style="float: left; margin: 0 5px 0 0;" > Minutes, Agendas and Newsletters</h2>
<p>To upload Minutes, Agendas or Newsletters, go to <strong>Media --&gt; Minutes, Agendas, Newsletters</strong> .</p>

<?php
if (!$u->documents_exist()) {
  ?>
<p>There are no uploaded minutes, agendas or newsletters.</p>
  <?php
} else {
?>
<h3>Insert Table</h3>
  <form action="" method="GET" name="insertTableForm" onsubmit="return insertTableShortcode();">
  <table width="100%" cellpadding="4" cellspacing="0" class="min_ag">
    <tr>
      <td style="vertical-align: top;"><label for="docType">Show Documents: </label></td>
      
      <td style="vertical-align: top;"> 
        <?php
          $check_group = array();
          // Only show doc types that exist
          $items_in_row = 1;
          foreach ($u->document_types as $d=>$docdef) {
            $value = $d;
            $title = $docdef['title'];
            if ($u->docs_exist[$d]) {
              $add_break = ($items_in_row++ % 5) ? '' : '<br />';
              $check_group[] = '<input type="checkbox" name="docType" value="'.$value.'"/> '.$title.$add_break;
            }
          }
          echo implode('&nbsp;&nbsp;&nbsp',$check_group);
        ?>
        &nbsp;&nbsp;&nbsp;
        <input type="checkbox" name="docType" value="all" /> All
      </td>
    </tr>
    
    <tr>
      <td style="vertical-align: top;"><label for="year">Show Years: </label></td>       
      <td style="vertical-align: top;"> 
        <?php
          $check_group = array();
          // Only show years that have documents
          $items_in_row = 1;
          for ($year = $u->start_year; $year <= $u->end_year; $year++) {
            if ($u->year_has_documents($year,'all')) {
              $add_break = ($items_in_row++ % 5) ? '' : '<br />';
              $check_group[] = '<input type="checkbox" name="year" value="'.$year.'"/>&nbsp;'.$year.$add_break;
            }
          }
          echo implode('&nbsp;&nbsp;&nbsp',$check_group);
        ?>
        <br />
        <input type="checkbox" name="year" value="previous" /> Previous Year
        <input type="checkbox" name="year" value="current" /> Current Year
        <input type="checkbox" name="year" value="all" /> All
      </td>
    </tr>
    
    <tr>      
      <td colspan="2"><input type="submit" name="insertTable" value="Insert Table" /></td>
    </tr>
    </table>
  </form>

<h3>or, Insert Link to Document</h3>

<p><strong>To insert the link, click on the document name.</strong></p>
<?php
$td_content = '<a href="'. $uploadsURL. '/[[[filename]]]" onclick="return insertDocLink(this)">[[[display]]]</a>';
$show = 'all';
$year = 'all';
echo $u->createTable($td_content, $show, $year);
?>
  <?php
}
?>
</div>