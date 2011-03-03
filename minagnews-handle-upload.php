<?php
// Customized version of wp_handle_upload that handles the special directory for minagnews
function minagnews_handle_upload( &$file, $overrides = false, $time = null, $uploadsDir, $uploadName ) {
    // The default error handler.
    if ( ! function_exists( 'wp_handle_upload_error' ) ) {
		function wp_handle_upload_error( &$file, $message ) {
            return array( 'error'=>$message );
        }
    }

    $file = apply_filters( 'wp_handle_upload_prefilter', $file );

    // You may define your own function and pass the name in $overrides['upload_error_handler']
    $upload_error_handler = 'wp_handle_upload_error';

    // You may have had one or more 'wp_handle_upload_prefilter' functions error out the file.  Handle that gracefully.
    if ( isset( $file['error'] ) && !is_numeric( $file['error'] ) && $file['error'] )
        return $upload_error_handler( $file, $file['error'] );

    // You may define your own function and pass the name in $overrides['unique_filename_callback']
    $unique_filename_callback = null;

    // $_POST['action'] must be set and its value must equal $overrides['action'] or this:
    $action = 'wp_handle_upload';

    // Courtesy of php.net, the strings that describe the error indicated in $_FILES[{form field}]['error'].
    $upload_error_strings = array( false,
        __( "The uploaded file exceeds the <code>upload_max_filesize</code> directive in <code>php.ini</code>." ),
        __( "The uploaded file exceeds the <em>MAX_FILE_SIZE</em> directive that was specified in the HTML form." ),
        __( "The uploaded file was only partially uploaded." ),
        __( "No file was uploaded." ),
        '',
        __( "Missing a temporary folder." ),
        __( "Failed to write file to disk." ),
        __( "File upload stopped by extension." ));

    // All tests are on by default. Most can be turned off by $override[{test_name}] = false;
    $test_form = true;
    $test_size = true;
    $test_upload = true;

    // If you override this, you must provide $ext and $type!!!!
    $test_type = true;
    $mimes = false;

    // Install user overrides. Did we mention that this voids your warranty?
    if ( is_array( $overrides ) )
        extract( $overrides, EXTR_OVERWRITE );

    // A correct form post will pass this test.
    if ( $test_form && (!isset( $_POST['action'] ) || ($_POST['action'] != $action ) ) )
        return call_user_func($upload_error_handler, $file, __( 'Invalid form submission.' ));

    // A successful upload will pass this test. It makes no sense to override this one.
    if ( $file['error'] > 0 )
        return call_user_func($upload_error_handler, $file, $upload_error_strings[$file['error']] );

    // A non-empty file will pass this test.
    if ( $test_size && !($file['size'] > 0 ) ) {
        if ( is_multisite() )
            $error_msg = __( 'File is empty. Please upload something more substantial.' );
        else
            $error_msg = __( 'File is empty. Please upload something more substantial. This error could also be caused by uploads being disabled in your php.ini or by post_max_size being defined as smaller than upload_max_filesize in php.ini.' );
        return call_user_func($upload_error_handler, $file, $error_msg);
    }

    // A properly uploaded file will pass this test. There should be no reason to override this one.
    if ( $test_upload && ! @ is_uploaded_file( $file['tmp_name'] ) )
        return call_user_func($upload_error_handler, $file, __( 'Specified file failed upload test.' ));

    // A correct MIME type will pass this test. Override $mimes or use the upload_mimes filter.
    if ( $test_type ) {
$mimes = array('pdf' => 'application/pdf');
        $wp_filetype = wp_check_filetype_and_ext( $file['tmp_name'], $file['name'], $mimes );

        extract( $wp_filetype );

        // Check to see if wp_check_filetype_and_ext() determined the filename was incorrect
        if ( $proper_filename )
            $file['name'] = $proper_filename;

        if ( ( !$type || !$ext ) && !current_user_can( 'unfiltered_upload' ) )
            return call_user_func($upload_error_handler, $file, __( 'File type must be .pdf.' ));

        if ( !$ext )
            $ext = ltrim(strrchr($file['name'], '.'), '.');

        if ( !$type )
            $type = $file['type'];
    } else {
        $type = '';
    }

    // A writable uploads dir will pass this test. Again, there's no point overriding this one.
//    if ( ! ( ( $uploads = wp_upload_dir($time) ) && false === $uploads['error'] ) )
//        return call_user_func($upload_error_handler, $file, $uploads['error'] );
$uploads = wp_upload_dir();
$uploadsPath = $uploads['basedir'] . "/$uploadsDir";
if (!is_dir($uploadsPath)) {
  mkdir($uploadsPath);
}


//      $filename = wp_unique_filename( $uploads['path'], $file['name'], $unique_filename_callback );
$filename = $uploadName;
    

    // Move the file to the uploads dir
//      $new_file = $uploads['path'] . "/$filename";
    $new_file = $uploadsPath . "/$filename";
// does the file already exist?
    if (file_exists($new_file)) {
        return $upload_error_handler( $file, sprintf( __('%s already exists. Please delete the existing file and try your upload again. (See below)' ), $filename ) );
    }
    if ( false === @ move_uploaded_file( $file['tmp_name'], $new_file ) )
        return $upload_error_handler( $file, sprintf( __('The uploaded file could not be moved to %s.' ), $filename ) );

    // Set correct file permissions
    $stat = stat( dirname( $new_file ));
    $perms = $stat['mode'] & 0000666;
    @ chmod( $new_file, $perms );

    // Compute the URL
//      $url = $uploads['url'] . "/$filename";
    $url = $uploadsPath. "/$filename";

    if ( is_multisite() )
        delete_transient( 'dirsize_cache' );

    return apply_filters( 'wp_handle_upload', array( 'file' => $new_file, 'url' => $url, 'type' => $type ), 'upload' );
}
?>