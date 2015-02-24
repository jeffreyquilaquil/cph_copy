/*
 * Author: Ramazan APAYDIN
 * Website: http://ramazanapaydin.com
 * Versiyon: 1.0
*/

/***************************
 * RSS Plugin
 ***************************/
(function(e) {
    e.fn.FeedEk = function(t) {
        var n = {FeedUrl: "http://rss.cnn.com/rss/edition.rss", MaxCount: 5, ShowDesc: true, ShowPubDate: true, CharacterLimit: 0, TitleLinkTarget: "_blank", RssHeader: null};
        if (t) {
            e.extend(n, t);
        }
        var r = e(this).attr("id");
        var i;
        e("#" + r).empty().append('<div style="padding:3px;"></div>');
        e.ajax({url: "http://ajax.googleapis.com/ajax/services/feed/load?v=1.0&num=" + n.MaxCount + "&output=json&q=" + encodeURIComponent(n.FeedUrl) + "&hl=en&callback=?", dataType: "json", success: function(t) {
                e("#" + r).empty();
                var s = "";
                if (n.RssHeader) {
                    $(n.RssHeader).prepend("<div class='item itemheader'><div class='pleft'><img class='header_avatar' src='img/rss.png' width='50' height='50' title='avatar'/></div><div class='pright'><div class='title'><a target='_blank' href='" + t.responseData.feed.feedUrl + "'>" + removeHtml(t.responseData.feed.title) + "</a></div><div>" + removeHtml(t.responseData.feed.description) + "</div></div></div>");
                }
                ;
                e.each(t.responseData.feed.entries, function(e, t) {
                    s += '<div class="item"><div class="itemTitle"><a href="' + t.link + '" target="' + n.TitleLinkTarget + '" >' + removeHtml(t.title) + "</a></div>";
                    if (n.ShowDesc) {
                        if (n.DescCharacterLimit > 0 && t.content.length > n.DescCharacterLimit) {
                            ht = removeHtml(t.content);
                            s += '<div class="itemContent">' + ht.substr(0, n.DescCharacterLimit) + "...</div>"
                        } else {
                            s += '<div class="itemContent">' + removeHtml(t.content) + "</div>";
                        }
                    }
                    if (n.ShowPubDate) {
                        i = new Date(t.publishedDate);
                        s += '<div class="time">' + removeHtml(i.toLocaleDateString()) + "</div>";
                    }
                    s += "</div>";
                });
                e("#" + r).append(s);
            }});
    };
})(jQuery);

function removeHtml(str) {
    return str.replace(/(<([^>]+)>)/ig,"");
}

/***************************
 * jQuery Cookie Plugin
 ***************************/
(function(factory) {
    if (typeof define === 'function' && define.amd) {
        // AMD. Register as anonymous module.
        define(['jquery'], factory);
    } else {
        // Browser globals.
        factory(jQuery);
    }
}(function($) {

    var pluses = /\+/g;

    function raw(s) {
        return s;
    }

    function decoded(s) {
        return decodeURIComponent(s.replace(pluses, ' '));
    }

    function converted(s) {
        if (s.indexOf('"') === 0) {
            // This is a quoted cookie as according to RFC2068, unescape
            s = s.slice(1, -1).replace(/\\"/g, '"').replace(/\\\\/g, '\\');
        }
        try {
            return config.json ? JSON.parse(s) : s;
        } catch (er) {
        }
    }

    var config = $.cookie = function(key, value, options) {

        // write
        if (value !== undefined) {
            options = $.extend({}, config.defaults, options);

            if (typeof options.expires === 'number') {
                var days = options.expires, t = options.expires = new Date();
                t.setDate(t.getDate() + days);
            }

            value = config.json ? JSON.stringify(value) : String(value);

            return (document.cookie = [
                config.raw ? key : encodeURIComponent(key),
                '=',
                config.raw ? value : encodeURIComponent(value),
                options.expires ? '; expires=' + options.expires.toUTCString() : '', // use expires attribute, max-age is not supported by IE
                options.path ? '; path=' + options.path : '',
                options.domain ? '; domain=' + options.domain : '',
                options.secure ? '; secure' : ''
            ].join(''));
        }

        // read
        var decode = config.raw ? raw : decoded;
        var cookies = document.cookie.split('; ');
        var result = key ? undefined : {};
        for (var i = 0, l = cookies.length; i < l; i++) {
            var parts = cookies[i].split('=');
            var name = decode(parts.shift());
            var cookie = decode(parts.join('='));

            if (key && key === name) {
                result = converted(cookie);
                break;
            }

            if (!key) {
                result[name] = converted(cookie);
            }
        }

        return result;
    };

    config.defaults = {};

    $.removeCookie = function(key, options) {
        if ($.cookie(key) !== undefined) {
            // Must not alter options, thus extending a fresh object...
            $.cookie(key, '', $.extend({}, options, {expires: -1}));
            return true;
        }
        return false;
    };

}));


/***************************
 * BootStrap Tabbed JS 
 ***************************/
!function($) {
    "use strict"; // jshint ;_;,

    /* TAB CLASS DEFINITION
     * ==================== */

    var Tab = function(element) {
        this.element = $(element);
    };

    Tab.prototype = {
        constructor: Tab

                , show: function() {
            var $this = this.element
                    , $ul = $this.closest('ul:not(.dropdown-menu)')
                    , selector = $this.attr('data-target')
                    , previous
                    , $target
                    , e;

            if (!selector) {
                selector = $this.attr('href');
                selector = selector && selector.replace(/.*(?=#[^\s]*$)/, ''); //strip for ie7
            }

            if ($this.parent('li').hasClass('active'))
                return;

            previous = $ul.find('.active:last a')[0];

            e = $.Event('show', {
                relatedTarget: previous
            });

            $this.trigger(e);

            if (e.isDefaultPrevented())
                return;

            $target = $(selector);

            this.activate($this.parent('li'), $ul);
            this.activate($target, $target.parent(), function() {
                $this.trigger({
                    type: 'shown'
                            , relatedTarget: previous
                });
            });
        }

        , activate: function(element, container, callback) {
            var $active = container.find('> .active')
                    , transition = callback
                    && $.support.transition
                    && $active.hasClass('fade');

            function next() {
                $active
                        .removeClass('active')
                        .find('> .dropdown-menu > .active')
                        .removeClass('active');

                element.addClass('active');

                if (transition) {
                    element[0].offsetWidth;// reflow for transition
                    element.addClass('in');
                } else {
                    element.removeClass('fade');
                }

                if (element.parent('.dropdown-menu')) {
                    element.closest('li.dropdown').addClass('active');
                }

                callback && callback();
            }

            transition ?
                    $active.one($.support.transition.end, next) :
                    next();

            $active.removeClass('in');
        }
    };


    /* TAB PLUGIN DEFINITION
     * ===================== */

    var old = $.fn.tab;

    $.fn.tab = function(option) {
        return this.each(function() {
            var $this = $(this)
                    , data = $this.data('tab');
            if (!data)
                $this.data('tab', (data = new Tab(this)));
            if (typeof option == 'string')
                data[option]();
        });
    };

    $.fn.tab.Constructor = Tab;


    /* TAB NO CONFLICT
     * =============== */

    $.fn.tab.noConflict = function() {
        $.fn.tab = old;
        return this;
    };


    /* TAB DATA-API
     * ============ */

    $(document).on('click.tab.data-api', '[data-toggle="tab"], [data-toggle="pill"]', function(e) {
        e.preventDefault();
        $(this).tab('show');
    });

}(window.jQuery);


/***************************
 * jQuery Slim Scroll Plugin
 ***************************/
(function(f) {
    jQuery.fn.extend({slimScroll: function(l) {
            var a = f.extend({width: "auto", height: "250px", size: "7px", color: "#000", position: "right", distance: "1px", start: "top", opacity: 0.4, alwaysVisible: !1, disableFadeOut: !1, railVisible: !1, railColor: "#333", railOpacity: 0.2, railDraggable: !0, railClass: "slimScrollRail", barClass: "slimScrollBar", wrapperClass: "slimScrollDiv", allowPageScroll: !1, wheelStep: 20, touchScrollStep: 200}, l);
            this.each(function() {
                function r(d) {
                    if (n) {
                        d = d || window.event;
                        var c = 0;
                        d.wheelDelta && (c = -d.wheelDelta /
                                120);
                        d.detail && (c = d.detail / 3);
                        f(d.target || d.srcTarget).closest("." + a.wrapperClass).is(b.parent()) && g(c, !0);
                        d.preventDefault && !p && d.preventDefault();
                        p || (d.returnValue = !1)
                    }
                }
                function g(d, f, h) {
                    var e = d, g = b.outerHeight() - c.outerHeight();
                    f && (e = parseInt(c.css("top")) + d * parseInt(a.wheelStep) / 100 * c.outerHeight(), e = Math.min(Math.max(e, 0), g), e = 0 < d ? Math.ceil(e) : Math.floor(e), c.css({top: e + "px"}));
                    j = parseInt(c.css("top")) / (b.outerHeight() - c.outerHeight());
                    e = j * (b[0].scrollHeight - b.outerHeight());
                    h && (e = d, d = e / b[0].scrollHeight *
                            b.outerHeight(), d = Math.min(Math.max(d, 0), g), c.css({top: d + "px"}));
                    b.scrollTop(e);
                    b.trigger("slimscrolling", ~~e);
                    s();
                    m();
                }
                function A() {
                    window.addEventListener ? (this.addEventListener("DOMMouseScroll", r, !1), this.addEventListener("mousewheel", r, !1)) : document.attachEvent("onmousewheel", r);
                }
                function t() {
                    q = Math.max(b.outerHeight() / b[0].scrollHeight * b.outerHeight(), B);
                    c.css({height: q + "px"});
                    var a = q == b.outerHeight() ? "none" : "block";
                    c.css({display: a})
                }
                function s() {
                    t();
                    clearTimeout(w);
                    j == ~~j && (p = a.allowPageScroll,
                            x != j && b.trigger("slimscroll", 0 == ~~j ? "top" : "bottom"));
                    x = j;
                    q >= b.outerHeight() ? p = !0 : (c.stop(!0, !0).fadeIn("fast"), a.railVisible && h.stop(!0, !0).fadeIn("fast"))
                }
                function m() {
                    a.alwaysVisible || (w = setTimeout(function() {
                        if ((!a.disableFadeOut || !n) && !u && !v)
                            c.fadeOut("slow"), h.fadeOut("slow")
                    }, 1E3))
                }
                var n, u, v, w, y, q, j, x, B = 30, p = !1, b = f(this);
                if (b.parent().hasClass(a.wrapperClass)) {
                    var k = b.scrollTop(), c = b.parent().find("." + a.barClass), h = b.parent().find("." + a.railClass);
                    t();
                    if (f.isPlainObject(l)) {
                        if ("scrollTo"in l)
                            k =
                                    parseInt(a.scrollTo);
                        else if ("scrollBy"in l)
                            k += parseInt(a.scrollBy);
                        else if ("destroy"in l) {
                            c.remove();
                            h.remove();
                            b.unwrap();
                            return
                        }
                        g(k, !1, !0)
                    }
                } else {
                    a.height = "auto" == a.height ? b.parent().innerHeight() : a.height;
                    k = f("<div></div>").addClass(a.wrapperClass).css({position: "relative", overflow: "hidden", width: a.width, height: a.height});
                    b.css({overflow: "hidden", width: a.width, height: a.height});
                    var h = f("<div></div>").addClass(a.railClass).css({width: a.size, height: "100%", position: "absolute", top: 0, display: a.alwaysVisible &&
                                a.railVisible ? "block" : "none", "border-radius": a.size, background: a.railColor, opacity: a.railOpacity, zIndex: 90}), c = f("<div></div>").addClass(a.barClass).css({background: a.color, width: a.size, position: "absolute", top: 0, opacity: a.opacity, display: a.alwaysVisible ? "block" : "none", "border-radius": a.size, BorderRadius: a.size, MozBorderRadius: a.size, WebkitBorderRadius: a.size, zIndex: 99}), z = "right" == a.position ? {right: a.distance} : {left: a.distance};
                    h.css(z);
                    c.css(z);
                    b.wrap(k);
                    b.parent().append(c);
                    b.parent().append(h);
                    a.railDraggable && c.draggable({axis: "y", containment: "parent", start: function() {
                            v = !0
                        }, stop: function() {
                            v = !1;
                            m()
                        }, drag: function() {
                            g(0, f(this).position().top, !1)
                        }});
                    h.hover(function() {
                        s()
                    }, function() {
                        m()
                    });
                    c.hover(function() {
                        u = !0
                    }, function() {
                        u = !1
                    });
                    b.hover(function() {
                        n = !0;
                        s();
                        m()
                    }, function() {
                        n = !1;
                        m()
                    });
                    b.bind("touchstart", function(a) {
                        a.originalEvent.touches.length && (y = a.originalEvent.touches[0].pageY)
                    });
                    b.bind("touchmove", function(b) {
                        b.originalEvent.preventDefault();
                        b.originalEvent.touches.length && g((y -
                                b.originalEvent.touches[0].pageY) / a.touchScrollStep, !0)
                    });
                    "bottom" === a.start ? (c.css({top: b.outerHeight() - c.outerHeight()}), g(0, !0)) : "top" !== a.start && (g(f(a.start).position().top, null, !0), a.alwaysVisible || c.hide());
                    A();
                    t()
                }
            });
            return this
        }});
    jQuery.fn.extend({slimscroll: jQuery.fn.slimScroll})
})(jQuery);
