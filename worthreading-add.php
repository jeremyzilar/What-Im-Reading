<?php

// load wordpress (this is a dirty hack for now, discouraged by WordPress guidelines)
require (dirname(__FILE__).'/../../../wp-config.php');

// only available to admin user
if ( !current_user_can( 'manage_options' ) ) {
	echo "This functionality requires login!";
	exit;
}?>

<?php
	

	// Scripts
	function add_suggest_script() {
  	wp_enqueue_script( 'suggest', get_bloginfo('wpurl').'/wp-includes/js/jquery/suggest.js', array(), '', true );
	}
	add_action( 'wp_enqueue_scripts', 'add_suggest_script' );

	// Styles
	wp_register_style(
    'worthreading',
    plugins_url( 'style.css', __FILE__ ),
    array(),
    '1.2',
    'screen'
	);
	wp_enqueue_style( 'worthreading' );
?>

    <html>
    	<meta charset="utf-8">
      <head>
				<title>Add bookmark</title>
				<link rel="icon" href="<?php echo plugins_url( 'favicon-add.png' , __FILE__ ); ?>">

				<!-- Fonts: Lato / http://www.latofonts.com/ -->
				<link href='http://fonts.googleapis.com/css?family=Lato:100,300,400,700,900,100italic,300italic,400italic,700italic,900italic' rel='stylesheet' type='text/css'>

      </head>

      <body>

			<?php
				// if there is POST data, the form has been submitted
				if( 'POST' == $_SERVER['REQUEST_METHOD'] && !empty( $_POST['action'] ) &&  $_POST['action'] == "new_post") {

					// check that the two compulsory fields are filled
					if ( strlen($_POST['title']) > 0 && strpos($_POST['link_url'], 'http') !== FALSE ) {

						// create the custom post entry
						$new_post = array(
							'post_title'	=>	$_POST['title'],
							'tax_input' 	=>	array( 'publication' => explode(",", $_POST['publications']) ),
							'post_status'	=>	'publish',
							'post_type'		=>	'bookmark'
						);
						$post_id = wp_insert_post($new_post, true);


        		// add in the bookmark image
        		$imgfile = $_POST['img_file'];
        		require_once(ABSPATH . 'wp-admin' . '/includes/image.php');
    		    require_once(ABSPATH . 'wp-admin' . '/includes/file.php');
    		    require_once(ABSPATH . 'wp-admin' . '/includes/media.php');
    		    $file = media_sideload_image( $imgfile, $post_id, 'desc desc' );
    		    $attachments = get_posts(array('numberposts' => '1', 'post_parent' => $post_id, 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => 'ASC'));
    		    if(sizeof($attachments) > 0){
    		      // set image as the post thumbnail
    		      set_post_thumbnail($post_id, $attachments[0]->ID);
    		    } 

        		// add the bookmark's meta data
        		add_post_meta($post_id, 'link_url',  $_POST['link_url'],  true);
        		add_post_meta($post_id, 'link_desc', $_POST['link_desc'], true);
        		add_post_meta($post_id, 'link_via',  $_POST['link_via'],  true);
        		// add_post_meta($post_id, '_thumbnail_id',  $attachments[0]->ID,  true);


						// give positive user feedback
						echo <<< EOF
						<div id="head">
							<div class="color color-dark"></div>
							<div class="color color-med"></div>
							<div class="color color-light"></div>
							<h1>Worth Reading</h1>
						</div>
						<div class="msg">
							<p>Published!</p>
							<button onClick="window.close();" class="btn">Close</button>	
						</div>
						<script>window.onload=function(){ document.getElementById('focusbutton').focus(); }</script>
EOF;

					} else {
						
						echo <<< EOF
						<div id="head">
							<h1>Worth Reading</h1>
						</div>
						<div class="msg">
							<p>Please enter at least a title and URL for the bookmark.</p>
							<button onClick="window.history.go(-1);" id="focusbutton">Back</button>
						</div>
						<script>window.onload=function(){ document.getElementById('focusbutton').focus(); }</script>
EOF;
					}

				// if there is no POST data, display the form
				} else {

					function file_get_contents_curl($url) {
					  $ch = curl_init();
					  curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.17 (KHTML, like Gecko) Chrome/24.0.1312.52 Safari/537.17');
					  curl_setopt($ch, CURLOPT_HEADER, 0);
					  curl_setopt($ch, CURLOPT_AUTOREFERER, true);
					  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
					  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
					  curl_setopt($ch, CURLOPT_URL, $url);
					  $data = curl_exec($ch);
					  curl_close($ch);
					  return $data;
					}

					if (strlen($_GET['url']) > 0 ) { 
						$url = preg_replace('/\?.*/', '', $_GET['url']) . '?=worthreading';
					}
					// echo $url;
					$html = file_get_contents_curl($url);
					// print_r($html);
					//parsing begins here:
					$doc = new DOMDocument();
					@$doc->loadHTML($html);

					//get and display what you need:
					$nodes = $doc->getElementsByTagName('title');
					$titletag = $nodes->item(0)->nodeValue;

					$metas = $doc->getElementsByTagName('meta');

					for ($i = 0; $i < $metas->length; $i++) {
					  $meta = $metas->item($i);
					  if($meta->getAttribute('name') == 'keywords'){
					  	$keywords = $meta->getAttribute('content');
					  }
					  if($meta->getAttribute('property') == 'og:title') {
							$headline = $meta->getAttribute('content');
					  }
					  if (empty($headline)) {
					  	$headline = $_GET['title'];
					  }
					  if($meta->getAttribute('property') == 'og:site_name'){
					  	$site_name = $meta->getAttribute('content');
					  }
					  if($meta->getAttribute('property') == 'og:image'){
					  	$image = $meta->getAttribute('content');
					  }
					  if (empty($_GET['desc'])) {
							if($meta->getAttribute('name') == 'description'){
								$description = $meta->getAttribute('content');
							}
					  } else {
					  	$description = $_GET['desc'];
					  }
					}

					// echo "Site: $site_name". '<br/><br/>';
					// echo "Description: $description". '<br/><br/>';
					// echo "head: $headline". '<br/><br/>';
					// echo "Keywords: $keywords". '<br/><br/>';
					// echo "IMG: $image". '<br/><br/>';
					?>
					<div id="head">
						<div class="color color-dark"></div>
						<div class="color color-med"></div>
						<div class="color color-light"></div>
						<h1>Worth Reading</h1>
					</div>

					<form id="new_post" name="new_post" method="post" action="" enctype="multipart/form-data">

						<div class="bookmark-title">
							<label for="title">Headline:</label>
							<input type="text" id="title" tabindex="1" name="title" value="<?php if (strlen($headline) > 0 ) { echo $headline; } ?>" />	
						</div>
						
						<div class="bookmark-url">
							<label for="link_url">URL</label>
							<input type="text" id="link_url" tabindex="2" name="link_url" value="<?php echo $url; ?>" />	
							<small>e.g. http://nytimes.com</small>
						</div>

						<div class="bookmark-desc">
							<label for="link_desc">Description</label>
							<textarea type="text" id="link_desc" tabindex="3" name="link_desc" /><?php echo $description; ?></textarea>
						</div>

						<div class="bookmark-pub">
							<label for="publications">Publication</label>
							<input type="text" id="publications" tabindex="4" name="publications" value="<?php if (strlen($site_name) > 0 ) { echo $site_name; } ?>" />
							<small>e.g. The New York Times</small>
						</div>

						<div class="bookmark-img">
							<img src="<?php echo $image; ?>" alt="$headline" class="thumb">
							<div>
								<label for="img_file">Image</label>
								<input type="text" id="img_file" tabindex="6" name="img_file" value="<?php echo $image; ?>" />								
							</div>
						</div>

						<script>window.onload=function(){ document.getElementById('link_desc').focus(); }</script>
						
						<div class="bookmark-via hidden">
							<label for="link_via">via:</label>
							<input type="text" id="link_via" tabindex="5" name="link_via" />
							<small>e.g. @jeremyzilar</small>
						</div>
						
						<div>
							<input type="submit" value="Publish" tabindex="40" id="submit" class="btn" name="submit" />	
						</div>
						
						<input type="hidden" name="action" value="new_post" />
						<?php wp_nonce_field( 'new-post' ); ?>

					</form>

					<?php

				}

			?>
				<?php wp_footer(); ?>

				<script type="text/javascript">
				jQuery(window).load(function(){
		      jQuery('#publications').suggest("<?php echo get_bloginfo('wpurl'); ?>/wp-admin/admin-ajax.php?action=ajax-tag-search&tax=publication", {multiple:true, multipleSep: ","});
				});		        	
				</script>
        </body>

    </html>