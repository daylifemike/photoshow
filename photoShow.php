<?php
/*
    Plugin Name: PhotoShow
    Plugin URI: http://codecanyon.net/item/photoshow-for-wordpress/243448
    Description: A image gallery plugin for WordPress. See the options page for examples and instructions.
    Author: makfak
    Author URI: http://www.codecanyon.net/user/makfak
    Version: 1.3
*/


if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { 
    die('Illegal Entry');  
}

add_action('init', array('PhotoShow', 'init'));

class PhotoShow {
    public static function version () {
        return '1.3';
    }

    public static function init () {
        global $pagenow;

        $options = get_option('photoshow_options');

        add_filter( 'widget_text', 'do_shortcode' ); // Widget
        add_filter( 'post_gallery', array( __CLASS__, 'post_gallery' ), 1337, 2 ); // [gallery photoshow="true" theme="photoshow"]
        add_filter( 'plugin_action_links', array( __CLASS__, 'plugin_action_links' ), 10, 2);

        add_action( 'admin_menu', array( __CLASS__, 'setup_admin_page') );

        wp_register_script( 'photoshow_js', plugins_url('/js/photoshow.js', __FILE__ ), array('jquery'));
        wp_enqueue_style( 'photoshow_css', plugins_url('/css/photoshow.css', __FILE__ ));

        if (!is_admin()) {
            if($options['lightbox']) {
                wp_enqueue_script( 'photoshow_prettyphoto_js', plugins_url('/includes/prettyPhoto/jquery.prettyPhoto.js', __FILE__ ), array('jquery'));
                wp_enqueue_style( 'photoshow_prettyphoto_css', plugins_url('/includes/prettyPhoto/prettyPhoto.css', __FILE__ ));
            }

            add_shortcode( 'photoShow', array( __CLASS__, 'shortcode' ) );
            add_shortcode( 'photoshow', array( __CLASS__, 'shortcode' ) );

        } else {
            if ( isset($_GET['page']) ) {
                if ( $_GET['page'] == "photoShow.php" || $_GET['page'] == "photoshow" ) {
                    wp_enqueue_script( 'photoshow_admin_js', plugins_url('/js/photoshow.admin.js', __FILE__), array('jquery'));
                }
            }

            if ( isset( $_GET['post'] ) || in_array( $pagenow, array( 'post-new.php' ) ) ) {
                wp_enqueue_script( 'photoshow_editor_js', plugins_url('/js/photoshow.editor.js', __FILE__ ), array('jquery'));
            }

            // wp_enqueue_style( 'photoshow_menu_css', plugins_url('/css/photoshow.menu.css', __FILE__ ));
        }

        wp_enqueue_script('photoshow_js');
    }

    public static function get_options () {
        $defaults = array(
            'height' => 235,
            'width' => 350,
            'start_view' => 'photo', // photo, thumbs
            'captions_visible' => true,
            'auto_play' => false,
            'auto_play_interval' => 5000,
            'show_auto_play_timer' => true,
            'image_animation_duration' => 650,
            'lightbox' => true,
            'custom_lightbox' => false,
            'custom_lightbox_name' => 'prettyPhoto',
            'custom_lightbox_params' => '{}',
            'random' => false,
            'theme' => 'dark'
        );

        $options = get_option('photoshow_options');

        if (!is_array($options)) {
            $options = $defaults;
            update_option('photoshow_options', $options);
        } else {
            $options = $options + $defaults; // "+" means dup keys aren't overwritten
        }

        return $options;
    }

    public static function post_gallery ($empty = '', $atts = array()) {
        global $post;

        $isPhotoShow = false;

        if ( isset($atts['photoshow']) ) {
            if ( $atts['photoshow'] === 'true' ) {
                $isPhotoShow = true;
            }
        } else if ( isset($atts['theme']) ) {
            if ( $atts['theme'] === 'photoshow' ) {
                $isPhotoShow = true;
            }
        }

        if ( !$isPhotoShow ) {
            return $empty;
        } else {
            $output = PhotoShow::shortcode($atts);
            return $output;
        }
    }

    public static function plugin_action_links ($links, $file) {
        // http://wp.smashingmagazine.com/2011/03/08/ten-things-every-wordpress-plugin-developer-should-know/
        static $this_plugin;

        if (!$this_plugin) {
            $this_plugin = plugin_basename(__FILE__);
        }

        if ($file == $this_plugin) {
            $settings_link = '<a href="' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=photoshow">Settings</a>';
            array_unshift($links, $settings_link);
        }

        return $links;
    }

    public static function setup_admin_page () {
        if(isset($_POST['photoshow_save'])) {
            $options = PhotoShow::get_options();

            foreach ($options as $k => $v) {
                if ( !array_key_exists($k, $_POST) ) {
                    if (intval($options[$k]) || empty($options[$k])) {
                        $_POST[$k] = 0;
                    } else {
                        $_POST[$k] = $options[$k];
                    }
                }
                if (is_string($_POST[$k])) {
                    $options[$k] = trim( stripslashes( $_POST[$k] ) );
                } else {
                    $options[$k] = $_POST[$k];
                }
            }

            update_option('photoshow_options', $options);

            $_POST['message'] = "Settings Updated";
        }

        add_menu_page(
            'PhotoShow v' . PhotoShow::version(),
            'PhotoShow',
            'update_plugins',
            'photoshow', // basename(__FILE__) == 'photoShow.php'
            array( __CLASS__, 'render_admin_page' ),
            'div'
        );
    }

    public static function render_admin_page () {
        include('includes/photoshow.admin.php');
    }

    public static function gallery_from_wordpress ($id, $include, $exclude, $ids, $return_img_obj = false) {
        global $wp_version;

        $output_buffer = '';
        $common_params = array(
            'post_status' => 'inherit',
            'post_type' => 'attachment',
            'post_mime_type' => 'image',
            'order' => 'asc',
            'orderby' => 'menu_order'
        );
        $_attachments = array();
        $attachments = array();

        // IDs are an explicit list -- ignore all the other things
        if ( !empty($ids) ) {
            $params = array_merge($common_params, array(
                'include' => preg_replace( '/[^0-9,]+/', '', $ids ),
                'orderby' => 'post__in'
            ));
            $_attachments = get_posts( array_merge($common_params, $params) );

            foreach ( $_attachments as $key => $val ) {
                $attachments[$val->ID] = $_attachments[$key];
            }
        // we want all the children (+) any includes (-) any excludes
        } else {
            $params = array_merge($common_params, array(
                'post_parent' => $id
            ));

            $attachments = get_children( array_merge($params, $common_params) );

            if ( !empty($include) ) {
                $params = array_merge($common_params, array(
                    'include' => preg_replace( '/[^0-9,]+/', '', $include )
                ));

                $_attachments = get_posts( array_merge($params, $common_params) );

                foreach ( $_attachments as $key => $val ) {
                    $attachments[$val->ID] = $_attachments[$key];
                }
            }

            if ( !empty($exclude) ) {
                $exclude = preg_replace( '/[^0-9,]+/', '', $exclude );
                $exclude = explode(",", $exclude);
                foreach ( $attachments as $_a ) {
                    if ( in_array($_a->ID, $exclude) ) {
                        unset($attachments[$_a->ID]);
                    }
                }
            }
        }

        if ( $return_img_obj ) {
            return $attachments;
        }

        if ( !empty($attachments) ) {
            $i = 0;
            $len = count($attachments);

            foreach ( $attachments as $_post ) {
                $image_full = wp_get_attachment_image_src($_post->ID , 'full');
                // $image_large = wp_get_attachment_image_src($_post->ID , 'large');
                // $image_medium = wp_get_attachment_image_src($_post->ID , 'medium');
                // $image_thumbnail = wp_get_attachment_image_src($_post->ID , 'thumbnail');
                $image_title = esc_attr($_post->post_title);
                // $image_alttext = esc_attr(get_post_meta($_post->ID, '_wp_attachment_image_alt', true));
                $image_caption = esc_attr($_post->post_excerpt);
                // $image_description = $_post->post_content; // this is where we hide a link_url
                // $image_attachment_page = get_attachment_link($_post->ID); // url for attachment page

                $output_buffer .='{
                    src: "' . $image_full[0] . '",
                    title: "' . $image_title . '",
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

    public static function gallery_from_nextgen ($id, $type) {
        global $wpdb, $post;
        $picturelist = array();
        $output_buffer ='';

        if ( $type === 'gallery' ) {
            $picturelist = array_merge( $picturelist, nggdb::get_gallery($id) );
        } else {
            $album = nggdb::find_album( $id );
            $galleryIDs = $album->gallery_ids;
            foreach ($galleryIDs as $key => $galleryID) {
                $picturelist = array_merge( $picturelist, nggdb::get_gallery($galleryID) );
            }
        }

        $i = 0;
        $len = count($picturelist);

        foreach ($picturelist as $key => $picture) {
            $image_description = $picture->description;
            $image_alttext = $picture->alttext;

            $output_buffer .='{
                src: "' . esc_attr($picture->imageURL) . '",
                caption: "' . esc_attr($image_description) . '",
                title: "' . esc_attr($image_alttext) . '"
            }';

            if($i != $len - 1) {
                $output_buffer .=',';
            }

            $i++;
        }
        return $output_buffer;
    }

    public static function shortcode( $atts ) {
        global $post;
        $post_id = intval($post->ID);
        $base = array(
            'id'        => $post_id,
            'include'   => '',
            'exclude'   => '',
            'ids'       => ''
        );
        $options = PhotoShow::get_options();
        $options = wp_parse_args($base, $options);
        $settings = wp_parse_args($atts, $options);
        
        $unique = floor(((time() + rand(21,40)) * rand(1,5)) / rand(1,5));

        if ( !empty($atts['nggid']) ) {
            $gallery = PhotoShow::gallery_from_nextgen($atts['nggid'], 'gallery');
        } else if ( !empty($atts['ngaid']) ) {
            $gallery = PhotoShow::gallery_from_nextgen($atts['ngaid'], 'album');
        } else {
            $gallery = PhotoShow::gallery_from_wordpress($settings['id'], $settings['include'], $settings['exclude'], $settings['ids']);
        }

        $bool_settings = array(
            'auto_play', 'show_auto_play_timer', 'lightbox', 'custom_lightbox', 'random'
        );

        foreach ( $bool_settings as $key ) {
            if ( array_key_exists($key, $settings) ) {
                if(intval($settings[$key])){
                    $settings[$key] = "true";
                } else {
                    $settings[$key] = "false";
                }
            }
        }

        if(intval($settings['captions_visible'])){
            $settings['captions_visible'] = "'always'";
        } else {
            $settings['captions_visible'] = "'never'";
        }

        $output_buffer = '
            <!-- PhotoShow v'. PhotoShow::version() .' -->
            <script type="text/javascript">
                var PSalbum'. $unique .' = [{ 
                    title: "gallery-'. $unique .'",
                    caption: "",
                    thumbnail: "",
                    photos: ['. $gallery .']}];
                
                jQuery(document).ready(function($) {
                    $("#photoShowTarget'. $unique .'").photoShow({
                        gallery: PSalbum'. $unique .',
                        height: "'. intval($settings['height']) .'px",
                        width: "'. intval($settings['width']) .'px",
                        start_view: "'. $settings['start_view'] .'",
                        captions_visible: '. $settings['captions_visible'] .',
                        auto_play: '. $settings['auto_play'] .',
                        auto_play_interval: '. intval($settings['auto_play_interval']) .',
                        show_auto_play_timer: '. $settings['show_auto_play_timer'] .',
                        image_animation_duration: '. intval($settings['image_animation_duration']) .',
                        random: '. $settings['random'] .',
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
                                    overlay_gallery: false,
                                    slideshow: false,
                                    theme: "pp_default",
                                    deeplinking: false,
                                    show_title: false,
                                    social_tools: ""
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
            <div id="photoShowTarget'. $unique .'"></div>
            <div id="PS_preloadify" class="PS_preloadify">';
                
                    // if ( !empty($attachments) ) {
                    //     foreach ( $attachments as $aid => $attachment ) {
                    //         $img = wp_get_attachment_image_src( $aid , 'full');
                    //         $_post = & get_post($aid); 
                    //         $image_title = attribute_escape($_post->post_title);
                    //         $image_alttext = get_post_meta($aid, '_wp_attachment_image_alt', true);
                    //         $image_caption = $_post->post_excerpt;
                    //         $image_description = $_post->post_content;
                            
                    //         $output_buffer .='<img src="' . $img[0] . '"/>';
                    //     }
                    // }
                
            $output_buffer .= '</div>';

        return preg_replace('/\s+/', ' ', $output_buffer);
    }
}

// Template Tag
function wp_photoshow( $atts ){
    if (!is_admin()) {
        echo photoshow_shortcode( $atts );
    }
}