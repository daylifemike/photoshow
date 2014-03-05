<?php
    $options = PhotoShow::get_options();
?>
<div class="wrap photoshow">
    <h1>PhotoShow v<?php echo PhotoShow::version(); ?></h1>
    <p>PhotoShow takes advantage of Wordpress' built-in [gallery] shortcode.</p>
    <p>Simply add the <code>[photoshow]</code> shortcode to your post/page content and any images attached to that post/page will be displayed as a PhotoShow gallery.</p>
    <p>When using WP's "Create Gallery" flow, on the "Edit Gallery" screen, simply set the "theme" to "PhotoShow" to transform your [gallery] into a PhotoShow.</p>
    
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
            <li><b>ids</b> : the list of attachment IDs to be used in the gallery</li>
            <li><b>nggid</b> : the ID for the desired NextGen gallery</li>
            <li><b>ngaid</b> : the ID for the desired NextGen album</li>
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