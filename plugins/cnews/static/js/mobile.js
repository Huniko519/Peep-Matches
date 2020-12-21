window.ow_newsfeed_const = {};
window.ow_newsfeed_feed_list = {};

var NEWSFEED_Ajax = function( url, data, callback, type ) {
    $.ajax({
        type: type === "POST" ? type : "GET",
        url: url,
        data: data,
        success: callback || $.noop(),
        dataType: "json"
    });
};

var NEWSFEED_MobileFeed = function(autoId, data)
{
	var self = this;
	this.autoId = autoId;
	this.setData(data);

	this.containerNode = $('#' + autoId).get(0);
	this.$listNode = this.$('.owm_newsfeed_list');
        

	this.totalItems = 0;
	this.actionsCount = 0;
        this.allowLoadMore = this.data.data.viewMore;

	this.actions = {};
	this.actionsById = {};

	this.$viewMore = this.$('.feed-load-more');

        $(window).scroll(function( event ) {
            self.tryLoadMore();
        });
        
        self.tryLoadMore();
};

NEWSFEED_MobileFeed.prototype =
{
    setData: function(data) {
        this.data = data;
    },

    adjust: function()
    {
        if ( this.$listNode.find('.owm_newsfeed_item:not(.owm_newsfeed_nocontent)').length )
        {
            this.$listNode.find('.owm_newsfeed_nocontent').hide();
        }
        else
        {
            this.$listNode.find('.owm_newsfeed_nocontent').show();
        }
    },

    reloadItem: function( actionId )
    {
        var action = this.actionsById[actionId];

        if ( !action )
        {
            return false;
        }

        this.loadItemMarkup({actionId: actionId}, function($m){
            $(action.containerNode).replaceWith($m);
        });
    },

    loadItemMarkup: function(params, callback)
    {
            var self = this;

            params.feedData = this.data;
            params = JSON.stringify(params);

            NEWSFEED_Ajax(window.ow_newsfeed_const.LOAD_ITEM_RSP, {p: params}, function( markup ) {
                if ( markup.result === 'error' )
                {
                    return false;
                }

                var $m = $(markup.html);
                callback.apply(self, [$m]);

                self.processMarkup(markup);
            });
    },

    loadNewItem: function(params, preloader, callback)
    {
        if ( typeof preloader === 'undefined' )
        {
            preloader = true;
        }

        var self = this;
        if (preloader)
        {
            var $ph = self.getPlaceholder();
            this.$listNode.prepend($ph);
        }
        
        this.loadItemMarkup(params, function($a) {
            this.$listNode.prepend($a.hide());

            if ( callback )
            {
                callback.apply(self);
            }

            self.adjust();
            if ( preloader )
            {
                var h = $a.height();
                $a.height($ph.height());
                $ph.replaceWith($a.css('opacity', '0.1').show());
                $a.animate({opacity: 1, height: h}, 'fast');
            }
            else
            {
                $a.animate({opacity: 'show', height: 'show'}, 'fast');
            }
        });
    },

    loadList: function( callback )
    {
        var self = this, params = JSON.stringify(this.data);

        NEWSFEED_Ajax(window.ow_newsfeed_const.LOAD_ITEM_LIST_RSP, {p: params}, function( markup ) {
            if ( markup.result === 'error' )
            {
                return false;
            }

            var $m = $(markup.html).filter('.owm_newsfeed_item');
            callback.apply(self, [$m]);

            self.processMarkup(markup);
        });
    },

    tryLoadMore: function() 
    {
        if ( !this.allowLoadMore )
            return;
        
        var self = this;
        
        var diff = $(document).height() - ($(window).scrollTop() + $(window).height());

        if ( diff < 100 )
        {
            this.loadMore();
        }
    },

    loadMore: function(callback)
    {
        var self = this;
        
        function completed()
        {
            var moreCount = self.totalItems - self.actionsCount;
            moreCount = moreCount < 0 ? 0 : moreCount;
            self.$viewMore.find(".feed-more-count").text(moreCount);
            self.$viewMore.css("visibility", "hidden");
            
            self.allowLoadMore = true;
            
            if ( !moreCount ) {
                self.$viewMore.hide();
                self.allowLoadMore = false;
            }
        }
        
        this.allowLoadMore = false;
        this.$viewMore.css("visibility", "visible");
        
        this.loadList(function( $m )
        {
            window.setTimeout(completed);
            self.$listNode.append($m);
            
            if ( callback ) {
                callback.apply(self);
            }
        });
    },

    getPlaceholder: function()
    {
        return $('<div class="owm_newsfeed_placeholder owm_preloader"></div>');
    },

    processMarkup: function( markup )
    {
        if (markup.styleSheets)
        {
            $.each(markup.styleSheets, function(i, o)
            {
                OW.addCssFile(o);
            });
        }

        if (markup.styleDeclarations)
        {
            OW.addCss(markup.styleDeclarations);
        }

        if (markup.beforeIncludes)
        {
            OW.addScript(markup.beforeIncludes);
        }

        if (markup.scriptFiles)
        {

            OW.addScriptFiles(markup.scriptFiles, function()
            {
                if (markup.onloadScript)
                {
                    OW.addScript(markup.onloadScript);
                }
            });
        }
        else
        {
            if (markup.onloadScript)
            {
                OW.addScript(markup.onloadScript);
            }
        }
    },

    /**
    * @return jQuery
    */
    $: function(selector)
    {
        return $(selector, this.containerNode);
    }
};


var NEWSFEED_MobileFeedItem = function(autoId, feed)
{
    this.autoId = autoId;
    this.containerNode = $('#' + autoId).get(0);

    this.feed = feed;
    feed.actionsById[autoId] = this;
    feed.actionsCount++;
    
    feed.lastItem = this;
};

NEWSFEED_MobileFeedItem.prototype =
{
    construct: function(data)
    {
        var self = this;

        this.entityType = data.entityType;
        this.entityId = data.entityId;
        this.id = data.id;
        this.updateStamp = data.updateStamp;

        this.likes = data.likes;

        this.comments = data.comments;
        this.displayType = data.displayType;

        this.$removeBtn = this.$('.newsfeed_remove_btn');
        
        this.$removeBtn.click(function()
        {
            if ( confirm($(this).data("confirm-msg")) )
            {
                self.remove();
            }

            return false;
        });
    },

    remove: function()
    {
        var self = this;

        NEWSFEED_Ajax(window.ow_newsfeed_const.DELETE_RSP, {actionId: this.id}, function( msg ) {
            if ( self.displayType === 'page' )
            {
                if ( msg )
                {
                    OW.info(msg);
                }

                self.$removeBtn.hide();
            }
        }, "POST");
 
        if ( self.displayType !== 'page' )
        {
            $(this.containerNode).remove();
            self.feed.adjust();
        }
    },

    /**
 * @return jQuery
 */
    $: function(selector)
    {
        return $(selector, this.containerNode);
    }
};

NEWSFEED_MobileFeatureLikes = function( entityType, entityId, data ) {
    this.node = $("#" + data.uniqId);
    this.entityType = entityType;
    this.entityId = entityId;
    
    this.btn = $(".owm_newsfeed_control_btn", this.node);
    this.data = data;
    this.liked = data.active;
    
    this.likesInprogress = false;
    
    this.init();
};

NEWSFEED_MobileFeatureLikes.prototype = {
    init: function() {
        var self = this;
        
        this.btn.click(function() {
            self[self.liked ? "unlike" : "like"]();
        });
    },
    
    query: function( rsp ) {
        var self = this;
        
        this.btn[this.liked ? "removeClass" : "addClass"]('owm_newsfeed_control_active');
        this.likesInprogress = true;
        NEWSFEED_Ajax(rsp, {entityType: self.entityType, entityId: self.entityId}, function(c)
        {
            self.likesInprogress = false;
            self.node.find(".owm_newsfeed_control_counter").text(c.count);
            self.liked = !self.liked;
        }, "POST");
    },
    
    like: function() {
        if ( this.likesInprogress ) return;

        if (this.data.error) {
            OW.error(this.data.error);
            return;
        }
        
        this.query(window.ow_newsfeed_const.LIKE_RSP);
    },
    
    unlike: function() {
        if ( this.likesInprogress ) return;
        this.query(window.ow_newsfeed_const.UNLIKE_RSP);
    }
};