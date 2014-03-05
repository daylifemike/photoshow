<?php
/*
Plugin Name: PhotoShow
Plugin URI: http://codecanyon.net/item/photoshow-for-wordpress/243448
Description: A image gallery plugin for WordPress. See the options page for examples and instructions.
Author: makfak
Author URI: http://www.codecanyon.net/user/makfak
Version: 1.25
*/


if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { 
	die('Illegal Entry');  
}

//============================== PhotoShow options ========================//
class photoshow_plugin_options {

	function PhotoShow_getOptions() {
		$options = get_option('photoshow_options');
		
		if (!is_array($options)) {
			$options['height'] = 235;
			$options['width'] = 350;
			$options['start_view'] = 'photo'; // photo, thumbs
			$options['captions_visible'] = true;
			$options['auto_play'] = false;
			$options['auto_play_interval'] = 5000;
			$options['show_auto_play_timer'] = true;
			$options['image_animation_duration'] = 650;
			$options['lightbox'] = true;
			$options['custom_lightbox'] = false;
			$options['custom_lightbox_name'] = 'prettyPhoto';
			$options['custom_lightbox_params'] = '{}';
			$options['random'] = false;
			$options['theme'] = 'dark';

			update_option('photoshow_options', $options);
		}
		return $options;
	}

	function update() {
		if(isset($_POST['photoshow_save'])) {
			$options = photoshow_plugin_options::PhotoShow_getOptions();

			$options['height'] = stripslashes($_POST['height']);
			$options['width'] = stripslashes($_POST['width']);
			$options['start_view'] = stripslashes($_POST['start_view']);
			$options['captions_visible'] = stripslashes($_POST['captions_visible']);
			$options['auto_play'] = stripslashes($_POST['auto_play']);
			$options['auto_play_interval'] = stripslashes($_POST['auto_play_interval']);
			$options['show_auto_play_timer'] = stripslashes($_POST['show_auto_play_timer']);
			$options['image_animation_duration'] = stripslashes($_POST['image_animation_duration']);
			$options['random'] = stripslashes($_POST['random']);
			$options['lightbox'] = stripslashes($_POST['lightbox']);
			$options['custom_lightbox'] = stripslashes($_POST['custom_lightbox']);
			$options['custom_lightbox_name'] = stripslashes($_POST['custom_lightbox_name']);
			$options['custom_lightbox_params'] = stripslashes($_POST['custom_lightbox_params']);
			$options['theme'] = stripslashes($_POST['theme']);

			update_option('photoshow_options', $options);
		} else {
			photoshow_plugin_options::PhotoShow_getOptions();
		}

		add_menu_page('PhotoShow', 'PhotoShow', 'edit_themes', basename(__FILE__), array('photoshow_plugin_options', 'display'));
	}
	
// -------------------------
// --- OPTIONS PAGE --------
// -------------------------
	function display() {
		$options = photoshow_plugin_options::PhotoShow_getOptions();
		?>
		
		<div class="wrap">
			<h2>PhotoShow</h2>
            <p>
                PhotoShow takes advantage of Wordpress' built-in gallery feature.  Simply add the <code>[photoshow]</code> shortcode to your 
                post/page content and any images attached to that post/page will be displayed as a PhotoShow gallery.
            </p>
            
			<form method="post" action="#" enctype="multipart/form-data" id="photoshow-options">				
                <h3 style="clear:both; padding-bottom:5px; margin-bottom:0; border-bottom:solid 1px #e6e6e6">Layout</h3>
                <div style="overflow:hidden;">
	                <div style="width:25%;float:left;">
                        <p>Width</p>
                        <p><input type="text" name="width" value="<?php echo($options['width']); ?>" /></p>
                        <span style="font-size:11px; color:#666666; padding:0 30px 0 3px; display:block;">in pixels</span>
                    </div>
                    <div style="width:25%;float:left;">
                        <p>Height</p>
                        <p><input type="text" name="height" value="<?php echo($options['height']); ?>" /></p>
                        <span style="font-size:11px; color:#666666; padding:0 30px 0 3px; display:block;">in pixels</span>
                    </div>
                    <div style="width:25%;float:left;">
	                    <p>Initial View</p>
	                    <p>
                            <select name="start_view">
								<option value="photo" <?php if($options['start_view'] == 'photo') echo "selected='selected'"; ?>>photo</option>
								<option value="thumbs" <?php if($options['start_view'] == 'thumbs') echo "selected='selected'"; ?>>thumbs</option>
							</select>
                        </p>
	                </div>
	                <div style="width:25%;float:left;">
	                    <p>
                            <label><input name="captions_visible" type="checkbox" value="1" <?php if($options['captions_visible']) echo "checked='checked'"; ?> /> Show Captions</label>
                        </p>
	                </div>
                </div>
                
                
                <h3 style="clear:both; padding-bottom:5px; margin-bottom:0; border-bottom:solid 1px #e6e6e6">Behavior</h3>
                <div style="overflow:hidden;">
	                <div style="width:20%;float:left;">
	                    <p>
                            <label><input name="auto_play" type="checkbox" value="1" <?php if($options['auto_play']) echo "checked='checked'"; ?> /> Autoplay</label>
                        </p>
	                </div>
                    <div style="width:20%;float:left;">
                        <p>Autoplay Interval</p>
                        <p><input type="text" name="auto_play_interval" value="<?php echo($options['auto_play_interval']); ?>" /></p>
                        <span style="font-size:11px; color:#666666; padding:0 30px 0 3px; display:block;">in miliseconds (1000 ms = 1 sec)</span>
                    </div>
	                <div style="width:20%;float:left;">
	                    <p>
                            <label><input name="show_auto_play_timer" type="checkbox" value="1" <?php if($options['show_auto_play_timer']) echo "checked='checked'"; ?> /> Show Autoplay Timer</label>
                        </p>
	                </div>
                    <div style="width:20%;float:left;">
                        <p>Image Animation Duration</p>
                        <p><input type="text" name="image_animation_duration" value="<?php echo($options['image_animation_duration']); ?>" /></p>
                        <span style="font-size:11px; color:#666666; padding:0 30px 0 3px; display:block;">in miliseconds (1000 ms = 1 sec)</span>
                    </div>
                    <div style="width:20%;float:left;">
                        <p>
                            <label><input name="random" type="checkbox" value="1" <?php if($options['random']) echo "checked='checked'"; ?> /> Randomize Photos</label>
                        </p>
                    </div>
	            </div>
                
                <h3 style="clear:both; padding-bottom:5px; margin-bottom:0; border-bottom:solid 1px #e6e6e6">Lightbox</h3>
                <div style="overflow:hidden;">
                    <div style="width:25%;float:left;">
                        <p>
                            <label><input name="lightbox" type="checkbox" value="1" <?php if($options['lightbox']) echo "checked='checked'"; ?> /> Use Default Lightbox</label>
                        </p>
                        <span style="font-size:11px; color:#666666; padding:0 30px 0 3px; display:block;">displays your photos in a prettyPhoto lightbox when clicked.</span>
                    </div>
                    <div style="width:25%;float:left;">
                        <p>
                            <label><input name="custom_lightbox" type="checkbox" value="1" <?php if($options['custom_lightbox']) echo "checked='checked'"; ?> /> Use Custom Lightbox</label>
                        </p>
                        <span style="font-size:11px; color:#666666; padding:0 30px 0 3px; display:block;">allows you to specify your own lightbox and params</span>
                    </div>
                    <div style="width:25%;float:left;">
                        <p>Custom Lightbox Name</p>
                        <p><input type="text" name="custom_lightbox_name" value="<?php echo($options['custom_lightbox_name']); ?>" /></p>
                        <span style="font-size:11px; color:#666666; padding:0 30px 0 3px; display:block;">
                            this is the name of the JS function called to activate your lightbox <br><i>(ie: prettyPhoto, fancybox, fancyZoom, facebox)</i>
                                <br><br>
                            capitalization matters
                                <br><br>
                            if you aren't familiar with JavaScript and jQuery, you may need to consult your lightbox 
                            plugin's documentation to find this function name
                        </span>
                    </div>
                    <div style="width:25%;float:left;">
                        <p>Custom Lightbox Params</p>
                        <p><textarea name="custom_lightbox_params"><?php echo($options['custom_lightbox_params']); ?></textarea></p>
                        <span style="font-size:11px; color:#666666; padding:0 30px 0 3px; display:block;">
                            this is a JS object that gets passed into your lightbox function call <br><i>(eg: {theme:'darkness'})</i>
                                <br><br>
                            if you aren't familiar with JavaScript and jQuery but have the lightbox enabled elsewhere on your site, 
                            view your page's source and look for something similar to... 
                                <br>
                            <i>$().lightboxName({ 
                                <br>&nbsp;&nbsp;&nbsp;
                                option:value, 
                                <br>&nbsp;&nbsp;&nbsp;
                                option2:value2 
                                <br>
                                });
                            </i>
                        </span>
                    </div>
                </div>

				<p style="margin-top:30px;"><input class="button-primary" type="submit" name="photoshow_save" value="Save Changes" /></p>
                <ul id="photoshow-error-list"></ul>
			</form>
            
            <div style="margin:50px 0 0 0;">
                <h3 style="clear:both; padding-bottom:5px; margin:0; border-bottom:solid 1px #e6e6e6">Inline Attributes</h3>
                <p>
                    The PhotoShow shortcode has full support for inline attributes (eg. <code>[photoshow width="600" height="400" auto_play="1"]</code>). 
                    Any inline attributes will override the default values set on this page.  See the options above for additional details on use.
                </p>
                <p>Available attributes:</p>
                <ul style="list-style:disc; margin:0 0 0 20px;">
                    <li><b>id</b> : the post/page id for the desired gallery</li>
                    <li><b>nggid</b> : the ID for the desired NextGen gallery</li>
                    <li><b>height</b> : any number <i>(in pixels)</i></li>
                    <li><b>width</b> : any number <i>(in pixels)</i></li>
                    <li><b>start_view</b> : photo, thumbs</li>
                    <li><b>captions_visible</b> : 1 = yes, 0 = no</li>
                    <li><b>auto_play</b> : 1 = yes, 0 = no</li>
                    <li><b>auto_play_interval</b> : any number <i>(in miliseconds)</i></li>
                    <li><b>show_auto_play_timer</b> : 1 = yes, 0 = no</li>
                    <li><b>random</b> : 1 = yes, 0 = no</li>
                    <li><b>image_animation_duration</b> : any number <i>(in miliseconds)</i></li>
                    <li><b>lightbox</b> : 1 = yes, 0 = no</li>
                    <li><b>custom_lightbox</b> : 1 = yes, 0 = no</li>
                    <li><b>custom_lightbox_name</b> : js function name <i>(eg: prettyPhoto)</i></li>
                    <li><b>custom_lightbox_params</b> : js object passed to the above function <i>(eg: {theme:'darkness'})</i></li>
                </ul>
            </div>

            <div style="margin:50px 0 0 0;">
                <h3 style="clear:both; padding-bottom:5px; margin:0; border-bottom:solid 1px #e6e6e6">Template Tag</h3>
                <p>
                    PhotoShow also supports Wordpress Template Tags (<code>wp_photoshow()</code>).  This can be added to your theme's
                    template files to automatically add a gallery to every page.
                </p>
                <p>The PhotoShow template tag accepts an array of the attributes listed above.  For Example:</p>
<pre><code style="display:block">   $atts = array(
        'id' => 1,
        'start_view' => 'thumbs'
    );
    wp_photoshow($atts);</code></pre>
            </div>

            <div style="margin:50px 0 0 0;">
                <h3 style="clear:both; padding-bottom:5px; margin:0; border-bottom:solid 1px #e6e6e6">Widget</h3>
                <p>
                    To use PhotoShow in your Widget-enabled sidebar simply add a standard text widget and 
                    add a <code>[photoshow]</code> shortcode to the widget's text (exactly as you would in a page or post).
                </p>
                <p>See "Inline Attributes" above for more information about customizing your widget instance.</p>
                <p>
                    <b>PLEASE NOTE:</b> 
                        <br>
                    Some templates use very strong CSS selectors to style the widget sidebar. 
                    PhotoShow's CSS selectors have been designed to 'win' against most reasonable selectors but 'lose' in some templates. 
                    If your sidebar PhotoShow instance is experiencing some layout problems you may have to modify PhotoShow's CSS 
                    to use selectors that 'win' against your theme.
                </p>
                <p>
                    If none of this makes any sense... contact me via my <a href="http://www.codecanyon.net/user/makfak">CodeCanyon author page</a> 
                    I'll do my best to help. 
                </p>
            </div>

            <div style="margin:50px 0 0 0;">
                <p>Thank you for purchasing PhotoShow for Wordpress</p>
            </div>

		</div>
		<?php
	} 
} 

function PhotoShow_getOption($option) {
    global $mytheme;
    return $mytheme->option[$option];
}

// register functions
add_action('admin_menu', array('photoshow_plugin_options', 'update'));
add_filter('widget_text', 'do_shortcode'); // Widget

$options = get_option('photoshow_options');


//============================== insert HTML header tag ========================//
$photoshow_wp_plugin_path = get_option('siteurl')."/wp-content/plugins/photoShow";
wp_register_script( 'jquery152', $photoshow_wp_plugin_path . '/jquery-1.5.2.min.js');
wp_enqueue_script('jquery152');

if (!is_admin()) {
	if($options['lightbox']) {
		wp_enqueue_style( 'prettyphoto-styles', $photoshow_wp_plugin_path . '/prettyPhoto/prettyPhoto.css');
		wp_enqueue_script( 'prettyphoto-script', $photoshow_wp_plugin_path . '/prettyPhoto/jquery.prettyPhoto.js', array('jquery152'));
	}
	wp_enqueue_style( 'photoshow-styles', $photoshow_wp_plugin_path . '/photoShow.css');
	wp_enqueue_script( 'photoshow', $photoshow_wp_plugin_path . '/jquery.photoShow.pack.js', array('jquery152'));
	
	add_shortcode( 'photoShow', 'photoshow_shortcode' );
	add_shortcode( 'photoshow', 'photoshow_shortcode' );
} else if (isset($_GET['page'])) { 
    if ($_GET['page'] == "photoShow.php") {
        wp_enqueue_script( 'photoshow-form-validation', $photoshow_wp_plugin_path . '/jquery.photoShow.wp.form.js', array('jquery152'));
    }
}

function photoshow_shortcode( $atts ) {
	global $post;
	$post_id = intval($post->ID);
	$options = get_option('photoshow_options');

	extract(shortcode_atts(array(
		'id'                       => $post_id,
		'height'                   => $options['height'],
		'width'                    => $options['width'],
		'start_view'               => $options['start_view'],
		'captions_visible'         => $options['captions_visible'],
		'auto_play'                => $options['auto_play'],
		'auto_play_interval'       => $options['auto_play_interval'],
		'show_auto_play_timer' 	   => $options['show_auto_play_timer'],
		'image_animation_duration' => $options['image_animation_duration'],
		'random'                   => $options['random'],
		'lightbox'                 => $options['lightbox'],
		'custom_lightbox'          => $options['custom_lightbox'],
		'custom_lightbox_name'     => $options['custom_lightbox_name'],
		'custom_lightbox_params'   => $options['custom_lightbox_params'],
		'include'                  => '',
		'exclude'                  => '' 
	), $atts));
	
	$unique = time() + rand(0,20);

	$output_buffer = '
		<script type="text/javascript">
			var PSalbum'.$unique.' = [{ 
		        title: "gallery-'.$unique.'",
		        caption: "",
		        thumbnail: "",
				photos: [';
	
					if ( !empty($atts['nggid']) ) {
						$output_buffer .= PSBuildJsonFromNGG($atts['nggid']);
					} else {
						$output_buffer .= PSBuildJsonFromPost($id, $include, $exclude);
					}			

				// adjustment math
				if(intval($captions_visible)){
					$captions_visible = "'always'";
				} else {
					$captions_visible = "'never'";
				}
				
				if(intval($auto_play)){
					$auto_play = "true";
				} else {
					$auto_play = "false";
				}

				if(intval($show_auto_play_timer)){
					$show_auto_play_timer = "true";
				} else {
					$show_auto_play_timer = "false";
				}

				if(intval($lightbox)){
					$lightbox = "true";
				} else {
					$lightbox = "false";
				}
				
				if(intval($custom_lightbox)){
					$custom_lightbox = "true";
					// just in case
					$lightbox = "false";
				} else {
					$custom_lightbox = "false";
				}

				if(intval($random)){
					$random = "true";
				} else {
					$random = "false";
				}
				
			$output_buffer .=']}];
			
			jq152(document).ready(function($) {
				$("#photoShowTarget'.$unique.'").photoShow({
					gallery: PSalbum'.$unique.',
					height: "'. intval($height) .'px",
					width: "'. intval($width) .'px",
					start_view: "'. $start_view .'",
					captions_visible: '. $captions_visible .',
					auto_play: '. $auto_play .',
					auto_play_interval: '. intval($auto_play_interval) .',
					show_auto_play_timer: '. $show_auto_play_timer .',
					image_animation_duration: '. intval($image_animation_duration) .',
					random: '. $random .',
					title: "",
			';
			
			if($options['lightbox'] || $options['custom_lightbox']) {
				$output_buffer .='
					modal_name: "pslightbox",
					modal_group: true,';
				
				if($options['lightbox']) {
					$output_buffer .='
						modal_ready_callback : function($photoshow){
							$("a[rel^=\'pslightbox\']", $photoshow.obj).prettyPhoto({
                    			overlay_gallery: false
                			});
						},
					';
				} elseif ($options['custom_lightbox']) {
					$output_buffer .='
						modal_ready_callback : function($photoshow){
							$("a[rel^=\'pslightbox\']", $photoshow.obj).'.$options['custom_lightbox_name'].'('.$options['custom_lightbox_params'].');
						},
					';
				}
			}
			
			$output_buffer .='
					controls : ["prev","autoplay","thumbs","next"]
				});
			});
		</script>
		<div id="photoShowTarget'.$unique.'"></div>
		<div id="PS_preloadify" class="PS_preloadify">';
			
				if ( !empty($attachments) ) {
					foreach ( $attachments as $aid => $attachment ) {
						$img = wp_get_attachment_image_src( $aid , 'full');
						$_post = & get_post($aid); 
						$image_title = attribute_escape($_post->post_title);
						$image_alttext = get_post_meta($aid, '_wp_attachment_image_alt', true);
						$image_caption = $_post->post_excerpt;
						$image_description = $_post->post_content;
						
						$output_buffer .='<img src="' . $img[0] . '"/>';
					}
				}
			
		$output_buffer .= '</div>';

	return preg_replace('/\s+/', ' ', $output_buffer);
}


function PSBuildJsonFromPost($id, $include, $exclude){
	$output_buffer = '';
	
	if ( !empty($include) ) {
		$include = preg_replace( '/[^0-9,]+/', '', $include);
		$_attachments = get_posts( array('include' => $include, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => 'asc', 'orderby' => 'menu_order') );
				
		$attachments = array();

		foreach ( $_attachments as $key => $val ) {
			$attachments[$val->ID] = $_attachments[$key];
		}
	} elseif ( !empty($exclude) ) {
		$exclude = preg_replace( '/[^0-9,]+/', '', $exclude );
		$attachments = get_children( array('post_parent' => $id, 'exclude' => $exclude, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => 'asc', 'orderby' => 'menu_order') );
	} else {
		$attachments = get_children( array('post_parent' => $id, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => 'asc', 'orderby' => 'menu_order') );
	}
	
	if ( !empty($attachments) ) {
		$i = 0;
		$len = count($attachments);
		foreach ( $attachments as $aid => $attachment ) {
			$img = wp_get_attachment_image_src( $aid , 'full');
			$_post = & get_post($aid); 
			$image_title = attribute_escape($_post->post_title);
			$image_alttext = get_post_meta($aid, '_wp_attachment_image_alt', true);
			$image_caption = $_post->post_excerpt;
			$image_description = $_post->post_content;						
			
			$output_buffer .='{
			        title: "' . $image_title . '",
			    	src: "' . $img[0] . '",
			        caption: "' . $image_caption . '"
			    }';
				
			if($i != $len - 1) {
				$output_buffer .=',';	    
			}

			$i++;
		}
	}

	return $output_buffer;
}

function PSBuildJsonFromNGG($galleryID) {
	global $wpdb, $post;
	$output_buffer ='';
	
	//Set sort order value, if not used (upgrade issue)
    $ngg_options['galSort'] = ($ngg_options['galSort']) ? $ngg_options['galSort'] : 'pid';
    $ngg_options['galSortDir'] = ($ngg_options['galSortDir'] == 'DESC') ? 'DESC' : 'ASC';
    
    // get gallery values
    $picturelist = nggdb::get_gallery($galleryID, $ngg_options['galSort'], $ngg_options['galSortDir']);

    $i = 0;
    $len = count($picturelist);
    foreach ($picturelist as $key => $picture) {
    	$output_buffer .='{
		        title: "' . $picture->alttext . '",
		    	src: "' . $picture->imageURL . '",
		        caption: "' . $picture->description . '"
		    }';

		if($i != $len - 1) {
			$output_buffer .=',';	    
		}
		
    	$i++;
    }
	return $output_buffer;
}


// Template Tag
function wp_photoshow( $atts ){
	if (!is_admin()) {
		echo photoshow_shortcode( $atts );
	}
}