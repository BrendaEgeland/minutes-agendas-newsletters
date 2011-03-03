<?php
// The content of the pop-up window in the editor for adding a link to minutes/agendas/newsletters

// 2011-01-25: Corrected radio group values displayed at top of listing. Was using file prefix rather than the document type, as required by the shortcode

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
      <td><label for="docType">Show: </label></td>
      <td>
        <?php
          $radio_group = array();
          // Only show doc types that exist
          foreach ($u->document_types as $d=>$docdef) {
            $value = $d;
            $title = $docdef['title'];
            if ($u->docs_exist[$d]) {
              $radio_group[] = '<input type="radio" name="docType" value="'.$value.'"/> '.$title;
            }
          }
          echo implode('&nbsp;&nbsp;&nbsp',$radio_group);
        ?>
        &nbsp;&nbsp;&nbsp;
        <input type="radio" name="docType" value="All" /> All
      </td>
      <td><input type="submit" name="insertTable" value="Insert Table" /></td>
    </tr>
    </table>
  </form>

<h3>Insert Link to Document</h3>

<p><strong>To insert the link, click on the document name.</strong></p>
<?php
$td_content = '<a href="'. $uploadsURL. '/[[[filename]]]" onclick="return insertDocLink(this)">[[[display]]]</a>';
$show = 'all';
echo $u->createTable($td_content, $show);
?>
  <?php
}
?>
</div>