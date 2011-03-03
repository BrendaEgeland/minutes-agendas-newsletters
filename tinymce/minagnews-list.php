<?php
// IE STUPIDITY: Can't use wp-blog-header since IE throws a 404 message for the popup if permalinks are turned on.
// The wp-config.php is causing 'deprecated' warnings, but at least those aren't seen.
// require_once('../../../../wp-blog-header.php'); 
require_once( dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/wp-load.php');
	
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<title>Link to Minutes/Agendas/Newsletters</title>
<link rel='stylesheet' href='../css/minagnews-editor.css' type='text/css' />
<script type='text/javascript' src='../js/jquery.js?v=142'></script>
<script type="text/javascript" src="<?php bloginfo('wpurl'); ?>/wp-includes/js/tinymce/tiny_mce_popup.js"></script>
<script type="text/javascript" src="<?php bloginfo('wpurl'); ?>/wp-includes/js/tinymce/utils/form_utils.js"></script>
<script type="text/javascript" src="minagnews.js?v=<?php echo rand();?>"></script>
</head>
<body>
<?php include('../includes/minagnews-popup-listing.php'); ?>
</body>
</html>