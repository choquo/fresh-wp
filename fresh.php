<?php
/**
 * Fresh WP
 * Author: Carlos Maldonado (@choquo)
 * https://github.com/choquo/fresh-wp
 * License: MIT
 */

include_once 'fresh-plugins.php';
include_once 'fresh-theme.php';

//Remove things from database
if( isset($_POST['go']) ){
    include_once 'wp-config.php';
    $con = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    // Check connection
    if (mysqli_connect_errno()){
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }else{
        //Perform queries 
        mysqli_query($con,"DELETE FROM ".$table_prefix.'posts');
        mysqli_query($con,"DELETE FROM ".$table_prefix.'postmeta');
        mysqli_query($con,"DELETE FROM ".$table_prefix.'comments');
        mysqli_query($con,"DELETE FROM ".$table_prefix.'commentmeta');
        //$metaboxhidden = 'a:4:{i:0;s:19:"dashboard_right_now";i:1;s:18:"dashboard_activity";i:2;s:21:"dashboard_quick_press";i:3;s:17:"dashboard_primary";}';
        //mysqli_query($con,"UPDATE ".$table_prefix.'usermeta'." SET meta_value='".$metaboxhidden."' WHERE meta_key='metaboxhidden_dashboard' ");
        //mysqli_query($con,"UPDATE ".$table_prefix.'usermeta'." SET meta_value='0' WHERE meta_key='show_welcome_panel' ");
        
        mysqli_query($con,"UPDATE ".$table_prefix.'options'." SET option_value='/%postname%/' WHERE option_name='permalink_structure' ");
        //mysqli_query($con,"UPDATE ".$table_prefix.'options'." SET option_value='' WHERE option_name='blogdescription' ");
        mysqli_query($con,"UPDATE ".$table_prefix.'options'." SET option_value='".$theme["theme_folder_name"]."' WHERE option_name='template' ");
        mysqli_query($con,"UPDATE ".$table_prefix.'options'." SET option_value='".$theme["theme_folder_name"]."' WHERE option_name='stylesheet' ");

        //WPS Hide Login - Plugin
        //Change login path: example, http://domain.com/login, //http://domain.com/admin, etc
        //Try to get the whl_page value from the table, if exists, then update if not then insert
        $login_word_query = mysqli_query($con,"SELECT * FROM ".$table_prefix.'options'." WHERE option_name='whl_page'");
        $login_word_count = mysqli_num_rows($login_word_query);
        if( $login_word_count>=1 ){
            mysqli_query($con,"UPDATE ".$table_prefix.'options'." SET option_value='".$_POST['login-path']."' WHERE option_name='whl_page' ");
        }else{
            mysqli_query($con,"INSERT INTO ".$table_prefix.'options'." (option_name, option_value) VALUES ('whl_page', '".$_POST['login-path']."') ");
        }

        mysqli_close($con);
    }
}
?>


<?php 
//PLUGINS
if( isset($_POST['go']) ){

    //Download plugins and unzip it
    foreach( $plugins as $plugin_data ){
        $url = $plugin_data["link"];
        $random = rand(0,100);
        $zipFile = "wp-content/plugins/zipfile".$random.".zip"; // Local Zip File Path
        $zipResource = fopen($zipFile, "w");
        // Get The Zip File From Server
        $curlTask = curl_init();
        curl_setopt($curlTask, CURLOPT_URL, $url);
        curl_setopt($curlTask, CURLOPT_FAILONERROR, true);
        curl_setopt($curlTask, CURLOPT_HEADER, 0);
        curl_setopt($curlTask, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curlTask, CURLOPT_AUTOREFERER, true);
        curl_setopt($curlTask, CURLOPT_BINARYTRANSFER,true);
        //curl_setopt($curlTask, CURLOPT_TIMEOUT, 10); //https://curl.haxx.se/libcurl/c/CURLOPT_TIMEOUT.html
        curl_setopt($curlTask, CURLOPT_TIMEOUT, 0);
        curl_setopt($curlTask, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curlTask, CURLOPT_SSL_VERIFYPEER, 0); 
        curl_setopt($curlTask, CURLOPT_FILE, $zipResource);
        $curlExetute = curl_exec($curlTask);
        if(!$curlExetute) {
         echo "Error plugin (a) :- " . curl_error($curlTask);
        }
        curl_close($curlTask);
        
        //Unzip
        /* Open the Zip file */
        $zip = new ZipArchive;
        $extractPath = "wp-content/plugins/";
        if($zip->open( $zipFile ) != "true"){
        echo "Error plugin (b) :- Unable to open the Zip File";
        } 
        /* Extract Zip File */
        $zip->extractTo($extractPath);
        $zip->close();
        unlink( $zipFile );
    }
}
?>


<?php 
//THEME
if( isset($_POST['go']) ){

    //Download theme and unzip it    
    $url = $theme["link"];
    $random = rand(0,100);
    $zipFile = "wp-content/themes/zipfile".$random.".zip"; // Local Zip File Path
    $zipResource = fopen($zipFile, "w");
    // Get The Zip File From Server
    $curlTask = curl_init();
    curl_setopt($curlTask, CURLOPT_URL, $url);
    curl_setopt($curlTask, CURLOPT_FAILONERROR, true);
    curl_setopt($curlTask, CURLOPT_HEADER, 0);
    curl_setopt($curlTask, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($curlTask, CURLOPT_AUTOREFERER, true);
    curl_setopt($curlTask, CURLOPT_BINARYTRANSFER,true);
    //curl_setopt($curlTask, CURLOPT_TIMEOUT, 10); //https://curl.haxx.se/libcurl/c/CURLOPT_TIMEOUT.html
    curl_setopt($curlTask, CURLOPT_TIMEOUT, 0);
    curl_setopt($curlTask, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($curlTask, CURLOPT_SSL_VERIFYPEER, 0); 
    curl_setopt($curlTask, CURLOPT_FILE, $zipResource);
    $curlExetute = curl_exec($curlTask);
    if(!$curlExetute) {
        echo "Error theme (a) :- " . curl_error($curlTask);
    }
    curl_close($curlTask);
    
    //Unzip
    /* Open the Zip file */
    $zip = new ZipArchive;
    $extractPath = "wp-content/themes/";
    if($zip->open( $zipFile ) != "true"){
    echo "Error theme (b) :- Unable to open the Zip File";
    } 
    /* Extract Zip File */
    $zip->extractTo($extractPath);
    $zip->close();
    unlink( $zipFile );
}
?>


<?php 
//WRITE wp-config.php
if( isset($_POST['go']) ){
        //How it works:
        //All works based on the string 'define('WP_DEBUG', false);' of the wp-config.php file
        //If the block of code of fresh.php is alredy there then delete it first
        //And then insert the content again (if you made changes after the first run of the script)
        //This works different from the functions.php file where the code is inserted at the en of the file

        //Fresh was already there    
        $wpconfig = file_get_contents('wp-config.php');
        $wpconfigNew = preg_replace("/\/\/BEGIN fresh.php(.*)END fresh.php/smi", "", $wpconfig);
        $wpconfigNew = trim( $wpconfigNew, "[\n|\r|\n\r]"); //Remove first enter from document
        $wpconfigFile = fopen('wp-config.php', "w+");
        fwrite( $wpconfigFile, trim($wpconfigNew) );
        fclose( $wpconfigFile );

        //First time
        $wpconfig = file_get_contents('wp-config.php');        
        $wpconfigNew = preg_replace("/define\(\'WP_DEBUG\'\,\sfalse\)\;/smi", "define('WP_DEBUG', false);".PHP_EOL.stripslashes($_POST['wp-config']), $wpconfig);
        $wpconfigNew = trim( $wpconfigNew, "[\n|\r|\n\r]"); //Remove first enter from document
        $wpconfigFile = fopen('wp-config.php', "w+");
        fwrite( $wpconfigFile, trim($wpconfigNew) );
        fclose( $wpconfigFile );
}
?>


<?php 
//WRITE functions.php
if( isset($_POST['go']) ){
        //How it works:
        //Insert code at the end of the document
        //If the block of code is already into the document
        //Then delete first the content and insert the new code at the end of the file

        //Fresh was already there    
        $functions = file_get_contents('wp-content/themes/'.$theme["theme_folder_name"].'/functions.php');
        $functionsNew = preg_replace("/\/\/BEGIN fresh.php(.*)END fresh.php/smi", "", $functions);
        $functionsNew = trim( $functionsNew, "[\n|\r|\n\r]"); //Remove first enter from document
        $functionsFile = fopen('wp-content/themes/'.$theme["theme_folder_name"].'/functions.php', "w+");
        fwrite( $functionsFile, trim($functionsNew) );
        fclose( $functionsFile );

        //First time
        $functions = file_get_contents('wp-content/themes/'.$theme["theme_folder_name"].'/functions.php');
        $functionsNew = $functions . PHP_EOL.PHP_EOL.stripslashes($_POST['functions']);
        $functionsNew = trim( $functionsNew, "[\n|\r|\n\r]"); //Remove first enter from document
        $functionsFile = fopen('wp-content/themes/'.$theme["theme_folder_name"].'/functions.php', "w+");
        fwrite( $functionsFile, $functionsNew );
        fclose( $functionsFile );
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Wordpress Fresh!</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <style>
        textarea{
            font-size: .8em;
            width:100%;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-md-12 pt-4">
                <form action="fresh.php" method="POST">
                    <input type="hidden" name="go">
                    <h3>Wordpress Fresh!</h3>
                    <p>Automated tasks for your new wordpress project.</p>
                    <ul>
                        <li>Remove Akismet Plugin</li>
                        <li>Remove Hello Dolly Plugin</li>
                        <li>Uninstall default themes: twentyfifteen, twentyseventeen, twentysixteen</li>
                        <li>Install and activate your theme: <a href="<?php echo $theme["link"]; ?>" target="_blank"><?php echo $theme["theme_folder_name"]; ?></a></li>
                        <li>Install plugins: <?php foreach( $plugins as $plugin_data ){ echo $plugin_data["name"].' | '; } ?></li>
                        <li>Set the path to login to /<input name="login-path" value="<?=!isset($_POST['login-path'])?'admin':$_POST['login-path']?>"> instead of /wp-admin (WPS Hide Login) </li>
                        <li>Custom wp-config.php file</li>
                        <li>Custom functions.php file</li>
                        <li>Remove posts, postmeta, pages, comments, commentsmeta</li>
                        <li>Hide metaboxes in dashboard main page</li>
                        <li>Set permalink structure: /%postname%/</li>
                    </ul>

                    <?php 
                    if( isset($_POST['go']) ){
                        echo '<span style="font-size: 10px;"><strong>Installed Plugins:</strong><br>';
                        foreach( $plugins as $plugin_output ){
                            echo $plugin_output["name"].'<br>';
                        }
                        echo '</span><br>';
                    }
                    ?>

                    <div class="row">
                        <div class="col-md-6">
                            <label for=""><strong>wp-config.php</strong> (customize your config file)</label>
                            <br>
                            <textarea style="min-width: 500px; min-height: 200px;" name="wp-config">
//BEGIN fresh.php
//======================================================
// Limit number of revisions or disable revisions at all
define('WP_POST_REVISIONS', 3);
//define('WP_POST_REVISIONS', false);

//Disable automatic updates of the core of wordpress
define( 'WP_AUTO_UPDATE_CORE', false );

//Prevent the request of FTP credentials on install plugins
define('FS_METHOD', 'direct');
//======================================================
//END fresh.php
                            </textarea>
                        </div>
                        <div class="col-md-6">
                            <label for="">theme/<strong>functions.php</strong> (customize your functions.php file)</label>
                            <br>
                            <textarea style="min-width: 500px; min-height: 200px;" name="functions">
//BEGIN fresh.php
//======================================================
//Disable emoji mess (performance)
//Source: https://github.com/taniarascia/wp-functions
function disable_wp_emojicons() {
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	add_filter( 'tiny_mce_plugins', 'disable_emojicons_tinymce' );
	add_filter( 'emoji_svg_url', '__return_false' );
}
add_action( 'init', 'disable_wp_emojicons' );
function disable_emojicons_tinymce( $plugins ) {
	return is_array( $plugins ) ? array_diff( $plugins, array( 'wpemoji' ) ) : array();
}
//======================================================
//Disable XML-RPC xmlrpc.php (for security)
//Source: https://github.com/taniarascia/wp-functions
add_filter('xmlrpc_enabled', '__return_false');
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wlwmanifest_link' );
//======================================================
//Disable code editing for all users except for super admin
if( !is_super_admin() ){
    define('DISALLOW_FILE_EDIT', true);
}
//======================================================
//Remove dashboard metaboxes/widgets
if (!is_super_admin()) {
    remove_action('welcome_panel', 'wp_welcome_panel');
    function remove_dashboard_meta() {
        remove_meta_box('dashboard_incoming_links', 'dashboard', 'normal'); //Removes the 'incoming links' widget
        remove_meta_box('dashboard_plugins', 'dashboard', 'normal'); //Removes the 'plugins' widget
        remove_meta_box('dashboard_primary', 'dashboard', 'normal'); //Removes the 'WordPress News' widget
        remove_meta_box('dashboard_secondary', 'dashboard', 'normal'); //Removes the secondary widget
        remove_meta_box('dashboard_quick_press', 'dashboard', 'side'); //Removes the 'Quick Draft' widget
        remove_meta_box('dashboard_recent_drafts', 'dashboard', 'side'); //Removes the 'Recent Drafts' widget
        remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal'); //Removes the 'Activity' widget
        remove_meta_box('dashboard_right_now', 'dashboard', 'normal'); //Removes the 'At a Glance' widget
        remove_meta_box('dashboard_activity', 'dashboard', 'normal'); //Removes the 'Activity' widget (since 3.8)
    }
    add_action('admin_init', 'remove_dashboard_meta');
}
//======================================================
//Hide all notices only for users not for superadmins
if (!is_super_admin()) {
    function hide_update_notice_to_all_but_admin_users(){
        remove_all_actions( 'admin_notices' );
    }
    add_action( 'admin_head', 'hide_update_notice_to_all_but_admin_users', 1 );
}
//======================================================
function wp_editable_areas_setup() {
    // Add default posts and comments RSS feed links to head.
    add_theme_support('automatic-feed-links');

    // Let WordPress manage the document title.
    // By adding theme support, we declare that this theme does not use a
    // hard-coded <title> tag in the document head, and expect WordPress to
    // provide it for us.
    add_theme_support('title-tag');

    // Enable support for Post Thumbnails on posts and pages.
    add_theme_support('post-thumbnails');
    add_image_size('medium', get_option('medium_size_w'), get_option('medium_size_h'), array('center', 'center'));
    add_image_size('large', get_option('large_size_w'), get_option('large_size_h'), array('center', 'center'));

    // This theme uses wp_nav_menu() in one location.
    register_nav_menus(array(
        'top-menu'   =>  __('Top Menu', 'wp-editable-areas')
    ));

    // Switch default core markup for search form, comment form, and comments
    // to output valid HTML5.
    add_theme_support('html5', array(
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'search-form'
    ));

    // Enable support for Post Formats.
    add_theme_support('post-formats', array(
        'aside',
        'image',
        'video',
        'quote',
        'link',
        'gallery',
        'audio',
    ));

    // Add theme support for Custom Logo.
    add_theme_support('custom-logo');
}
add_action('after_setup_theme', 'wp_editable_areas_setup');
//======================================================
//END fresh.php
                            </textarea>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <button class="btn btn-lg btn-success">Fresh now!</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <?php
    //FUNCTIONS
    //----------------------------------------------------------------------------------------
    function deleteDirectory($dirPath) {
        if (is_dir($dirPath)) {
            $objects = scandir($dirPath);
            foreach ($objects as $object) {
                if ($object != "." && $object !="..") {
                    if (filetype($dirPath . DIRECTORY_SEPARATOR . $object) == "dir") {
                        deleteDirectory($dirPath . DIRECTORY_SEPARATOR . $object);
                    } else {
                        unlink($dirPath . DIRECTORY_SEPARATOR . $object);
                    }
                }
            }
        reset($objects);
        rmdir($dirPath);
        }
    }
    ?>

    <?php if( isset($_POST['go']) ){ ?>
    
        <?php
        //WORDPRESS FRESH CLEANER AFTER FIRST INSTALL
        //Remove all the unnecesary plugins and other stuff to get a fresh clean wordpress install
        //----------------------------------------------------------------------------------------
        //Delete Hello Dolly Plugin
        if( file_exists('wp-content/plugins/hello.php') ){
            unlink('wp-content/plugins/hello.php');
        }

        //Delete Akismet
        if( file_exists( 'wp-content/plugins/akismet/' ) || is_dir( 'wp-content/plugins/akismet/' ) ){
            deleteDirectory( 'wp-content/plugins/akismet/' );
        }

        //Delete default themes
        if( file_exists( 'wp-content/themes/twentyfifteen/' ) || is_dir( 'wp-content/themes/twentyfifteen/' ) ){
            deleteDirectory( 'wp-content/themes/twentyfifteen/' );
        }
        if( file_exists( 'wp-content/themes/twentyseventeen/' ) || is_dir( 'wp-content/themes/twentyseventeen/' ) ){
            deleteDirectory( 'wp-content/themes/twentyseventeen/' );
        }
        if( file_exists( 'wp-content/themes/twentysixteen/' ) || is_dir( 'wp-content/themes/twentysixteen/' ) ){
            deleteDirectory( 'wp-content/themes/twentysixteen/' );
        }
        ?>

        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <br>
                    <?php echo "All tasks Done!, 🍺 Enjoy your Fresh Wordpress Installation, don't forget 👻delete all the fresh-*.php files from your server." ?>
                    <br><br>
                </div>
            </div>
        </div>

    <?php } ?>

</body>
</html>