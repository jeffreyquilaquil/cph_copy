/*
 * Author: Ramazan APAYDIN
 * Website: http://ramazanapaydin.com
 * Versiyon: 1.0
*/

// Twitter Settings
// twitterProfil         = true          => Show Twitter Profile Header
// twitterUsername       = "apaydin541"  => Twitter Username
// twitterPer_Page       = 5             => Show item count
// twitterHeaderLimit    = 50            => Clipping character
// twitterConsumerKey       = 50         => Twitter Application Consumer Key
// twitterConsumerSecret    = 50         => Twitter Application Consumer Secret Key
// twitterAccessToken       = 50         => Twitter Application Access Token Key
// twitterAccessTokenSecret = 50         => Twitter Application Access Token Scret Key
// ======================================= 
var twitterActive        = true;
var twitterProfil        = true;
var twitterUsername      = "apaydin541";
var twitterConsumerKey   = "pPgY3atohdiOe29Ix1PQOAWCc";
var twitterConsumerSecret= "NWxMhL4XI2XVnT9W9PcRVfTMHklN3m7cejZhSBTo9ObqwrNsZo";
var twitterAccessToken   = "80856878-9z0t3AkqF6IruKfcT0r7SMumOgIW7do4nDtPAzsWn";
var twitterAccessTokenSec= "tm1mbKixCbVR4NJ5X5sioJ5ejED5wAaCOQGPX2dJ1uogp";
var twitterPer_Page      = 5;
var twitterHeaderLimit   = 50;

// Facebook Settings 
// facebookProfil        = true             => Show Facebook Profile Header
// facebookPageName      = "facebook"       => Facebook Page Name
// facebookPer_Page      = 7                => Show item count
// facebookTrim          = 140              => Clipping character
// facebookAPPID         = "1231231212312"  => Facebook Application ID
// facebookAPPSecret     = "21J12H1K2J3..." => Facebook Application Password
// Create New Application Facebook          => https://developers.facebook.com/apps
// ====================================== 
var facebookActive       = true;
var facebookProfil       = true;
//var facebookPageName     = "10204592926296034";
var facebookPageName     = "facebook";
var facebookPer_Page     = 6;
var facebookTrim         = 140;
var facebookAPPID        = "298052347057882";
var facebookAPPSecret    = "d69a463dd691e39e7d10b6af31e945bf";

// Dribbble Settings 
// dribbbleProfil        = true         => Show Dribbble Profile Header
// dribbbleGallery       = true         => Dribble Gallery Mode On
// dribbbleUsername      = "dribbble"   => Drible Username
// dribbblePer_Page      = 5            => Show item count
// ======================================= 
var dribbbleActive       = true;
var dribbbleProfil       = true;
var dribbbleGallery      = false;
var dribbbleUsername     = "dribbble";
var dribbblePer_Page     = 5;

// Flickr Settings 
// flickrProfil          = true              => Show Flicker Profile Header
// flickrUsername        = "flickr"          => Flickr Username
// flickrPer_Page        = 5                 => Show item count
// flickrAPI_KEY         = "fc6c52ed4f45..." => Flickr Application Key
// Create New Application Flickr             => https://www.flickr.com/services/apps/create/apply/ 
// ========================================= 
var flickrActive         = true;
var flickrProfil         = true;
var flickrUsername       = "flickr";
var flickrPer_Page       = 5;
var flickrAPI_KEY        = "fc6c522ed4f4582bd9ee52069212a8620e4626";

// Pinteret Settings 
// pinterestProfil          = true                        => Show Pinterest Profile Header
// pinterestUsername        = "pinterest"                 => Pinterest Username
// pinterestPer_Page        = 10                          => Show item count
// pinterestDescLimit       = 80                          => Clipping character
// pinterestProfileName     = "Ramazan Apaydın"           => Profile Header Name
// pinterestProfileDesc     = "Web Developer"             => Profile Header Description
// pinterestProfileWebS     = "http://ramazanapaydin.com" => Profile Header Website Url
// ======================================= 
var pinterestActive      = true;
var pinterestProfil      = true;
var pinterestUsername    = "pinterest";
var pinterestPer_Page    = 10;
var pinterestDescLimit   = 80;
var pinterestProfileName = "Ludivina Marinas";
var pinterestProfileDesc = "Web Developer";
var pinterestProfileWebS = "http://ramazanapaydin.com";

// Tumblr Settings 
// tumblrProfil          = true                     => Show Tumblr Profile Header
// tumblrUsername        = "apaydin541.tumbr.com"   => Tumblr Username
// tumblrPer_Page        = 5                        => Show item count
// tumblrTrim            = 100                      => Clipping character
// tumblrTitleTrim       = 60                       => Clipping character
// tumblrAPI_KEY         = "2RZ2teQPzuI7Nst0Rt..."  => Tumblr OAuth Consumer Key
// Create New Application Tumblr                    => http://www.tumblr.com/oauth/apps
// ========================================= 
var tumblrActive         = true;
var tumblrProfil         = true;
var tumblrUsername       = "apaydin541.tumblr.com";
var tumblrPer_Page       = 5;
var tumblrTrim           = 100;
var tumblrTitleTrim      = 60;
var tumblrAPI_KEY        = "2R22teQPzuI72Nst0R2Y95CmvA2KYcV012KzR2r1lv2q1AjaFJz6";    //http://www.tumblr.com/oauth/apps

// Youtube Settings 
// youtubeProfil          = true                    => Show Youtube Profile Header
// youtubeChannelName     = "muyap"                 => Youtube Channel Name (Doesn't work user name)
// youtubePer_Page        = 5                       => Show item count
// youtubeContentLimit    = 100                     => Clipping character
// youtubeTitleLimit      = 100                     => Clipping character
// ========================================= 
var youtubeActive        = true;
var youtubeProfil        = true;
var youtubeChannelName   = "muyap";
var youtubePer_Page      = 5;
var youtubeContentLimit  = 100;
var youtubeTitleLimit    = 100;

// Vimeo Settings 
// vimeoProfil          = true                     => Show Vimeo Profile Header
// vimeoChannelName     = "whitehouse"             => Vimeo Channel Name (Doesn't work user name)
// vimeoPer_Page        = 5                        => Show item count
// vimeoHeaderLimitDesc = 80                       => Clipping character
// vimeoTitleLimit      = 60                       => Clipping character
// vimeoConsumerKey     = "c242d9caadd6188fd..."   => Vimeo Client ID (Consumer Key)
// vimeoConsumerSecret  = "1bc34ade250f2e863d..."  => Vimeo Client Secret (Consumer Secret)
// Create New Application Vimeo                    => https://developer.vimeo.com/apps/new
// =========================================== 
var vimeoActive          = true;
var vimeoProfil          = true;
var vimeoConsumerKey     = "3c242d9aad3d618fd3b2de34e6fb8f320cea6cf3";
var vimeoConsumerSecret  = "1bc34dee20f2e86de145a0d3c2w8f9brd9515fe5";
var vimeoChannelName     = "whitehouse";
var vimeoPer_Page        = 5;
var vimeoHeaderLimitDesc = 80;
var vimeoTitleLimit      = 60;

// Behance Settings 
// behanceProfil         = true                     => Show Behance Profile Header
// behanceUsername       = "apaydin541"             => Behance Username
// behancePer_Page       = 5                        => Show item count
// behanceTitleLimit     = 100                      => Clipping character
// behanceAPP_ID         = "1bc34ade250f2e863d..."  => Behance Client Secret (Consumer Secret)
// Create New Application Behance                   => http://www.behance.net/dev/register
// ========================================= 
var behanceActive        = true;
var behanceProfil        = true;
var behanceAPP_ID        = "BT309NaqLnHlRdexQyEwDAwqWVmwFC6";
var behanceUsername      = "apaydin541";
var behancePer_Page      = 5;
var behanceTitleLimit    = 100;

// RSS Settings 
// rssHeader            = true                           => Show RSS Profile Header
// rssDate              = true                           => Show RSS Date
// rssUrl               = http://ramazanapaydin.com/feed => RSS Feed Url
// rssCount             = 5                              => Show item count
// rssLimit             = 100                            => Clipping character
// ============================================= 
var rssActive           = true;
var rssHeader           = true;
var rssDate             = true;
var rssUrl              = "http://ramazanapaydin.com/feed";
var rssCount            = 5;
var rssLimit            = 100;

// General Settings 
// scrollbarHeight      = 400            => Scrollbar Height Value
// infinityScrollOffset = 400            => Infinity scroll bottom value
// mainWidth            = 360 | auto     => Main Width Value
// verticalNavigation   = true           => Show Veritacal Navigation
// infinityScroll       = true           => Activate Infinity Scroll (Facebook Scrolling)
// activeTab            = "twitter"      => First Active Tab is Twitter
// autoHideScrollbar    = true           => Scroll bar auto Show/Hide
// Active Tab Option    =  facebook - dribbble - flickr - pinterest - tumblr -  youtube - vimeo - behance -  rss
// ========================================
var scrollbarHeight     = 400;
var infinityScrollOffset= 200;
var mainWidth           = "360";
var activeTab           = "twitter";
var verticalNavigation  = true;
var infinityScroll      = true;
var autoHideScrollbar   = true;

// Append Content 
// ======================================================== 
var twitterContentBase          = "<div class='item itemtwitter'><div class='pleft'><img class='avatar' src='%IMG%'></div><div class='pright'><p>%CNT%</p><div class='tweetbtn'><img src='img/retweet_mini.png' width='13' height='13' alt='Favorite'> <a href='http://twitter.com/intent/retweet?tweet_id=%RETWT%'>Retweet</a> <img src='img/reply_mini.png' width='12' height='12' alt='Favorite'> <a href='http://twitter.com/intent/tweet?in_reply_to=%REPLY%'>Reply</a> <img src='img/favorite_mini.png' width='12' height='12' alt='Favorite'> <a href='http://twitter.com/intent/favorite?tweet_id=%FAVOR%'>Favorite</a> <div class='time'>%LNKTL%</div></div></div>";
var twitterHeaderBase           = "<div class='item itemheader'><div class='pleft'><img class='header_avatar' src='%IMGSRC%' title='avatar'/></div><div class='pright'><div class='title'><a target='_blank' href='%LINK%'>%NAME%</a></div><div>%ABOUT%</div><span>%TWEET% Tweet</span> | <span>%FOLLOWING% Following</span> | <span>%FOLLOWER% Followers</span></div></div>";

var facebookContentBase         = "<div class='item itemfacebook'><div class='pleft'><img class='avatar' src='%IMGSRC%'/></div><div class='pright'><div class='title'><a href='%TLINK%' target='_blank'>%TITLE%</a></div><p>%CNT%</p><div><img class='avatar' src='img/like.png' alt='Likes'> <span>%LIKE% Likes</span>&nbsp; <div class='time'>%TIME%</div></div></div>";
var facebookHeaderBase          = "<div class='item itemheader'><div class='pleft'><img class='header_avatar' src='%IMGSRC%' title='avatar'/></div><div class='pright'><div class='title'><a target='_blank' href='%LINK%'>%NAME%</a></div><div>%ABOUT%</div><span>%LIKE% Likes</span> | <div class='time'>%FOUNDED%</div></div></div>";

var dribbbleContentBase         = "<div class='item itemdribbble'><div class='pleft'><a class='fancybox-thumbs' href='%LINK%' data-fancybox-group='thumbdribbble' title='%TITLE%' target='_blank'><img class='avatar' src='%IMGSRC%'></a></div><div class='pright'><div class='title'><a href='%URL%' target='_blank'>%TITLE%</a></div><img src='img/icon-comments.png'> <span>%COMMENT% Comment</span>&nbsp; <img src='img/icon-shot-like.png'> <span>%LIKE% Like</span>&nbsp; <img src='img/icon-views.png'> <span>%VIEWS% Views</span>&nbsp; <div class='time'>%TIME%</div></div>";
var dribbbleContentBaseGallery  = "<li><a class='fancybox-thumbs' href='%LINK%' data-fancybox-group='thumbdribbble' title='%TITLE%' target='_blank'><img class='gallery' src='%IMGSRC%'></a></li>";
var dribbbleHeaderBase          = "<div class='item itemheader'><div class='pleft'><img class='header_avatar' src='%IMGSRC%' title='avatar'/></div><div class='pright'><div class='title'><a target='_blank' href='%LINK%'>%NAME%</a></div><div>%LOCATION%</div><span>%FOLLOW% Followers</span> | <span>%SHOUT% Shouts</span> | <span>%LIKE% Likes</span></div></div>";

var flickrContentBase           = "<li><a class='fancybox-thumbs' href='%LINK%' data-fancybox-group='thumbflickr' target='_blank'><img class='gallery' src='%IMGSRC%'></a></li>";
var flickrHeaderBase            = "<div class='item itemheader'><div class='pleft'><img class='header_avatar' src='%IMGSRC%' title='avatar'/></div><div class='pright'><div class='title'><a target='_blank' href='%LINK%'>%NAME%</a></div><div>%ABOUT%</div><span>%IMAGES% Images</span> | <div class='time'>%TIME%</div></div></div>";

var pinterestContentBase        = "<div class='item itempinterest'><div class='pleft'><a class='fancybox-thumbs' href='%LINK%' data-fancybox-group='thumbpins' title='%TITLE%' target='_blank'><img class='avatar' src='%IMGSRC%'></a></div><div class='pright'><div class='title'><a href='%URL%' target='_blank'>%TITLE%</a></div><div>%DESC%</div><span>%ATTRIB%</span></div>";
var pinterestHeaderBase         = "<div class='item itemheader'><div class='pleft'><img class='header_avatar' src='img/pinterest.png' title='avatar'/></div><div class='pright'><div class='title'><a target='_blank' href='%WEBSITE%'>%NAME%</a></div><div>%DESC%</div><span>%WEBSITE%</span></div></div>";

var tumblrContentBase           = "<div class='item itemtumblr'><div class='pleft'><img class='avatar' src='%IMGSRC%'/></div><div class='pright'><div class='title'><a href='%TLINK%' target='_blank'>%TITLE%</a></div><p>%CNT%</p><div><span>%NOTES% Notes</span> | <div class='time'>%TIME%</div> | <span><a href='%TLINK%' target='_blank'>Read more</a></span></div></div>";
var tumblrHeaderBase            = "<div class='item itemheader'><div class='pleft'><img class='header_avatar' src='%IMGSRC%' title='avatar'/></div><div class='pright'><div class='title'><a target='_blank' href='%LINK%'>%NAME%</a></div><div>%ABOUT%</div><span>%POSTS% Posts</span> | <div class='time'>%TIME%</div></div></div>";

var youtubeContentBase          = "<div class='item itemyoutube'><div class='pleft'><a class='fancybox-media' href='%LINK%' title='%TITLE%' target='_blank'><img class='avatar' src='%IMGSRC%'></a></div><div class='pright'><div class='title'><a href='%TITLEURL%' target='_blank'>%TITLE%</a></div><div>%CONTENT%</div><span>%LIKE% Like</span> | <span>%VIEWS% Views</span> | <span>%DURATION%</span> | <div class='time'>%TIME%</div></div>";
var youtubeHeaderBase           = "<div class='item itemheader'><div class='pleft'><img class='header_avatar' src='%IMGSRC%' title='avatar'/></div><div class='pright'><div class='title'><a target='_blank' href='%LINK%'>%NAME%</a></div><span>%VIEWS% Views</span> | <span>%SUBSCRIBE% Subscribe</span> | <div class='time'>%TIME%</div></div></div>";

var vimeoContentBase            = "<div class='item itemvimeo'><div class='pleft'><a class='fancybox-media' href='%LINK%' title='%TITLE%' target='_blank'><img class='avatar' src='%IMGSRC%'></a></div><div class='pright'><div class='title'><a href='%TITLEURL%' target='_blank'>%TITLE%</a></div><span>%LIKE% Like</span> | <span>%PLAYS% Plays</span> | <span>%COMMENT% Comments</span></div>";
var vimeoHeaderBase             = "<div class='item itemheader'><div class='pleft'><img class='header_avatar' src='%IMGSRC%' title='avatar'/></div><div class='pright'><div class='title'><a target='_blank' href='%LINK%'>%NAME%</a></div><div>%DESC%</div><span>%TOTALVIDEO% Views</span> | <span>%SUBSCRIBE% Subscribe</span></div></div>";

var behanceContentBase          = "<div class='item itembehance'><div class='pleft'><a class='fancybox-thumbs' data-fancybox-group='thumbbehance' href='%LINK%' title='%TITLE%' target='_blank'><img class='avatar' src='%IMGSRC%'></a></div><div class='pright'><div class='title'><a href='%TITLEURL%' target='_blank'>%TITLE%</a></div><div>%FIELDS%</div><span>%VIEWS% Views</span> | <span>%APPRE% Appreciation</span> | <span>%COMMENT% Comments</span></div>";
var behanceHeaderBase           = "<div class='item itemheader'><div class='pleft'><img class='header_avatar' src='%IMGSRC%' title='avatar'/></div><div class='pright'><div class='title'><a target='_blank' href='%LINK%'>%NAME%</a></div><div>%OCCUPATION%</div><span>%VIEWS% Views</span> | <span>%FOLLOWERS% Followers</span> | <span>%APPRE% Appreciation</span></div></div>";

// jQuery Document Ready Function
// ===============================================   
$(document).ready(function(){
    
// Social Tabs and Other
// ===============================================   
    $('.tabs a').click(function(e) {
        e.preventDefault();
        $(this).tab('show');
        var getitem = (this.href).split('#');
        $('#' + getitem[1] + 'scroll').mCustomScrollbar('update');
        
    });
    
    if (verticalNavigation) {
        $('#socialapp').addClass('vertical');
    }
    
    $('.app').css('width',mainWidth);
    $('.' + activeTab + '-nav').addClass('active');
    $('#' + activeTab).addClass('active');
    
// Fancybox Lightbox Gallery
// ===============================================    
    $('.fancybox-thumbs').fancybox({
        closeBtn: true,
        arrows: true,
        nextClick: true,
        helpers: {
            thumbs: {
                width: 50,
                height: 50
            }
        }
    });
    
    $('.fancybox-media')
            .attr('rel', 'media-gallery')
            .fancybox({
        openEffect: 'none',
        closeEffect: 'none',
        prevEffect: 'none',
        nextEffect: 'none',
        arrows: false,
        helpers: {
            media: {},
            buttons: {}
        }
    });
    
// Slim Scroll Settings and Infinity Scrolling
// ===============================================   
    $("#twitterscroll").mCustomScrollbar({
        autoHideScrollbar:autoHideScrollbar,
        set_height: scrollbarHeight,
        theme: "dark",
        scrollButtons: {
            enable: true
        },
        callbacks: {
            onTotalScroll: function() {
                if(infinityScroll) twitterMore();
            },
            onTotalScrollOffset:infinityScrollOffset
        }
    });
    $("#facebookscroll").mCustomScrollbar({
        autoHideScrollbar:autoHideScrollbar,
        set_height: scrollbarHeight,
        theme: "dark",
        scrollButtons: {
            enable: true
        },
        callbacks: {
            onTotalScroll: function() {
                if(infinityScroll) facebookMore();
            },
            onTotalScrollOffset:infinityScrollOffset
        }
    });
    $("#dribbblescroll").mCustomScrollbar({
        autoHideScrollbar:autoHideScrollbar,
        set_height: scrollbarHeight,
        theme: "dark",
        scrollButtons: {
            enable: true
        },
        callbacks: {
            onTotalScroll: function() {
                if(infinityScroll) dribbbleMore();
            },
            onTotalScrollOffset:infinityScrollOffset
        }
    });
    $("#flickrscroll").mCustomScrollbar({
        autoHideScrollbar:autoHideScrollbar,
        set_height: scrollbarHeight,
        theme: "dark",
        scrollButtons: {
            enable: true
        },
        callbacks: {
            onTotalScroll: function() {
                if(infinityScroll) flickrMore();
            },
            onTotalScrollOffset:infinityScrollOffset
        }
    });
    $("#pinterestscroll").mCustomScrollbar({
        autoHideScrollbar:autoHideScrollbar,
        set_height: scrollbarHeight,
        theme: "dark",
        scrollButtons: {
            enable: true
        },
        callbacks: {
            onTotalScroll: function() {
                if(infinityScroll) pinterestMore();
            },
            onTotalScrollOffset:infinityScrollOffset
        }
    });
    $("#tumblrscroll").mCustomScrollbar({
        autoHideScrollbar:autoHideScrollbar,
        set_height: scrollbarHeight,
        theme: "dark",
        scrollButtons: {
            enable: true
        },
        callbacks: {
            onTotalScroll: function() {
                if(infinityScroll) tumblrMore();
            },
            onTotalScrollOffset:infinityScrollOffset
        }
    });
    $("#youtubescroll").mCustomScrollbar({
        autoHideScrollbar:autoHideScrollbar,
        set_height: scrollbarHeight,
        theme: "dark",
        scrollButtons: {
            enable: true
        },
        callbacks: {
            onTotalScroll: function() {
                if(infinityScroll) youtubeMore();
            },
            onTotalScrollOffset:infinityScrollOffset
        }
    });
    $("#vimeoscroll").mCustomScrollbar({
        autoHideScrollbar:autoHideScrollbar,
        set_height: scrollbarHeight,
        theme: "dark",
        scrollButtons: {
            enable: true
        },
        callbacks: {
            onTotalScroll: function() {
                if(infinityScroll) vimeoMore();
            },
            onTotalScrollOffset:infinityScrollOffset
        }
    });
    $("#behancescroll").mCustomScrollbar({
        autoHideScrollbar:autoHideScrollbar,
        set_height: scrollbarHeight,
        theme: "dark",
        scrollButtons: {
            enable: true
        },
        callbacks: {
            onTotalScroll: function() {
                if(infinityScroll) behanceMore();
            },
            onTotalScrollOffset:infinityScrollOffset
        }
    });
    $("#rssscroll").mCustomScrollbar({
        autoHideScrollbar:autoHideScrollbar,
        set_height: scrollbarHeight,
        theme: "dark",
        scrollButtons: {
            enable: true
        },
        callbacks: {
            onTotalScroll: function() {
                if(infinityScroll) behanceMore();
            },
            onTotalScrollOffset:infinityScrollOffset
        }
    });
    
// Social APP Activate or Deactivate 
// ================================================
    if (twitterActive) {
        twitter();
        $('.tweetbtn a').live('click', function(e) {
            e.preventDefault();
            window.open(this.href, '', "width=600,height=500,scrollbars=yes");
        });

        $('#twitter .tmore').live('click', function() {
            twitterMore();
        });
    }
    if (facebookActive) {
        facebook();
        if(facebookProfil) facebookProfile();
        $('#facebook .tmore').live('click', function() {
            facebookMore();
        });
    }
    if (dribbbleActive) {
        dribbble();
        if(dribbbleProfil) dribbleProfile();
        $('#dribbble .tmore').live('click', function() {
            dribbbleMore();
        });
    }
    if (flickrActive) {
        flickrUserID();
        $('#flickr .tmore').live('click', function() {
            flickrMore();
        });
    }
    if (pinterestActive) {
        pinterest();
        if(pinterestProfil) pinterestProfile();
        $('#pinterest .tmore').live('click', function() {
            pinterestMore();
        });
    }
    if (tumblrActive) {
        tumblr();
        $('#tumblr .tmore').live('click', function() {
            tumblrMore();
        });
    }
    if (youtubeActive) {
        youtube();
        if(youtubeProfil) youtubeProfile();
        $('#youtube .tmore').live('click', function() {
            youtubeMore();
        });
    }
    if (vimeoActive) {
        vimeo();
        if(vimeoProfil) vimeoProfile();
        $('#vimeo .tmore').live('click', function() {
            vimeoMore();
        });
    }
    if (behanceActive) {
        behance();
        if (behanceProfil)
            behanceProfile();
        $('#behance .tmore').live('click', function() {
            behanceMore();
        });
    }
    if (rssActive) rssFeed();
});

var ajaxresponse = {
    'restwitter':null,
    'resfacebook':null,
    'resdribbble':null,
    'resflickr':null,
    'respinterest':null,
    'restumblr':null,
    'resyoutube':null,
    'resvimeo':null,
    'resbehance':null
};

// Twitter Function
// ================================================ 
function twitter() {
    var url = "http://api.twitter.com/1.1/statuses/user_timeline.json?screen_name=" + twitterUsername + "&count=" + twitterPer_Page + "&callback=?";
    var tbase = twitterContentBase;

    ajaxresponse.restwitter = $.ajax({
        dataType: "json",
        url: url,
        beforeSend: function() {
            $('#slimtwit').append('<div class="loading"><img src="img/loading.gif"></div>');
        },
        success: function(data) {
            $('#slimtwit .loading').remove();
            if (twitterProfil) {
                tpbase = twitterHeaderBase;
                wcontent = tpbase.replaceAll('%IMGSRC%', data[0].user.profile_image_url);
                wcontent = wcontent.replaceAll('%NAME%', data[0].user.name);
                wcontent = wcontent.replaceAll('%ABOUT%', (data[0].user.description).substr(0, twitterHeaderLimit));
                wcontent = wcontent.replaceAll('%LINK%', "http://twitter.com/" + data[0].user.screen_name);
                wcontent = wcontent.replaceAll('%TWEET%', number_format(data[0].user.statuses_count, 0, '.', '.'));
                wcontent = wcontent.replaceAll('%FOLLOWING%', number_format(data[0].user.friends_count, 0, '.', '.'));
                wcontent = wcontent.replaceAll('%FOLLOWER%', number_format(data[0].user.followers_count, 0, '.', '.'));
                $('#twitter').prepend(wcontent);
            }
            $.each(data, function(key, value) {
                wcontent = tbase.replaceAll('%CNT%', convertUrl(value.text));
                wcontent = wcontent.replaceAll('%IMG%', value.user.profile_image_url);
                wcontent = wcontent.replaceAll('%LNK%', twitterUsername + "/statuses/" + value.id_str);
                wcontent = wcontent.replaceAll('%LNKTL%', relativeTime(value.created_at));
                wcontent = wcontent.replaceAll('%RETWT%', value.id_str);
                wcontent = wcontent.replaceAll('%REPLY%', value.id_str);
                wcontent = wcontent.replaceAll('%FAVOR%', value.id_str);
                $('#slimtwit').append(wcontent);
            });
            $('#slimtwit').append('<a class="tmore">More</a>');
            $("#twitterscroll").mCustomScrollbar('update');
            $.cookie('twitterpaged', '1');
        }
    });
}
function twitterMore() {
    var paged       = parseInt($.cookie('twitterpaged')) + 1;
    var url         = "http://api.twitter.com/1.1/statuses/user_timeline.json?screen_name="+twitterUsername+"&count="+twitterPer_Page+"&page="+paged+"&callback=?"
    var tbase       = twitterContentBase;

    if ((ajaxresponse.restwitter === null) || (ajaxresponse.restwitter.state() === 'resolved')) {
        ajaxresponse.restwitter = $.ajax({
            dataType: "json",
            url: url,
            beforeSend: function() {
                $('#slimtwit .tmore').html('<img src="img/loading.gif" />');
            },
            success: function(data) {
                $('#slimtwit .tmore').remove();
                $.each(data, function(key, value) {
                    wcontent = tbase.replaceAll('%CNT%', convertUrl(value.text));
                    wcontent = wcontent.replaceAll('%IMG%', value.user.profile_image_url);
                    wcontent = wcontent.replaceAll('%LNK%', twitterUsername + "/statuses/" + value.id_str);
                    wcontent = wcontent.replaceAll('%LNKTL%', relativeTime(value.created_at));
                    wcontent = wcontent.replaceAll('%RETWT%', value.id_str);
                    wcontent = wcontent.replaceAll('%REPLY%', value.id_str);
                    wcontent = wcontent.replaceAll('%FAVOR%', value.id_str);
                    $('#slimtwit').append(wcontent);
                });
                if (data != "") {
                    $('#slimtwit').append('<a class="tmore">More</a>');
                    $("#twitterscroll").mCustomScrollbar('update');

                } else {
                    $('#slimtwit .tmore').remove();
                }
                $.cookie('twitterpaged', paged);
            }
        });
    }
}

// Facebook Function
// ================================================ 
function facebook(){
    var accessToken = facebookGetAccessToken(facebookAPPID,facebookAPPSecret);
    var url         = "https://graph.facebook.com/"+facebookPageName+"/feed?limit="+facebookPer_Page+"&access_token=" + accessToken;
    var tbase       = facebookContentBase;
    
    ajaxresponse.resfacebook = $.ajax({
        dataType: "jsonp",
        url: url,
        beforeSend:function(){
            $('#slimface').append('<div class="loading"><img src="img/loading.gif"></div>');
        },
        success: function(data){ console.log(data);
            $('#slimface .loading').remove();
            $.each(data.data, function(key, value) {
                if(value.message) {
                    link = value.id.split('_')
                    message = value.message;
                    wcontent = tbase.replaceAll('%CNT%', convertUrl(message.substr(0,facebookTrim)));
                    wcontent = wcontent.replaceAll('%TITLE%', value.from.name );
                    wcontent = wcontent.replaceAll('%IMGSRC%', 'https://graph.facebook.com/'+value.from.id+'/picture' );
                    wcontent = wcontent.replaceAll('%TLINK%', "https://www.facebook.com/"+facebookPageName+"/posts/" + link[1] );
                    wcontent = wcontent.replaceAll('%TIME%', relativeTime(value.created_time) );
                    wcontent = wcontent.replaceAll('%LIKE%', (value.likes) ? number_format(value.likes.count,0,'.','.') : "0" );
                    $('#slimface').append(wcontent);
                }
            }); 
            $('#slimface').append('<a class="tmore">More</a>');
            $("#facebookscroll").mCustomScrollbar('update');
            $.cookie('facebookpaged',data.paging.next);  
        }
    });
}
function facebookMore(){
    var url = $.cookie('facebookpaged');
    var tbase = facebookContentBase;

    if ((ajaxresponse.resfacebook === null) || (ajaxresponse.resfacebook.state() === 'resolved')) {
        ajaxresponse.resfacebook = $.ajax({
            dataType: "jsonp",
            url: url,
            beforeSend: function() {
                $('#slimface .tmore').html('<img src="img/loading.gif" />');
            },
            success: function(data) {
                $('#slimface .tmore').remove();
                $.each(data.data, function(key, value) {
                    if (value.message) {
                        $('#slimface .tmore').remove();
                        link = value.id.split('_')
                        message = value.message;
                        message = message.substr(0, facebookTrim)
                        wcontent = tbase.replaceAll('%CNT%', convertUrl(message));
                        wcontent = wcontent.replaceAll('%TITLE%', value.from.name);
                        wcontent = wcontent.replaceAll('%IMGSRC%', 'https://graph.facebook.com/' + value.from.id + '/picture');
                        wcontent = wcontent.replaceAll('%TLINK%', "https://www.facebook.com/" + facebookPageName + "/posts/" + link[1]);
                        wcontent = wcontent.replaceAll('%TIME%', relativeTime(value.created_time));
                        wcontent = wcontent.replaceAll('%LIKE%', (value.likes) ? number_format(value.likes.count, 0, '.', '.') : "0");
                        $('#slimface').append(wcontent);
                    }
                });
                if (data.paging) {
                    $('#slimface').append('<a class="tmore">More</a>');
                    $("#facebookscroll").mCustomScrollbar('update');
                   $.cookie('facebookpaged', data.paging.next);
                } else {
                    $('#slimface .tmore').remove();
                }
            }
        });
    }
}
function facebookGetAccessToken(appID,appSecret){
    //var url         = "https://graph.facebook.com/oauth/access_token?grant_type=fb_exchange_token&client_id="+appID+"&client_secret=" + appSecret;
    var url         = "https://graph.facebook.com/oauth/access_token?grant_type=fb_exchange_token&client_id="+appID+"&client_secret=" + appSecret;
    var accessToken = "";
    
    if ($.cookie('facebookAT') && $.cookie('facebookPGN') == facebookPageName) {
        accessToken = $.cookie('facebookAT');
    } else {
        $.ajax({
            async: false,
            dataType: 'json',
            url: url,
            complete: function(data) {
                responseText = data.responseText;
                responseText = responseText.split('=');
                accessToken = responseText[1];
                $.cookie('facebookAT', accessToken);
                $.cookie('facebookPGN', facebookPageName);
            }
        });
    }
    
    return accessToken;
}
function facebookProfile(){
    var url         = "https://graph.facebook.com/" + facebookPageName;
    var pageImage   = 'https://graph.facebook.com/'+facebookPageName+'/picture';
    var tbase       = facebookHeaderBase;
    
    $.ajax({
        dataType: "jsonp",
        url: url,
        success: function(data) {
                wcontent = tbase.replaceAll('%IMGSRC%', pageImage);
                wcontent = wcontent.replaceAll('%NAME%', data.name);
                wcontent = wcontent.replaceAll('%ABOUT%', data.about);
                wcontent = wcontent.replaceAll('%LINK%', data.link);
                wcontent = wcontent.replaceAll('%FOUNDED%', relativeTime(data.founded));
                wcontent = wcontent.replaceAll('%LIKE%', number_format(data.likes,0,'.','.'));
                $('#facebook').prepend(wcontent);

        }
    });
}

// Dribbble Function
// ================================================
function dribbble() {
    var url         = "http://api.dribbble.com/players/"+dribbbleUsername+"/shots?page=1&per_page="+dribbblePer_Page;
    var tbase       = dribbbleContentBaseGallery;
    
    ajaxresponse.resdribbble = $.ajax({
        dataType:"jsonp",
        url: url,
        beforeSend:function(){
            $('#slimdribbble').append('<div class="loading"><img src="img/loading.gif"></div>');
        },
        success: function(data){
            $('#slimdribbble .loading').remove();
            if (dribbbleGallery) {
                $('#slimdribbble').append("<div class='item itemdribbble'><ul></ul></div>");
                $.each(data.shots, function(key, value) {
                    wcontent = tbase.replaceAll('%LINK%', value.image_url);
                    wcontent = wcontent.replaceAll('%IMGSRC%', value.image_teaser_url);
                    wcontent = wcontent.replaceAll('%TITLE%', value.title);
                    $('#slimdribbble ul').append(wcontent);
                });
            } else {
                tbase = dribbbleContentBase;
                $.each(data.shots, function(key, value) {
                    wcontent = tbase.replaceAll('%LINK%', value.image_url);
                    wcontent = wcontent.replaceAll('%IMGSRC%', value.image_teaser_url);
                    wcontent = wcontent.replaceAll('%TITLE%', value.title);
                    wcontent = wcontent.replaceAll('%URL%', value.url);
                    wcontent = wcontent.replaceAll('%TIME%', relativeTime(value.created_at));
                    wcontent = wcontent.replaceAll('%COMMENT%', number_format(value.comments_count,0,'.','.'));
                    wcontent = wcontent.replaceAll('%LIKE%', number_format(value.likes_count,0,'.','.'));
                    wcontent = wcontent.replaceAll('%VIEWS%', number_format(value.views_count,0,'.','.'));
                    $('#slimdribbble').append(wcontent);
                });
            }
            $('#slimdribbble').append('<a class="tmore">More</a>');
            $("#dribbblescroll").mCustomScrollbar('update');
            $.cookie('dribbblepaged',1);
        }
    });
}
function dribbbleMore() {
    var paged       = parseInt($.cookie('dribbblepaged')) + 1;
    var url         = "http://api.dribbble.com/players/"+dribbbleUsername+"/shots?page="+paged+"&per_page="+dribbblePer_Page;
    var tbase       = dribbbleContentBaseGallery;
    
    if ((ajaxresponse.resdribbble === null) || (ajaxresponse.resdribbble.state() === 'resolved')) {
        ajaxresponse.resdribbble = $.ajax({
            dataType: "jsonp",
            url: url,
            beforeSend: function() {
                $('#slimdribbble .tmore').html('<img src="img/loading.gif" />');
            },
            success: function(data) {
                $('#slimdribbble .tmore').remove();
                if (dribbbleGallery) {
                    $.each(data.shots, function(key, value) {
                        wcontent = tbase.replace('%LINK%', value.image_url);
                        wcontent = wcontent.replaceAll('%IMGSRC%', value.image_teaser_url);
                        wcontent = wcontent.replaceAll('%TITLE%', value.title);
                        $('#slimdribbble ul').append(wcontent);
                    });
                } else {
                    tbase = dribbbleContentBase;
                    $.each(data.shots, function(key, value) {
                        wcontent = tbase.replace('%LINK%', value.image_url);
                        wcontent = wcontent.replaceAll('%IMGSRC%', value.image_teaser_url);
                        wcontent = wcontent.replaceAll('%TITLE%', value.title);
                        wcontent = wcontent.replaceAll('%URL%', value.url);
                        wcontent = wcontent.replaceAll('%TIME%', relativeTime(value.created_at));
                        wcontent = wcontent.replaceAll('%COMMENT%', number_format(value.comments_count, 0, '.', '.'));
                        wcontent = wcontent.replaceAll('%LIKE%', number_format(value.likes_count, 0, '.', '.'));
                        wcontent = wcontent.replaceAll('%VIEWS%', number_format(value.views_count, 0, '.', '.'));
                        $('#slimdribbble').append(wcontent);
                    });
                }
                if (data.shots != "") {
                    $('#slimdribbble').append('<a class="tmore">More</a>');
                    $("#dribbblescroll").mCustomScrollbar('update');
                } else {
                    $('#slimdribbble .tmore').remove();
                }
                $.cookie('dribbblepaged', paged);
            }
        });
    }
}
function dribbleProfile(){
    var url         = "http://api.dribbble.com/players/" + dribbbleUsername;
    var tbase       = dribbbleHeaderBase;
    
    $.ajax({
        dataType: "jsonp",
        url: url,
        success: function(data) {
                wcontent = tbase.replaceAll('%IMGSRC%', data.avatar_url);
                wcontent = wcontent.replaceAll('%NAME%', data.name);
                wcontent = wcontent.replaceAll('%LOCATION%', data.location);
                wcontent = wcontent.replaceAll('%LINK%', data.url);
                wcontent = wcontent.replaceAll('%FOLLOW%', number_format(data.followers_count,0,'.','.'));
                wcontent = wcontent.replaceAll('%SHOUT%', number_format(data.shots_count,0,'.','.'));
                wcontent = wcontent.replaceAll('%LIKE%', number_format(data.likes_received_count,0,'.','.'));
                $('#dribbble').prepend(wcontent);

        }
    });
}

// Flickr Function
// ================================================ 
var userID = null;
function flickr(userid) {
    var url         = "http://api.flickr.com/services/rest/?jsoncallback=?&api_key="+flickrAPI_KEY+"&method=flickr.photos.search&format=json&nojsoncallback=0&user_id="+userid+"&per_page="+flickrPer_Page+"&page=1";
    var tbase       = flickrContentBase;
    
    ajaxresponse.resflickr = $.ajax({
        dataType: "jsonp",
        url: url,
        beforeSend: function() {
            $('#slimflickr').append('<div class="loading"><img src="img/loading.gif"></div>');
        },
        success: function(data) {
            $('#slimflickr .loading').remove();
            $('#slimflickr').append("<div class='item itemflickr'><ul></ul></div>");
            $.each(data.photos.photo, function(key, value) {
                wcontent = tbase.replaceAll('%LINK%', "http://farm" + value.farm + ".static.flickr.com/" + value.server + "/" + value.id + "_" + value.secret + "_b.jpg");
                wcontent = wcontent.replaceAll('%IMGSRC%', "http://farm" + value.farm + ".static.flickr.com/" + value.server + "/" + value.id + "_" + value.secret + "_s.jpg");
                $('#slimflickr  ul').append(wcontent);
            });
            $('#slimflickr').append('<a class="tmore">More</a>');
            $("#flickrscroll").mCustomScrollbar('update');
            $.cookie('flickrpaged', 1);
        }
    });
}
function flickrMore() {
    var userID2      = (userID !== null) ? userID : $.cookie('flickerUID');
    var paged       = parseInt($.cookie('flickrpaged')) + 1;
    var url         = "http://api.flickr.com/services/rest/?jsoncallback=?&api_key="+flickrAPI_KEY+"&method=flickr.photos.search&format=json&user_id="+userID2+"&per_page="+flickrPer_Page+"&page="+paged;
    var tbase = flickrContentBase;

    if ((ajaxresponse.resflickr === null) || (ajaxresponse.resflickr.state() === 'resolved')) {
        ajaxresponse.resflickr = $.ajax({
            dataType: "jsonp",
            url: url,
            beforeSend: function() {
                $('#slimflickr .tmore').html('<img src="img/loading.gif" />');
            },
            success: function(data) {
                $('#slimflickr .tmore').remove();
                $.each(data.photos.photo, function(key, value) {
                    wcontent = tbase.replaceAll('%LINK%', "http://farm" + value.farm + ".static.flickr.com/" + value.server + "/" + value.id + "_" + value.secret + "_b.jpg");
                    wcontent = wcontent.replaceAll('%IMGSRC%', "http://farm" + value.farm + ".static.flickr.com/" + value.server + "/" + value.id + "_" + value.secret + "_s.jpg");
                    $('#slimflickr  ul').append(wcontent);
                });
                if (data.photos.photo != "") {
                    $('#slimflickr').append('<a class="tmore">More</a>');
                    $("#flickrscroll").mCustomScrollbar('update');
                } else {
                    $('#slimflickr .tmore').remove();
                }
                $.cookie('flickrpaged', paged);
            }
        });
    }
}
function flickrUserID(){
    var url         = "http://api.flickr.com/services/rest/?jsoncallback=?&api_key="+flickrAPI_KEY+"&method=flickr.urls.lookupUser&format=json&nojsoncallback=1&url=http://www.flickr.com/photos/" + flickrUsername;
    
    if($.cookie('flickerUID') && ($.cookie('flickerUNM') === flickrUsername) ) {
        userID = $.cookie('flickerUID');
        flickr(userID);
        if (flickrProfil) flickrProfile(userID);
    } else {
        returned = $.ajax({
            async: false,
            dataType:'jsonp',
            url: url,
            success: function(data){
                $.cookie('flickerUNM',flickrUsername);
                $.cookie('flickerUID',data.user.id);
            }
        });
        returned.done(function(result) {
            userID = result.user.id;
            flickr(result.user.id);
            if (flickrProfil) flickrProfile(result.user.id);
        });
    }
}
function flickrProfile(userid){
    var url         = "http://api.flickr.com/services/rest/?jsoncallback=?&method=flickr.people.getInfo&api_key="+flickrAPI_KEY+"&user_id="+userid+"&format=json&nojsoncallback=1";
    var tbase       = flickrHeaderBase;
    
    $.ajax({
        dataType: "jsonp",
        url: url,
        success: function(data) {
            time = new Date(parseInt(data.person.photos.firstdate._content) * 1000);
            wcontent = tbase.replaceAll('%IMGSRC%', "img/flickr.png");
            wcontent = wcontent.replaceAll('%NAME%', data.person.realname._content);
            wcontent = wcontent.replaceAll('%ABOUT%', data.person.description._content);
            wcontent = wcontent.replaceAll('%LINK%', data.person.profileurl._content);
            wcontent = wcontent.replaceAll('%IMAGES%', number_format(data.person.photos.count._content,0,'.','.'));
            wcontent = wcontent.replaceAll('%TIME%', relativeTime(time));
            $('#flickr').prepend(wcontent);
        }
    });
}

// Pinterest Function
// ================================================
function pinterest() {
    var url         = "lib/pinterest.php?username="+pinterestUsername;
    var tbase       = pinterestContentBase;
    
    ajaxresponse.respinterest = $.ajax({
        dataType:"json",
        url: url,
        beforeSend:function(){
            $('#slimpinterest').append('<div class="loading"><img src="img/loading.gif"></div>');
        },
        success: function(data) {
            var counter = 0;
            $('#slimpinterest .loading').remove();
            $.each(data.body, function(key, value) {
                if (counter < pinterestPer_Page) {
                    wcontent = tbase.replace('%LINK%', value.src);
                    wcontent = wcontent.replace('%IMGSRC%', value.src);
                    wcontent = wcontent.replaceAll('%TITLE%', value.board);
                    wcontent = wcontent.replace('%URL%', value.href);
                    wcontent = wcontent.replace('%DESC%', (value.desc).substr(0, pinterestDescLimit));
                    wcontent = wcontent.replace('%ATTRIB%', convertUrl(value.attrib));
                    $('#slimpinterest').append(wcontent);
                    counter++;
                }
            });
            $('#slimpinterest').append('<a class="tmore">More</a>');
            $("#pinterestscroll").mCustomScrollbar('update');
            $.cookie('pinterestpaged', pinterestPer_Page);
        }
    });
}
function pinterestMore() {
    var paged       = parseInt($.cookie('pinterestpaged'));
    var maxpage     = paged + pinterestPer_Page;       
    var url         = "lib/pinterest.php?username="+pinterestUsername;
    var tbase       = pinterestContentBase;
    
    if ((ajaxresponse.respinterest === null) || (ajaxresponse.respinterest.state() === 'resolved')) {
        ajaxresponse.respinterest = $.ajax({
            dataType: "json",
            url: url,
            beforeSend: function() {
                $('#slimpinterest .tmore').html('<img src="img/loading.gif" />');
            },
            success: function(data) {
                $('#slimpinterest .tmore').remove();
                var counter = 0;
                $.each(data.body, function(key, value) {
                    if (counter >= paged && counter <= maxpage) {
                        wcontent = tbase.replace('%LINK%', value.src);
                        wcontent = wcontent.replace('%IMGSRC%', value.src);
                        wcontent = wcontent.replaceAll('%TITLE%', value.board);
                        wcontent = wcontent.replace('%URL%', value.href);
                        wcontent = wcontent.replace('%DESC%', (value.desc).substr(0, pinterestDescLimit));
                        wcontent = wcontent.replace('%ATTRIB%', convertUrl(value.attrib));
                        $('#slimpinterest').append(wcontent);
                    }
                    counter++;
                });
                if (parseInt($.cookie('pinterestpaged')) <= 50) {
                    $('#slimpinterest').append('<a class="tmore">More</a>');
                    $("#pinterestscroll").mCustomScrollbar('update');
                } else {
                    $('#slimpinterest .tmore').remove();
                }
                $.cookie('pinterestpaged', maxpage);
            }
        });
    }
}
function pinterestProfile(){
    var tbase       = pinterestHeaderBase;
    
    newbase = tbase.replaceAll('%NAME%', pinterestProfileName);
    newbase = newbase.replaceAll('%DESC%', pinterestProfileDesc);
    newbase = newbase.replaceAll('%WEBSITE%', pinterestProfileWebS);

    $('#pinterest').prepend(newbase); 
}

// Tumblr Function
// ================================================
function tumblr() {
    var url         = "http://api.tumblr.com/v2/blog/"+tumblrUsername+"/posts?limit="+tumblrPer_Page+"&offset=0&api_key="+tumblrAPI_KEY+"&notes_info=true";
    var avatar      = "http://api.tumblr.com/v2/blog/"+tumblrUsername+".tumblr.com/avatar";
    var tbase       = tumblrContentBase;
    
    ajaxresponse.restumblr = $.ajax({
        url: url,
        dataType:"jsonp",
        beforeSend:function(){
            $('#slimtumblr').append('<div class="loading"><img src="img/loading.gif"></div>');
        },
        success: function(data){
            $('#slimtumblr .loading').remove();
            if (tumblrProfil) {
                tpbase = tumblrHeaderBase;
                wcontent = tpbase.replace('%IMGSRC%', avatar);
                wcontent = wcontent.replace('%NAME%', data.response.blog.title);
                wcontent = wcontent.replace('%ABOUT%', (removeHtml(data.response.blog.description)).substr(0,80));
                wcontent = wcontent.replace('%LINK%', data.response.blog.url);
                wcontent = wcontent.replace('%POSTS%', number_format(data.response.blog.posts,0,'.','.'));
                wcontent = wcontent.replace('%TIME%', relativeTime(data.response.blog.updated * 1000));
                $('#tumblr').prepend(wcontent);
            }
            $.each(data.response.posts, function(key, value) {
                if (value.type == "text") {
                    message = (typeof(value.body) != "undefined" || value.body != null) ? removeHtml(value.body) : "";
                    message =  message.substr(0,tumblrTrim);
                    title   = (value.title != null) ? (value.title).substr(0,tumblrTitleTrim) : "";
                    title   = (title.length >= tumblrTitleTrim) ? title + "..." : title;
                } else if (value.type == "photo") {
                    message = " <a class='fancybox-thumbs' href='" + value.photos[0].original_size.url + "' ><img src='"+value.photos[0].alt_sizes[4].url+"' /></a>";
                    title   = (value.caption != null) ? (removeHtml(value.caption)).substr(0,tumblrTitleTrim) : "";
                    title   = (title.length >= tumblrTitleTrim) ? title + "..." : title;
                } else if (value.type == "quote") {
                    message = (typeof(value.text) != "undefined" || value.text != null) ? removeHtml(value.text) : "";
                    message =  message.substr(0,tumblrTrim);
                    title   = (value.source_title != null) ? (value.source_title).substr(0,tumblrTitleTrim) : "";
                    title   = (title.length >= tumblrTitleTrim) ? title + "..." : title;
                } else if (value.type == "link") {
                    message = (typeof(value.description) != "undefined" || value.description != null) ? removeHtml(value.description) : "";
                    message =  message.substr(0,tumblrTrim);
                    title   = (value.title != null) ? (value.title).substr(0,tumblrTitleTrim) : "";
                    title   = (title.length >= tumblrTitleTrim) ? title + "..." : title;
                } else if (value.type == "chat") {
                    message = (typeof(value.body) != "undefined" || value.body != null ) ? removeHtml(value.body) : "";
                    message =  message.substr(0,tumblrTrim);
                    title   = (value.title != null) ? (value.title).substr(0,tumblrTitleTrim) : "" ;
                    title   = (title.length >= tumblrTitleTrim) ? title + "..." : title;
                } else if (value.type == "audio") {
                    message = (typeof(value.player) != "undefined" || value.player != null ) ? value.player : "";
                    title   = (value.id3_title != null) ? (value.id3_title).substr(0,tumblrTitleTrim) : "" ;
                    title   = (title.length >= tumblrTitleTrim) ? title + "..." : title;
                } else if (value.type == "video") {
                    message = (typeof(value.player[0].embed_code) != "undefined" || value.player[0].embed_code != null) ? value.player[0].embed_code : "";
                    title   = (value.source_title != null) ? (value.source_title).substr(0,tumblrTitleTrim) : "" ;
                    title   = (title.length >= tumblrTitleTrim) ? title + "..." : title; 
                } else if (value.type == "answer") {
                    message = (typeof(value.answer) != "undefined" || value.answer != null ) ? removeHtml(value.answer) : "";
                    message =  message.substr(0,tumblrTrim);
                    title   = (value.question != null) ? (value.question).substr(0,tumblrTitleTrim) : "" ;
                    title   = (title.length >= tumblrTitleTrim) ? title + "..." : title;
                }
                wcontent = tbase.replace('%TITLE%', title );
                wcontent = wcontent.replace('%IMGSRC%', avatar );
                wcontent = wcontent.replaceAll('%TLINK%', value.post_url );
                wcontent = wcontent.replace('%TIME%', relativeTime(value.timestamp * 1000) );
                wcontent = wcontent.replace('%NOTES%', (value.note_count) ? number_format(value.note_count,0,'.','.') : "0" );
                wcontent = wcontent.replace('%CNT%', message );
                $('#slimtumblr').append(wcontent);
            });
            $('#slimtumblr').append('<a class="tmore">More</a>');
            $("#tumblrscroll").mCustomScrollbar('update');
            $.cookie('tumblrpaged',0);
        }
    });
}
function tumblrMore() {
    var paged       = parseInt($.cookie('tumblrpaged')) + tumblrPer_Page;
    var url         = "http://api.tumblr.com/v2/blog/"+tumblrUsername+"/posts?limit="+tumblrPer_Page+"&offset="+paged+"&api_key="+tumblrAPI_KEY+"&notes_info=true";
    var avatar      = "http://api.tumblr.com/v2/blog/"+tumblrUsername+".tumblr.com/avatar";
    var tbase       = tumblrContentBase;

    if ((ajaxresponse.restumblr === null) || (ajaxresponse.restumblr.state() === 'resolved')) {
        ajaxresponse.restumblr = $.ajax({
            url: url,
            dataType: "jsonp",
            beforeSend: function() {
                $('#slimtumblr .tmore').html('<img src="img/loading.gif" />');
            },
            success: function(data) {
                $('#slimtumblr .tmore').remove();
                $.each(data.response.posts, function(key, value) {
                    if (value.type == "text") {
                        message = (typeof(value.body) != "undefined" || value.body != null) ? removeHtml(value.body) : "";
                        message = message.substr(0, tumblrTrim);
                        title = (value.title != null) ? (value.title).substr(0, tumblrTitleTrim) : "";
                        title = (title.length >= tumblrTitleTrim) ? title + "..." : title;
                    } else if (value.type == "photo") {
                        message = " <a class='fancybox-thumbs' href='" + value.photos[0].original_size.url + "' ><img src='" + value.photos[0].alt_sizes[4].url + "' /></a>";
                        title = (value.caption != null) ? (removeHtml(value.caption)).substr(0, tumblrTitleTrim) : "";
                        title = (title.length >= tumblrTitleTrim) ? title + "..." : title;
                    } else if (value.type == "quote") {
                        message = (typeof(value.text) != "undefined" || value.text != null) ? removeHtml(value.text) : "";
                        message = message.substr(0, tumblrTrim);
                        title = (value.source_title != null) ? (value.source_title).substr(0, tumblrTitleTrim) : "";
                        title = (title.length >= tumblrTitleTrim) ? title + "..." : title;
                    } else if (value.type == "link") {
                        message = (typeof(value.description) != "undefined" || value.description != null) ? removeHtml(value.description) : "";
                        message = message.substr(0, tumblrTrim);
                        title = (value.title != null) ? (value.title).substr(0, tumblrTitleTrim) : "";
                        title = (title.length >= tumblrTitleTrim) ? title + "..." : title;
                    } else if (value.type == "chat") {
                        message = (typeof(value.body) != "undefined" || value.body != null) ? removeHtml(value.body) : "";
                        message = message.substr(0, tumblrTrim);
                        title = (value.title != null) ? (value.title).substr(0, tumblrTitleTrim) : "";
                        title = (title.length >= tumblrTitleTrim) ? title + "..." : title;
                    } else if (value.type == "audio") {
                        message = (typeof(value.player) != "undefined" || value.player != null) ? value.player : "";
                        title = (value.id3_title != null) ? (value.id3_title).substr(0, tumblrTitleTrim) : "";
                        title = (title.length >= tumblrTitleTrim) ? title + "..." : title;
                    } else if (value.type == "video") {
                        message = (typeof(value.player[0].embed_code) != "undefined" || value.player[0].embed_code != null) ? value.player[0].embed_code : "";
                        title = (value.source_title != null) ? (value.source_title).substr(0, tumblrTitleTrim) : "";
                        title = (title.length >= tumblrTitleTrim) ? title + "..." : title;
                    } else if (value.type == "answer") {
                        message = (typeof(value.answer) != "undefined" || value.answer != null) ? removeHtml(value.answer) : "";
                        message = message.substr(0, tumblrTrim);
                        title = (value.question != null) ? (value.question).substr(0, tumblrTitleTrim) : "";
                        title = (title.length >= tumblrTitleTrim) ? title + "..." : title;
                    }
                    wcontent = tbase.replace('%TITLE%', title);
                    wcontent = wcontent.replace('%IMGSRC%', avatar);
                    wcontent = wcontent.replace('%TLINK%', value.post_url);
                    wcontent = wcontent.replace('%TIME%', relativeTime(value.timestamp * 1000));
                    wcontent = wcontent.replace('%NOTES%', (value.note_count) ? number_format(value.note_count, 0, '.', '.') : "0");
                    wcontent = wcontent.replace('%CNT%', message);
                    $('#slimtumblr').append(wcontent);
                });
                if (data.response.posts != "") {
                    $('#slimtumblr').append('<a class="tmore">More</a>');
                    $("#tumblrscroll").mCustomScrollbar('update');
                } else {
                    $('#slimtumblr .tmore').remove();
                }
                $.cookie('tumblrpaged', paged);
            }
        });
    }
}

// Youtube Function
// ================================================
function youtube() {
    var url         = "https://gdata.youtube.com/feeds/api/users/"+youtubeChannelName+"/uploads?v=2&alt=jsonc&max-results="+youtubePer_Page;
    var tbase       = youtubeContentBase;
    
    ajaxresponse.resyoutube = $.ajax({
        dataType:"jsonp",
        url: url,
        beforeSend:function(){
            $('#slimyoutube').append('<div class="loading"><img src="img/loading.gif"></div>');
        },
        success: function(data){
            $('#slimyoutube .loading').remove();
            $.each(data.data.items, function(key, value) {
                time=secondsToTime(value.duration);
                wcontent = tbase.replaceAll('%LINK%', value.player['default'] );
                wcontent = wcontent.replace('%IMGSRC%', value.thumbnail.sqDefault);
                wcontent = wcontent.replaceAll('%TITLE%', (value.title).substr(0,youtubeTitleLimit) );
                wcontent = wcontent.replaceAll('%CONTENT%', (value.description).substr(0,youtubeContentLimit) );
                wcontent = wcontent.replace('%TITLEURL%', value.player['default']);
                wcontent = wcontent.replace('%DURATION%',  time.h +":"+ time.m +":"+ time.s);
                wcontent = wcontent.replace('%LIKE%',  value.likeCount );
                wcontent = wcontent.replace('%VIEWS%',  number_format(value.viewCount,0,'.','.') );
                wcontent = wcontent.replace('%TIME%', relativeTime(value.uploaded));
                $('#slimyoutube').append(wcontent);
            });
            $('#slimyoutube').append('<a class="tmore">More</a>');
            $("#youtubescroll").mCustomScrollbar('update');
            $.cookie('youtubepaged',1);
        }
    });
}
function youtubeMore() {
    var paged       = parseInt($.cookie('youtubepaged')) + youtubePer_Page;
    var url         = "https://gdata.youtube.com/feeds/api/users/"+youtubeChannelName+"/uploads?v=2&alt=jsonc&max-results="+youtubePer_Page+"&start-index="+paged;
    var tbase       = youtubeContentBase;

    if ((ajaxresponse.resyoutube === null) || (ajaxresponse.resyoutube.state() === 'resolved')) {
        ajaxresponse.resyoutube = $.ajax({
            dataType: "jsonp",
            url: url,
            beforeSend: function() {
                $('#slimyoutube .tmore').html('<img src="img/loading.gif" />');
            },
            success: function(data) {
                $('#slimyoutube .tmore').remove();
                $.each(data.data.items, function(key, value) {
                    time = secondsToTime(value.duration);
                    wcontent = tbase.replace('%LINK%', value.player['default']);
                    wcontent = wcontent.replace('%IMGSRC%', value.thumbnail.sqDefault);
                    wcontent = wcontent.replaceAll('%TITLE%', (value.title).substr(0, youtubeTitleLimit));
                    wcontent = wcontent.replaceAll('%CONTENT%', (value.description).substr(0, youtubeContentLimit));
                    wcontent = wcontent.replace('%TITLEURL%', value.player['default']);
                    wcontent = wcontent.replace('%DURATION%', time.h + ":" + time.m + ":" + time.s);
                    wcontent = wcontent.replace('%LIKE%', value.likeCount);
                    wcontent = wcontent.replace('%VIEWS%', number_format(value.viewCount, 0, '.', '.'));
                    wcontent = wcontent.replace('%TIME%', relativeTime(value.uploaded));
                    $('#slimyoutube').append(wcontent);
                });
                if (typeof(data.data.items) != "undefined") {
                    $('#slimyoutube').append('<a class="tmore">More</a>');
                    $("#youtubescroll").mCustomScrollbar('update');
                } else {
                    $('#slimyoutube .tmore').remove();
                }
                $.cookie('youtubepaged', paged);
            }
        });
    }
}
function youtubeProfile(){
    var url         = "http://gdata.youtube.com/feeds/api/users/"+youtubeChannelName+"?alt=json";
    var tbase       = youtubeHeaderBase;
    
    $.ajax({
        dataType: "jsonp",
        url: url,
        success: function(data) {
                wcontent = tbase.replace('%IMGSRC%', data.entry.media$thumbnail.url);
                wcontent = wcontent.replace('%NAME%', data.entry.author[0].name.$t);
                wcontent = wcontent.replace('%LINK%', data.entry.link[0].href);
                wcontent = wcontent.replace('%VIEWS%', number_format(data.entry.yt$statistics.viewCount,0,'.','.'));
                wcontent = wcontent.replace('%SUBSCRIBE%', number_format(data.entry.yt$statistics.subscriberCount,0,'.','.'));
                wcontent = wcontent.replace('%TIME%', relativeTime(data.entry.published.$t));
                $('#youtube').prepend(wcontent);
        }
    });
}

// Vimeo Function
// ================================================
function vimeo() {      
    var url         = "lib/vimeo.php?per_page="+vimeoPer_Page+"&ck="+vimeoConsumerKey+"&csk="+vimeoConsumerSecret+"&channelname="+vimeoChannelName+"&type=vimeo.channels.getVideos";
    var tbase       = vimeoContentBase;
    
    ajaxresponse.resvimeo = $.ajax({
        dataType:"json",
        url: url,
        beforeSend:function(){
            $('#slimvimeo').append('<div class="loading"><img src="img/loading.gif"></div>');
        },
        success: function(data){
            $('#slimvimeo .loading').remove();
            $.each(data.videos.video, function(key, value) {
                wcontent = tbase.replace('%LINK%', "http://vimeo.com/" + value.id);
                wcontent = wcontent.replace('%IMGSRC%', value.thumbnails.thumbnail[0]._content);
                wcontent = wcontent.replaceAll('%TITLE%', (value.title).substr(0,vimeoTitleLimit) );
                wcontent = wcontent.replace('%TITLEURL%', "http://vimeo.com/" + value.id);
                wcontent = wcontent.replace('%LIKE%',  number_format(value.number_of_likes,0,'.','.') );
                wcontent = wcontent.replace('%PLAYS%',  number_format(value.number_of_plays,0,'.','.') );
                wcontent = wcontent.replace('%COMMENT%', number_format(value.number_of_comments,0,'.','.') );
                $('#slimvimeo').append(wcontent);
            });
            $('#slimvimeo').append('<a class="tmore">More</a>');
            $("#vimeoscroll").mCustomScrollbar('update');
            $.cookie('vimeopaged',1);
        }
    });

}
function vimeoMore() {
    var paged       = parseInt($.cookie('vimeopaged')) + 1;
    var url         = "lib/vimeo.php?page="+paged+"&per_page="+vimeoPer_Page+"&ck="+vimeoConsumerKey+"&csk="+vimeoConsumerSecret+"&channelname="+vimeoChannelName+"&type=vimeo.channels.getVideos";
    var tbase       = vimeoContentBase;
    
    if ((ajaxresponse.resvimeo === null) || (ajaxresponse.resvimeo.state() === 'resolved')) {
        ajaxresponse.resvimeo = $.ajax({
            dataType: "json",
            url: url,
            beforeSend: function() {
                $('#slimvimeo .tmore').html('<img src="img/loading.gif" />');
            },
            success: function(data) {
                $('#slimvimeo .tmore').remove();
                $.each(data.videos.video, function(key, value) {
                    wcontent = tbase.replace('%LINK%', "http://vimeo.com/" + value.id);
                    wcontent = wcontent.replace('%IMGSRC%', value.thumbnails.thumbnail[0]._content);
                    wcontent = wcontent.replaceAll('%TITLE%', (value.title).substr(0, vimeoTitleLimit));
                    wcontent = wcontent.replace('%TITLEURL%', "http://vimeo.com/" + value.id);
                    wcontent = wcontent.replace('%LIKE%', number_format(value.number_of_likes, 0, '.', '.'));
                    wcontent = wcontent.replace('%PLAYS%', number_format(value.number_of_plays, 0, '.', '.'));
                    wcontent = wcontent.replace('%COMMENT%', number_format(value.number_of_comments, 0, '.', '.'));
                    $('#slimvimeo').append(wcontent);
                });
                if (typeof(data.videos.video[0].id) != "undefined") {
                    $('#slimvimeo').append('<a class="tmore">More</a>');
                    $("#vimeoscroll").mCustomScrollbar('update');
                } else {
                    $('#slimvimeo .tmore').remove();
                }
                $.cookie('vimeopaged', paged);
            }
        });
    }
}
function vimeoProfile(){
    var url         = "lib/vimeo.php?ck="+vimeoConsumerKey+"&csk="+vimeoConsumerSecret+"&channelname="+vimeoChannelName+"&type=vimeo.channels.getInfo";
    var tbase       = vimeoHeaderBase;
    
    $.ajax({
        dataType: "json",
        url: url,
        success: function(data) {
            wcontent = tbase.replace('%IMGSRC%', data.channel.thumbnail_url);
            wcontent = wcontent.replace('%NAME%', data.channel.name);
            wcontent = wcontent.replace('%LINK%', data.channel.url);
            wcontent = wcontent.replace('%DESC%', (data.channel.description).substr(0,vimeoHeaderLimitDesc) );
            wcontent = wcontent.replace('%TOTALVIDEO%', number_format(data.channel.total_videos,0,'.','.'));
            wcontent = wcontent.replace('%SUBSCRIBE%', number_format(data.channel.total_subscribers,0,'.','.'));
            $('#vimeo').prepend(wcontent);
        }
    });
}

// Behance Function
// ================================================
function behance() {
    var url         = "http://www.behance.net/v2/users/"+behanceUsername+"/projects?per_page="+behancePer_Page+"&api_key="+behanceAPP_ID;
    var tbase       = behanceContentBase;
    
    ajaxresponse.resbehance = $.ajax({
        dataType:"jsonp",
        url: url,
        beforeSend:function(){
            $('#slimbehance').append('<div class="loading"><img src="img/loading.gif"></div>');
        },
        success: function(data){
            $('#slimbehance .loading').remove();
            $.each(data.projects, function(key, value) {
                wcontent = tbase.replace('%LINK%', value.covers['404']);
                wcontent = wcontent.replace('%IMGSRC%', value.covers['115']);
                wcontent = wcontent.replaceAll('%TITLE%', (value.name).substr(0,behanceTitleLimit) );
                wcontent = wcontent.replace('%TITLEURL%', value.url);
                wcontent = wcontent.replace('%FIELDS%', value.fields);
                wcontent = wcontent.replace('%VIEWS%',  number_format(value.stats.views,0,'.','.') );
                wcontent = wcontent.replace('%APPRE%',  number_format(value.stats.appreciations,0,'.','.') );
                wcontent = wcontent.replace('%COMMENT%',  number_format(value.stats.comments,0,'.','.') );
                $('#slimbehance').append(wcontent);
            });
            $('#slimbehance').append('<a class="tmore">More</a>');
            $("#behancescroll").mCustomScrollbar('update');
            $.cookie('behancepaged',1);
        }
    });
}
function behanceMore() {
    var paged       = parseInt($.cookie('behancepaged')) + 1;
    var url         = "http://www.behance.net/v2/users/"+behanceUsername+"/projects?page="+paged+"&per_page="+behancePer_Page+"&api_key="+behanceAPP_ID;
    var tbase       = behanceContentBase;
    
    if ((ajaxresponse.resbehance === null) || (ajaxresponse.resbehance.state() === 'resolved')) {
        ajaxresponse.resbehance = $.ajax({
            dataType: "jsonp",
            url: url,
            beforeSend: function() {
                $('#slimbehance .tmore').html('<img src="img/loading.gif" />');
            },
            success: function(data) {
                $('#slimbehance .tmore').remove();
                $.each(data.projects, function(key, value) {
                    wcontent = tbase.replace('%LINK%', value.covers['404']);
                    wcontent = wcontent.replace('%IMGSRC%', value.covers['115']);
                    wcontent = wcontent.replaceAll('%TITLE%', (value.name).substr(0, behanceTitleLimit));
                    wcontent = wcontent.replace('%TITLEURL%', value.url);
                    wcontent = wcontent.replace('%FIELDS%', value.fields);
                    wcontent = wcontent.replace('%VIEWS%', number_format(value.stats.views, 0, '.', '.'));
                    wcontent = wcontent.replace('%APPRE%', number_format(value.stats.appreciations, 0, '.', '.'));
                    wcontent = wcontent.replace('%COMMENT%', number_format(value.stats.comments, 0, '.', '.'));
                    $('#slimbehance').append(wcontent);
                });
                if (typeof(data.projects.id) != "undefined") {
                    $('#slimbehance').append('<a class="tmore">More</a>');
                    $("#behancescroll").mCustomScrollbar('update');
                } else {
                    $('#slimbehance .tmore').remove();
                }
                $.cookie('behancepaged', paged);
            }
        });
    }
}
function behanceProfile(){
    var url         = "http://www.behance.net/v2/users/"+behanceUsername+"?api_key="+behanceAPP_ID;
    var tbase       = behanceHeaderBase;
    
    $.ajax({
        dataType: "jsonp",
        url: url,
        success: function(data) {
                wcontent = tbase.replace('%IMGSRC%', data.user.images['50']);
                wcontent = wcontent.replace('%NAME%', data.user.display_name);
                wcontent = wcontent.replace('%LINK%', data.user.url);
                wcontent = wcontent.replace('%OCCUPATION%', data.user.occupation);
                wcontent = wcontent.replace('%VIEWS%', number_format(data.user.stats.views,0,'.','.'));
                wcontent = wcontent.replace('%FOLLOWERS%', number_format(data.user.stats.followers,0,'.','.'));
                wcontent = wcontent.replace('%APPRE%', number_format(data.user.stats.appreciations,0,'.','.'));
                $('#behance').prepend(wcontent);
        }
    });
}

// RSS Function
// ================================================ 
function rssFeed(){
    $('#slimrss').FeedEk({
        FeedUrl: rssUrl,
        MaxCount: rssCount,
        ShowDesc: true,
        ShowPubDate: rssDate,
        DescCharacterLimit: rssLimit,
        RssHeader:"#rss"
    });
}

// Other Function
// ================================================ 
function convertUrl(str) {
    return str.replace(/\b((http|https):\/\/\S+)/g,'<a href="$1" target="_blank">$1</a>');
}
function removeHtml(str) {
    return str.replace(/(<([^>]+)>)/ig,"");
}
function secondsToTime(secs) {
    var hours = Math.floor(secs / (60 * 60));
   
    var divisor_for_minutes = secs % (60 * 60);
    var minutes = Math.floor(divisor_for_minutes / 60);
 
    var divisor_for_seconds = divisor_for_minutes % 60;
    var seconds = Math.ceil(divisor_for_seconds);
   
    var obj = {
        "h": hours,
        "m": minutes,
        "s": seconds
    };
    return obj;
}
function number_format(number, decimals, dec_point, thousands_sep) {
    number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
    var n = !isFinite(+number) ? 0 : +number,
            prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
            sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
            dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
            s = '',
            toFixedFix = function(n, prec) {
        var k = Math.pow(10, prec);
        return '' + Math.round(n * k) / k;
    };
    // Fix for IE parseFloat(0.55).toFixed(0) = 0;
    s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
    if (s[0].length > 3) {
        s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
    }
    if ((s[1] || '').length < prec) {
        s[1] = s[1] || '';
        s[1] += new Array(prec - s[1].length + 1).join('0');
    }
    return s.join(dec);
}
function relativeTime(time) {

    var period = new Date(time);
    var delta = new Date() - period;

    if (delta <= 10000) {	// Less than 10 seconds ago
        return 'Just now';
    }

    var units = null;

    var conversions = {
        millisecond: 1, // ms -> ms
        second: 1000, // ms -> sec
        minute: 60, // sec -> min
        hour: 60, // min -> hour
        day: 24, // hour -> day
        month: 30, // day -> month (roughly)
        year: 12			// month -> year
    };

    for (var key in conversions) {
        if (delta < conversions[key]) {
            break;
        }
        else {
            units = key;
            delta = delta / conversions[key];
        }
    }

    // Pluralize if necessary:

    delta = Math.floor(delta);
    if (delta !== 1) {
        units += 's';
    }
    return [delta, units, "ago"].join(' ');

}
String.prototype.replaceAll = function(str1, str2, ignore){
	return this.replace(new RegExp(str1.replace(/([\/\,\!\\\^\$\{\}\[\]\(\)\.\*\+\?\|\<\>\-\&])/g,"\\$&"),(ignore?"gi":"g")),(typeof(str2)=="string")?str2.replace(/\$/g,"$$$$"):str2);
};