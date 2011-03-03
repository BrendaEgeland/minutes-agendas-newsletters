<?php
// when not using TinyMCE
// require_once('../../../../wp-blog-header.php');
require_once( dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/wp-load.php');

?><html>
<head>

</html>

<title>Link to Minutes/Agendas/Newsletters</title>
<link rel='stylesheet' href='../css/minagnews-editor.css' type='text/css' />
<script type='text/javascript' src='../js/jquery.js'></script>
<script type="text/javascript" src="minagnews.js"></script>
</head>
<body>
<p><strong><?php 'To insert the link, click on the minutes/agendas/newsletters of your choice'; ?></strong></p>
<?php include('../includes/minagnews-popup-listing.php'); ?>
</body>