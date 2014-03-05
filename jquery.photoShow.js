/*
    jQuery photoShow v1.7
    -- WORDPRESS VARIANT --
    requires jQuery 1.4.2+
*/

(function($) {

    if(typeof console === "undefined") {
        console = {
            log: function(msg) {
                console.errors.push(msg);
            },
            errors: []
        };
    }

    var PhotoShow = function() { };

    $.extend(PhotoShow.prototype, {

        init: function(el, options, i){
            var self = this,
                defaults = {
                    gallery : 'PSalbum', // json object, xml file path
                    input : 'json', // json, html, xml
                    title : 'default', // default, text
                    height : '235px',
                    width : '350px',
                    thumb_cols : 3,
                    thumb_rows : 3,
                    start_view : 'photo', // photo, thumbs, albums
                    start_album : 0,
                    albums_per_page : 4,
                    captions_visible : 'always', // hover, always, never
                    controls : ['prev', 'albums', 'autoplay', 'thumbs', 'next'],
                    auto_play : false,
                    auto_play_interval : 5000,
                    auto_play_type : 'infinite', // infinite, once
                    show_auto_play_timer : true,
                    image_animation_duration : 650,
                    of_text : 'of',
                    random : false,
                    modal_name : null,
                    modal_group : true,
                    modal_ready_callback : null,
                    theme: 'dark' // dark, light, custom
                };

            this.opts = $.extend({}, defaults, options);
            this.obj = $(el);
            this.is_animating = false;
            this.autoplay_interval = false;
            this.id = (Date.parse(new Date()) + Math.round(Math.random() * 10000));
            this.current_album = this.opts.start_album;
            
            // if HTML build the json obj
            if ( this.opts.input === 'html'){
                this.current_album = 0;
                this.constructJson();
            }

            // if XML build the json obj
            if ( this.opts.input === 'xml' ){
                $.get(this.opts.gallery, function(data, response){
                    if ( $(data).find('album').length > 0 ) {
                        self.opts.gallery = $(data).find('album').eq(0).parents('albums');
                        self.opts.gallery = self.transformXML();
                        self.constructinator();
                    } else {
                        console.log('PHOTOSHOW: ERROR: The XML either couldn\'t be found or was malformed.');
                        return;
                    }
                });
                
            }
            
            // error checks
            if ( this.opts.gallery === 'PSalbum' && this.opts.input ==='json' ) {
                if ( typeof(PSalbum) !== 'undefined' ) {
                    this.opts.gallery = PSalbum;
                } else {
                    console.log('PHOTOSHOW: ERROR: The JSON object "PSalbum" can not be found.');
                    return;
                }
            }
            if ( this.opts.gallery === 'PSalbum' && this.opts.input ==='xml' ) {
                console.log('PHOTOSHOW: ERROR: No XML file path specified.');
                return;
            }
            if ( this.opts.gallery.length - 1 < this.current_album ) {
                console.log('PHOTOSHOW: ERROR: "start_album" uses a 0-index (0 = the first album).'
                     + 'No album was found at the specified index ('+ this.current_album +')');
                return;
            }
            if ( (this.opts.start_view === 'thumbs' || this.opts.start_view === 'albums') && this.opts.auto_play ) {
                console.log('PHOTOSHOW: ERROR: Autoplay is disabled for the Thumbs or Albums views. Stopping Autoplay.');
                this.opts.auto_play = false;
            }
            
            if ( this.opts.input !== 'xml' ) {
                this.constructinator();
            }

        },
        
        constructinator: function(){
            // preload the first album
            this.preloadify();
            
            // shuffle if random = true
            if(this.opts.random) {
                this.opts.gallery[this.current_album].photos.sort(function(a,b) {
                    return (0.5 - Math.random());
                });
            }
            
            // build the container
            var title;
            if ( this.opts.title === 'default' ) {
                title = this.opts.gallery[this.current_album].title;
            } else {
                title = this.opts.title;
            }

            // construct all the DOM elements in memory
            this.$master = $('<div>').attr({
                'id':'photoShow',
                'class':'photoShow photoShow' + this.id + ' PS_' + this.opts.theme
            }).css({ 'width':this.opts.width });
            
            this.$title = $('<h3>').text( title );
            
            this.$container = $('<div>').attr('class','PS_container').css({
                'width':this.opts.width,
                'height':this.opts.height
            });
            
            this.$albums = $('<ul>').attr('class','PS_albums PS_closed').css({
                'width':this.opts.width,
                'height':this.opts.height,
                'bottom':'-' + this.opts.height
            });
            
            this.$images = $('<ul>').attr('class','PS_images').css({
                'width':this.opts.width,
                'height':this.opts.height
            });
            
            this.$thumbs = $('<div>').attr('class','PS_thumbs PS_closed').css({
                'width':this.opts.width,
                'height':this.opts.height,
                'bottom':'-' + this.opts.height
            });
            
            this.$controls = $('<ul>').attr('class','PS_controls');
            
            this.$timer = $('<div>').attr('class','PS_timer');
            
            this.$counter = $('<div>').attr('class','PS_counter');

            // put it the elements together
            this.$container.append(this.$images).append(this.$thumbs).append(this.$albums).append(this.$counter);
            this.$master.append(this.$title).append(this.$container).append(this.$timer).append(this.$controls);

            // build the albums
            this.$albums.append( this.constructAlbums() );
            this.albumEvents();

            // build the photos
            this.$images.append( this.constructPhotos() );
            this.captionsEvents();
            
            // build the thumbs
            this.$thumbs.append( this.constructThumbs() );
            
            // build counter
            this.$counter.append( this.constructCounter() );

            // inject it all into the DOM
            this.obj.html(this.$master);
            
            // bind events
            this.eventListener();

            this.sizeImages(this.$images);
            this.sizeThumbs(this.$thumbs);
            this.sizeAlbums();
            
            // build controls
            this.$controls.append( this.constructControls() );
            this.positionControls(this.$controls);
            
            if ( this.opts.auto_play ) {
                if (this.opts.show_auto_play_timer) {
                    this.$timer.show();
                }
                this.$controls.find('#PS_autoplay').parent().addClass('PS_on');
                this.autoPlay();
            }

            if ( this.opts.start_view === 'thumbs' ) {
                this.$thumbs.css({
                    'bottom' : '0px'
                });
                this.$controls.find('#PS_thumbs').click();
            } else if ( this.opts.start_view === 'albums' ) {
                this.$albums.css({
                    'bottom' : '0px'
                });
                this.$controls.find('#PS_albums').click();
            }

            if ( this.opts.modal_name !== null ) {
                this.bindLightbox();
            }            
        },

        eventListener: function(){
            var self = this;
            
            $(this.obj).click(function(event){
                var $target = $(event.target),
                    tag = $target.get(0).tagName.toLowerCase();

                if ( tag === 'img' ) {
                    if ( $target.parents('div').hasClass('PS_thumbs') ) {
                        var id = $target.parents('a').attr('rel');
                    
                        self.updateImages(id);
                        self.updateThumbs( $target.parents('li') );
                        self.updateCounter( self.$images.find('#' + id).parent('li') );
                        self.updateControls( self.$controls.find('#PS_thumbs').parent() );
                        setTimeout( function(){
                            self.animateThumbs();
                        }, 100);
                    }
                    return false;
                }

                if ( tag === 'a' ) {
                    if ( $target.parents('ul').hasClass('PS_controls') ) {
                        if( $target.attr('id') === 'PS_autoplay' || $target.attr('id') === 'PS_thumbs' ) {
                            self.updateControls( $target.parent() );
                        }

                        if ( $target.attr('id') === 'PS_autoplay' ) {
                            self.autoPlayToggle();
                        }
                        
                        if ( $target.attr('id') === 'PS_thumbs' ) {
                            self.animateThumbs();
                        }
                        
                        if ( $target.attr('id') === 'PS_albums' ) {
                            if($target.parent().hasClass('PS_on')) {
                                self.hideAlbums();
                            } else {
                                self.showAlbums();
                            }
                        }

                        if ( $target.attr('id') === 'PS_prev' || $target.attr('id') === 'PS_next' ) {
                            var prevnext = ( $target.attr('id') === 'PS_prev' ) ? 'prev' : 'next' ;
    
                            if ( self.$albums.hasClass( 'PS_open' ) ){
                                self.pageAlbums(prevnext);
                            } else if ( self.$thumbs.hasClass( 'PS_open' ) ){
                                self.pageThumbs(prevnext);
                            } else {
                                self.pageImages(prevnext);
                            }
                            
                            if ( self.autoplay_interval ){
                                self.autoPlayToggle(true);
                            }
                        }
                        return false;
                    }
                }
            });
        },

        captionsEvents: function(){
            if ( this.opts.captions_visible === 'never' ) {
                this.$images.children('li').children('div').hide();
                
            } else if ( this.opts.captions_visible === 'always' ) {
                this.$images.children('li').children('div').children('span').css({ 'opacity':'0.3' });
                this.$images.children('li').hover(function(){
                    $(this).children('div').children('span').stop().animate({ 'opacity':'0.7' }, 350);
                },function(){
                    $(this).children('div').children('span').stop().animate({ 'opacity':'0.3' }, 350);
                });

            } else if ( this.opts.captions_visible === 'hover' ) {
                var height = this.$images.children('li').children('div').height() * 2;
                this.$images.children('li').children('div').css({ 'bottom':'-' + height + 'px' });
                this.$images.children('li').hover(function(){
                    $(this).children('div').stop().animate({ 'bottom':'0' }, 500);
                },function(){
                    $(this).children('div').stop().animate({ 'bottom':'-' + height + 'px' }, 500);
                });                
            }
        },
        
        albumEvents: function(){
            var self = this;
            
            this.$title.hover(function(){
                $(this).addClass('hover');
            },function(){
                $(this).removeClass('hover');
            });
            
            this.$albums.find('li').hover(function(){
                $(this).addClass('hover');
            },function(){
                $(this).removeClass('hover');
            });
            
            this.$albums.find('li').click(function(){
                self.switchAlbums($(this));
                return false;
            });
        },
        
        autoPlay: function(){
            var self = this;

            this.autoplay_interval = setInterval(function() {

                if (self.opts.auto_play_type === 'once' && self.autoPlayCycleCheck()) {
                    self.updateControls( self.$controls.find('#PS_autoplay').parent() );
                    self.autoPlayToggle(true);
                    return;
                }
                
                self.pageImages('next', true);
                if (self.opts.show_auto_play_timer) {
                    self.animateTimer();
                }
            }, this.opts.auto_play_interval);
            
            if (this.opts.show_auto_play_timer) {
                this.animateTimer();
            }
        },
        
        autoPlayToggle: function(hardstop){
            if ( this.autoplay_interval || hardstop ){
                clearInterval( this.autoplay_interval );
                this.autoplay_interval = false;
                this.resetTimer();
            } else {                
                if(this.$thumbs.hasClass('PS_open')) {
                    this.animateThumbs();
                    this.updateControls( this.$controls.find('#PS_thumbs').parent() );
                }
                if(this.$albums.hasClass('PS_open')) {
                    this.hideAlbums();
                }
                this.autoPlay();
            }
        },

        autoPlayCycleCheck: function() {
            var $current = this.$images.find('li.PS_current'),
                current_pos = this.$images.children('li').index( $current[0] ) + 1,
                total_images = this.$images.children('li').length;

            if (current_pos === total_images) {
                return true;
            } else {
                return false;
            }
        },

        animateTimer: function() {
            var self = this,
                illusion_bump = 100,
                illusion_time = this.opts.auto_play_interval - this.opts.image_animation_duration - (illusion_bump * 2),
                illusion_pause = this.opts.image_animation_duration + illusion_bump;
            
            this.$timer.css({'width':'0%'}).delay(illusion_pause).animate({'width':'100%'}, illusion_time, function() {
                $(this).css({'width':'0%'});
            });
        },

        resetTimer: function() {
            this.$timer.stop().css({ 'width' : '0%' });
        },
        
        showAlbums: function() {
            if ( this.autoplay_interval ){
                this.autoPlayToggle(true);
                this.updateControls( this.$controls.find('#PS_autoplay').parent() );
            }
            this.$counter.hide();
            if(this.$thumbs.hasClass('PS_open')) {
                this.animateThumbs();
                this.updateControls( this.$controls.find('#PS_thumbs').parent() );
            }
            this.updateControls( this.$controls.find('#PS_albums').parent() );
            this.$albums.removeClass('PS_closed').addClass('PS_open').animate({
                'bottom':'0'
            }, 750);
            this.$title.text('Albums');
        },
        
        hideAlbums: function() {
            this.$counter.show();
            this.updateControls( this.$controls.find('#PS_albums').parent() );
            this.$albums.removeClass('PS_open').addClass('PS_closed').animate({
                'bottom':'-' + this.opts.height
            }, 750);
            this.$title.text(this.opts.gallery[this.current_album].title);
        },
        
        switchAlbums: function($target) {
            var self = this;
            
            this.$albums.find('li').removeClass('PS_current');
            $target.addClass('PS_current');
            this.current_album = $target.find('a').get(0).hash.replace('#album','');
            this.preloadify();
            this.$images.empty().append( self.constructPhotos(function() {
                setTimeout(function() {
                    self.hideAlbums();
                    if ( self.opts.modal_name !== null ) {
                        self.bindLightbox();
                    }
                }, 100);
            }) );
            
            this.captionsEvents();
            this.$thumbs.empty().append( self.constructThumbs() );
            this.sizeImages(this.$images);
            this.sizeThumbs(this.$thumbs);
            this.updateCounter(this.$images.find('li:first'));
            this.constructControlsData();
        },
        
        ThumbEvent: function($t){
            var id = $t.parents('a').attr('rel'),
                $target = this.$images.find('#' + id).parent('li');
            
            this.updateImages(id);
            this.updateThumbs($t);
            this.updateCounter($target);
        },
        
        animateThumbs: function(){
            var ani, old_class, new_class;
            
            if ( this.$thumbs.hasClass('PS_open') ) {
                ani = '-' + this.opts.height;
                new_class = 'PS_closed';
                old_class = 'PS_open';
                this.$counter.show();
            } else {
                ani = '0px';
                new_class = 'PS_open';
                old_class = 'PS_closed';
                this.$counter.hide();
            }
            
            if ( this.autoplay_interval ){
                this.autoPlayToggle(true);
                this.updateControls( this.$controls.find('#PS_autoplay').parent() );
            }
            
            if(this.$albums.hasClass('PS_open')) {
                this.hideAlbums();
            }
            
            this.$thumbs.removeClass( old_class ).addClass( new_class ).stop().animate({
                    'bottom' : ani
            }, 750);
        },
        
        updateImages: function(id){
            var $current = this.$images.find('.PS_current'),
                $target = this.$images.find('#' + id).parent('li');
            
            $current.removeClass('PS_current');
            $target.addClass('PS_current');
        },
        
        updateThumbs: function($target){
            var $current = this.$thumbs.find('li.PS_current');
            
            $current.removeClass('PS_current');
            $target.addClass('PS_current');
            
            if ( $target.parents('ul').css('display') === 'none' ) {
                this.$thumbs.find('ul.PS_current').removeClass('PS_current').hide();
                $target.parents('ul').addClass('PS_current').show();
            }
        },
        
        updateControls: function($target){
            if ( $target.hasClass( 'PS_on' ) ) {
                $target.removeClass( 'PS_on' ).addClass( 'PS_off' );
            } else {
                $target.removeClass( 'PS_off' ).addClass( 'PS_on' );
            }
        },
        
        updateCounter: function($current_image){
            var pos = this.$images.children('li').index( $current_image[0] ) + 1;
            this.$counter.text( pos + ' ' + this.opts.of_text + ' ' + this.opts.gallery[this.current_album].photos.length );
            this.updateControlsData(pos);
        },
        
        constructJson: function() {
            this.opts.gallery = [{
                title : '',
                caption : '',
                photos : []
            }];
            var self = this,
                $item;
            
            this.obj.find('li').each(function(i){
                $item = $(this);
                
                self.opts.gallery[self.current_album].photos[i] = {
                    title : $item.find('img').attr('alt'),
                    src : $item.find('img').attr('src'),
                    caption : $item.find('span').text()
                };
            });
        },
        
        transformXML: function() {
            var gallery = [];
            
            this.opts.gallery.find('album').each(function(i){
                var album = {},
                    data = $(this);
                
                album.title = data.children('title').text();
                album.caption = data.children('description').text();
                album.thumbnail = data.children('thumbnail').text();
                album.photos = [];
                
                data.children('photos').children().each(function(){
                    var photo = {};
                    
                    photo.title = $(this).children('title').text();
                    photo.caption = $(this).children('description').html();
                    photo.src = $(this).children('src').text();
                    
                    album.photos.push(photo);
                });  
                
                gallery.push(album);
            });
            return gallery;
        },
        
        constructPhotos: function(callback){
            var $images = $('<ul>');
            
            $.each(this.opts.gallery[this.current_album].photos, function(i) {
                $item = $('<li>');
                
                if(i === 0){ 
                    $item.attr('class','PS_current');
                }
                
                $('<a>').attr({
                        href : this.src,
                        //title : this.title,
                        id : 'PS_img_' + i,
                        title : this.title
                    })
                    .append( $('<img>') ).children().attr({
                        src : this.src,
                        alt : '' //this.title
                    })
                    .end().appendTo($item);
                
                $('<div>')
                    .append( $('<span>') )
                    .append( $('<h5>') ).children('h5').html(this.title).end()
                    .append( $('<p>') ).children('p').html(this.caption).end()
                    .appendTo($item);
                
                $images.append($item);
            });
            
            if($.isFunction(callback)){
                callback();
            }
            
            return $images.html();
        },
        
        constructThumbs: function(){
            var self = this,
                $thumbs = $('<div>'),
                thumbs_per_page = this.opts.thumb_cols * this.opts.thumb_rows,
                num_pages = Math.ceil( this.opts.gallery[this.current_album].photos.length / thumbs_per_page );

                this.thumbs_per_page = thumbs_per_page;
                this.num_pages = num_pages;
            
            $.each(this.opts.gallery[this.current_album].photos, function(i) {
                $item = $('<li>').attr('class','PS_off');
                
                if(i === 0){ 
                    $item.attr('class','PS_current');
                }
                
                $item
                    .append( $('<a>') ).children().attr({
                        href : this.src,
                        title : this.title,
                        rel : 'PS_img_' + i
                    })
                    .append( $('<img>') ).children().attr({
                        src : this.src,
                        alt : this.title
                    }).end().end().appendTo($thumbs);
            });
            
            // wrap the pages
            for(i = 0; i < num_pages; i++){
                $thumbs.children('li').slice(0,thumbs_per_page).wrapAll('<ul>');
            }
                                        
            //add col classes
            $thumbs.children('ul').each(function(){
                $(this).children('li')
                    .filter(':nth-child(' + self.opts.thumb_cols + 'n+1)').addClass('PS_first_col').end()
                    .filter(':nth-child(' + self.opts.thumb_cols + 'n)').addClass('PS_last_col').end()
                    .slice('-' + self.opts.thumb_cols).addClass('PS_last_row');                    
            });

            $thumbs.children().wrapAll('<div>');
            
            return $thumbs.html();
        },

        constructAlbums: function(){
            var self = this,
                $albums = $('<ul>'),
                $insert = $('<div>');
            
            $.each(this.opts.gallery, function(i) {
                $item = $('<li>');

                if(i === self.current_album && self.opts.start_view !== 'albums') { 
                    $item.attr('class','PS_current');
                }
                
                $item
                    .append( $('<a>') ).children().attr({
                        href : '#album' + i,
                        title : self.opts.gallery[i].title
                    })
                    .append( $('<img>') ).children().attr({
                        src : self.opts.gallery[i].thumbnail,
                        alt : self.opts.gallery[i].title
                    }); 
                
                $('<h5>').html(self.opts.gallery[i].title).appendTo($item);

                $('<p>').html(self.opts.gallery[i].caption).appendTo($item);
                
                $insert.append($item);
            });
            
            $albums.append($insert);
            
            return $albums.html();
        },
                
        sizeImages: function($images){
            var self = this,
                $lis = $images.find('li').addClass('PS_current'),
                $imgs = $images.find('img'),
                $img, diff, w, h;
                
            $.each($imgs, function(){
                $img = $(this);
                w = $img.width();
                h = $img.height();

                if ( h === 0 ) {
                    $img.css({
                        'width' : 'auto',
                        'height' : self.opts.height
                    });
                } else if ( w > h ){
                    var optsHeight = parseInt(self.opts.height, 10);
                    if(optsHeight > h) {
                        diff = optsHeight - Math.floor((parseInt(self.opts.width) * h) / w);
                    } else {
                        diff = optsHeight - h;
                    }
                    
                    $img.css({
                        'width' : self.opts.width,
                        'height' : 'auto',
                        'top' : Math.floor(diff / 2)
                    });
                } else {
                    $img.css({
                        'width' : 'auto',
                        'height' : self.opts.height 
                    });
                }
            });
            
            $lis.css({
                'width' : this.opts.width, 
                'height' : this.opts.height
            }).filter(':not(:first)').removeClass('PS_current');
        },
        
        sizeThumbs: function($thumbs){
            var $mtdiv = $thumbs.children('div'),
                cwidth = $thumbs.width() - ( parseInt($mtdiv.css('padding-left'), 10) + parseInt($mtdiv.css('padding-right'), 10) ),
                cheight = $thumbs.height() - ( parseInt($mtdiv.css('padding-top'), 10) + parseInt($mtdiv.css('padding-bottom'), 10) ),
                cols = this.opts.thumb_cols,
                rows = this.opts.thumb_rows,
                rmargin = parseInt($thumbs.find('li').css('margin-right'), 10),
                bmargin = parseInt($thumbs.find('li').css('margin-bottom'), 10),
                li_width = (cwidth - ((cols - 1) * rmargin)),
                mod_width = li_width % cols,
                li_height = (cheight - ((rows - 1) * bmargin)),
                mod_height = li_height % cols,
                final_width = Math.floor(li_width / cols),
                final_height = Math.floor(li_height / rows),
                ratio = (final_width > final_height) ? 'width' : 'height';

            $thumbs.find('li').css({
                'width' : final_width,
                'height' : final_height
            }).children('a').css({
                'width' : final_width,
                'height' : final_height
            });
            
            if(ratio === 'height'){
                $thumbs.find('img').each(function(){
                    if($(this).width() > $(this).height()){
                        $(this).css({
                            'width' : 'auto',
                            'height' : '100%',
                            'margin-left' : '-50%'
                        });
                    }
                });
            }
        
            if ( mod_width !== 0 ) {
                var new_width = final_width + mod_width;
                
                $thumbs.find('li').filter('.PS_last_col').css({
                    'width' : new_width
                }).children('a').css({
                    'width' : new_width
                });
            }
            
            $thumbs.find('ul').hide().filter(':first').addClass('PS_current').show();
            
            return;
        },
        
        sizeAlbums: function(){
            var $div = this.$albums.children('div'),
                $li = this.$albums.find('li'),
                container_height = this.$albums.height() - ( parseInt($div.css('padding-top'), 10) + parseInt($div.css('padding-bottom'), 10) ),
                bottom_margin = parseInt($li.css('margin-bottom'), 10),
                border_size = parseInt($li.css('border-bottom-width'), 10) + parseInt($li.css('border-top-width'), 10),
                lis_height = (container_height - ((this.opts.albums_per_page - 1) * bottom_margin) - (this.opts.albums_per_page * border_size)),
                final_height = Math.floor(lis_height / this.opts.albums_per_page),
                mod_height = lis_height % this.opts.albums_per_page,
                num_pages = Math.ceil( /*PSalbum.length*/ 10 / this.opts.albums_per_page );

            $li.css({
                'height':final_height + 'px'
            });
            
            // wrap the pages
            for(i = 0; i < num_pages; i++){
                var start = this.opts.albums_per_page * i,
                    end = start + this.opts.albums_per_page;
                $li.slice(start, end).wrapAll('<div>');
            }
            
            // it makes things easier to have the padding (DIV) on the page for math
            // but we don't want the 'page' DIVs to be wrapped in a DIV
            // so we remove (unwrap) the container and leave the children
            $div.children().unwrap();

            this.$albums.children('div').each(function(i){
                var $last = $(this).children().filter(':last');
                
                if(i === 0) {
                    $(this).addClass('PS_current');
                }
                
                $last.addClass('PS_last');
                
                if(mod_height !== 0){
                    $last.css({
                        'height':(final_height + mod_height) + 'px'
                    });
                }
            });
        },
        
        constructControls: function(){
            var self = this,
                $controls = $('<ul>');
            
            $.each(this.opts.controls, function(i, val){
                $item = $('<li>');
                
                if(i === 0){
                    $item.attr('class', 'PS_first');
                } else if(i === (self.opts.controls.length - 1)){
                    $item.attr('class', 'PS_last');
                }
                
                $('<a>').attr({
                        href : '#',
                        id : 'PS_' + val,
                        title : val
                    })
                    .append( $('<i>') ).children()
                    .text(val).end()
                    .appendTo($item);
                
                $controls.append($item);
            });
            this.constructControlsData();
            return $controls.html();
        },
        
        positionControls: function($controls){
            var width = $controls.width(),
                offset = Math.round((parseInt(this.opts.width, 10) - width) / 2);
            
            $controls.css({
                'left' : offset + 'px'
            });
        },
        
        constructControlsData: function(){
            this.$controls.data({
                'num_prev_images' : 0,
                'num_next_images' : this.opts.gallery[this.current_album].photos.length - 1,
                'num_prev_thumb_pages' : 0,
                'num_next_thumb_pages' : this.$thumbs.find('ul').length - 1,
                'num_prev_album_pages' : 0,
                'num_next_album_pages' : this.$albums.find('div').length - 1                       
            });
        },
        
        constructCounter: function(){
            $item = $('<span>');
            $item.text( '1 ' + this.opts.of_text + ' ' + this.opts.gallery[this.current_album].photos.length );
            return $item.html();
        },
        
        updateControlsData: function(pos, prevnext, loop, type){
            var page, current_prev, current_next;
            
            if ( pos ) {
                this.$controls.data( 'num_prev_images', pos - 1 );
                this.$controls.data( 'num_next_images', this.opts.gallery[this.current_album].photos.length - pos );
                
                if ( pos % this.thumbs_per_page === 0 ) {
                    page = Math.floor( pos / this.thumbs_per_page );
                } else {
                    page = Math.floor( pos / this.thumbs_per_page ) + 1;
                }
            }

            if ( pos % this.thumbs_per_page == 1 ) {
                prevnext = 'next';
                type = 'thumb';
            }

            if ( pos % this.thumbs_per_page == this.thumbs_per_page - 1 ) {
                prevnext = 'prev';
                type = 'thumb';
            }

            if ( prevnext ) {
                current_prev = this.$controls.data( 'num_prev_'+ type +'_pages' );
                current_next = this.$controls.data( 'num_next_'+ type +'_pages' );

                if ( prevnext === 'next' ) {
                    current_prev++;
                    current_next--;
                } else {
                    current_prev--;
                    current_next++;
                }
            }
            
            if ( loop ) {
                current_prev = this.$controls.data( 'num_next_'+ type +'_pages' );
                current_next = this.$controls.data( 'num_prev_'+ type +'_pages' );
            }
            
            this.$controls.data( 'num_prev_'+ type +'_pages', current_prev );
            this.$controls.data( 'num_next_'+ type +'_pages', current_next );
        },
        
        pageImages: function(prevnext, auto){
            var $current = this.$images.find('li.PS_current'),
                $target,
                id,
                $t,
                current_pos = this.$images.children('li').index( $current[0] ) + 1,
                total_images = this.$images.children('li').length;
            
            if ( prevnext === 'next' ) {
                if ( current_pos !== total_images) {
                    $target = $current.next();
                } else {
                    $target = this.$images.find('li:first');
                }
            } else {
                if ( current_pos !== 1) {
                    $target = $current.prev();
                } else {
                    $target = this.$images.find('li:last');
                }
            }           
            
            id = $target.children('a').attr('id');
            $t = this.$thumbs.find('a[rel="'+id+'"]').parents('li');
            
            this.swapImage($current, $target, prevnext);
            this.updateCounter($target);
            this.updateThumbs($t);
            
            if ( this.autoplay_interval && !auto ) {
                this.autoPlayToggle(true);
                this.updateControls( this.$controls.find('#PS_autoplay').parent() );
            }
        },
        
        swapImage: function($current, $next, prevnext){
            var direction = ( prevnext === 'next' ) ? '-' : '+',
                absolute = ( prevnext === 'next' ) ? '+' + this.opts.width : '-' + this.opts.width,
                $merged = $.makeArray($current).concat( $.makeArray($next) );
            
            $next.addClass('PS_current').css({ 'left' : absolute });
            
            $current.add($next).animate( { 'left':direction + '=' + this.opts.width } , this.opts.image_animation_duration, function(){
                $current.removeClass().css({ 'left':'0' });
            });
        },
        
        pageThumbs: function(prevnext){
            var $current_ul = this.$thumbs.find( 'ul.PS_current' ),
                next_ul,
                direction,
                loop;
            
            if ( prevnext === 'next' ) {
                if ( this.$controls.data( 'num_next_thumb_pages' ) > 0 ) {
                    $next_ul = $current_ul.next();
                    direction = 'next';
                    loop = null;
                } else {
                    $next_ul = this.$thumbs.find( 'ul:first' );
                    direction = null;
                    loop = true;
                }
            } else {
                if ( this.$controls.data( 'num_prev_thumb_pages' ) > 0 ) {
                    $next_ul = $current_ul.prev();
                    direction = 'prev';
                    loop = null;
                } else {
                    $next_ul = this.$thumbs.find( 'ul:last' );
                    direction = null;
                    loop = true;
                }
            }
            
            this.hideThumbsPage($current_ul, $next_ul);
            this.updateControlsData(null, direction, loop, 'thumb');
        },
        
        pageAlbums: function(prevnext){
            var $current_div = this.$albums.find( 'div.PS_current' ),
                next_div,
                direction,
                loop;
            
            if ( prevnext === 'next' ) {
                if ( this.$controls.data( 'num_next_album_pages' ) > 0 ) {
                    $next_div = $current_div.next();
                    direction = 'next';
                    loop = null;
                } else {
                    $next_div = this.$albums.find( 'div:first' );
                    direction = null;
                    loop = true;
                }
            } else {
                if ( this.$controls.data( 'num_prev_album_pages' ) > 0 ) {
                    $next_div = $current_div.prev();
                    direction = 'prev';
                    loop = null;
                } else {
                    $next_div = this.$albums.find( 'div:last' );
                    direction = null;
                    loop = true;
                }
            }
            
            this.hideAlbumsPage($current_div, $next_div);
            this.updateControlsData(null, direction, loop, 'album');
        },
        
        hideAlbumsPage: function($current_div, $next_div){
            var self = this,
                timer = 0, 
                time = 35,
                multiplier = 1,
                $current_lis = $current_div.children( 'li' ),
                list_length = $current_lis.length;

            $current_lis.each(function( i ){
                $(this).delay(timer).animate( { 'opacity':'0' }, 500, function(){
                    if ( i + 1 === list_length ) {
                        $current_div.hide().removeClass()
                            .children( 'li' ).css( { 'opacity':'1' } );
                        self.showAlbumsPage($next_div);
                    }
                });
                timer = (timer * multiplier + time);
            });
        },
        
        showAlbumsPage: function($div){
            var timer = 0, 
                time = 35,
                multiplier = 1,
                $lis = $div.children( 'li' );
            
            $lis.css( { 'opacity':'0' } )
                .parent( 'div' ).show()
                .end().each(function(i){
                    $(this).animate( { 'letter-spacing':'0' }, timer, function(){
                        $(this).animate( { 'opacity':'1' }, 500, function(){
                            if ( i + 1 === $lis.length ) {
                                $div.addClass( 'PS_current' );
                            }
                        });                              
                    });
                    timer = (timer * multiplier + time);
            });
        },
        
        hideThumbsPage: function($current_ul, $next_ul){
            var self = this,
                timer = 0, 
                time = 35,
                multiplier = 1,
                $current_lis = $current_ul.children( 'li' ),
                list_length = $current_lis.length;

            $current_lis.each(function( i ){
                $(this).delay(timer).animate( { 'opacity':'0' }, 500, function(){
                    if ( i + 1 === list_length ) {
                        $current_ul.hide().removeClass()
                            .children( 'li' ).css( { 'opacity':'1' } );
                        self.showThumbsPage($next_ul);
                    }
                });
                timer = (timer * multiplier + time);
            });
        },
        
        showThumbsPage: function($ul){
            var timer = 0, 
                time = 35,
                multiplier = 1,
                $lis = $ul.children( 'li' );
            
            $lis.css( { 'opacity':'0' } )
                .parent( 'ul' ).show()
                .end().each(function(i){
                    $(this).animate( { 'letter-spacing':'0' }, timer, function(){
                        $(this).animate( { 'opacity':'1' }, 500, function(){
                            if ( i + 1 === $lis.length ) {
                                $ul.addClass( 'PS_current' );
                            }
                        });                              
                    });
                    timer = (timer * multiplier + time);
            });
        },
        
        bindLightbox: function(){
            var self = this,
                rel_text = ( this.opts.modal_group ) ? '[' + this.opts.gallery[this.current_album].title.replace(/\s/g, '') + ']' : '';

            this.$images.find( 'li' ).each(function(){
                $(this).children( 'a' ).addClass( self.opts.modal_name ).attr({
                    'rel' : self.opts.modal_name + rel_text
                });
            });
            
            if($.isFunction(this.opts.modal_ready_callback)){
                this.opts.modal_ready_callback.apply(this, [self]);
            }           
        },
        
        preloadify: function(){
            var self = this,
                $images = $('<div>').attr('id','PS_preloadify');

            $.each(self.opts.gallery[self.current_album].photos, function() {
                $item = $('<img>').attr({src : this.src});
                $images.append($item);
            });
            
            $('body').append($images);
        }
        
    });

    $.fn.photoShow = function(options) {
        this.each(function(i) {
            if (!this.photoShow) {
                this.photoShow = new PhotoShow();
                this.photoShow.init(this, options, i);
            }
        });
        return this;
    };
})(jq152);