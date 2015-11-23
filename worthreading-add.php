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

						// add the bookmark's meta data
        		add_post_meta($post_id, 'link_url',  $_POST['link_url'],  true);
        		add_post_meta($post_id, 'link_desc', $_POST['link_desc'], true);
        		add_post_meta($post_id, 'link_via',  $_POST['link_via'],  true);

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

					?>
					<div id="head">
						<div class="color color-dark"></div>
						<div class="color color-med"></div>
						<div class="color color-light"></div>
						<h1>Worth Reading</h1>
					</div>

					<form id="new_post" name="new_post" method="post" action="" enctype="multipart/form-data">

						<div class="bookmark-title">
							<label for="title">Title:</label>
							<input type="text" id="title" tabindex="1" name="title" value="<?php if (strlen($_GET['title']) > 0 ) { echo $_GET['title']; } ?>" />	
						</div>
						
						<div class="bookmark-url">
							<label for="link_url">URL</label>
							<input type="text" id="link_url" tabindex="2" name="link_url" value="<?php if (strlen($_GET['url']) > 0 ) { echo $_GET['url']; } ?>" />	
							<small>e.g. http://nytimes.com</small>
						</div>

						<div class="bookmark-pub">
							<label for="publications">Publication</label>
							<input type="text" id="publications" tabindex="3" name="publications" />
							<small>e.g. The New York Times</small>
						</div>

						<div class="bookmark-desc">
							<label for="link_desc">Description</label>
							<textarea type="text" id="link_desc" tabindex="4" name="link_desc" /><?php if (strlen($_GET['desc']) > 0 ) { echo $_GET['desc']; } ?></textarea>
						</div>
						
						<script>window.onload=function(){ document.getElementById('publications').focus(); }</script>
						
						<div class="bookmark-via">
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