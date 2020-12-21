window.peep_cnews_const = {};
window.peep_cnews_feed_list = {};

var CNEWS_Ajax = function( url, data, callback, type ) {
    $.ajax({
        type: type === "POST" ? type : "GET",
        url: url,
        data: data,
        success: callback || $.noop(),
        dataType: "json"
    });
};

var CNEWS_Feed = function(autoId, data)
{

	var self = this;
	this.autoId = autoId;
	this.setData(data);

	this.containerNode = $('#' + autoId).get(0);
	this.$listNode = this.$('.peep_cnews');

	this.totalItems = 0;
	this.actionsCount = 0;

	this.actions = {};
	this.actionsById = {};


	this.$viewMore = this.$('.peep_cnews_view_more_c');

	this.$viewMore.find('input.peep_cnews_view_more').click(function(){
		var btn = this;
		PEEP.inProgressNode(this);
		self.loadMore(function(){
			PEEP.activateNode(btn);
			if ( self.totalItems > self.actionsCount)
			{
				self.$viewMore.show();
			}
		});
	});

	PEEP.bind('base.comments_list_init', function(p)
        {
		if ( self.actions[p.entityType + '.' + p.entityId] )
		{
			self.actions[p.entityType + '.' + p.entityId].comments = this.totalCount;
			self.actions[p.entityType + '.' + p.entityId].refreshCounter();
		}
	});
};

CNEWS_Feed.prototype =
{
                setData: function(data) {
                    this.data = data;
                },

		adjust: function()
		{
                    if ( this.$listNode.find('.peep_cnews_item:not(.cnews_nocontent)').length )
                    {
                        this.$listNode.find('.cnews_nocontent').hide();
                    }
                    else
                    {
                        this.$listNode.find('.cnews_nocontent').show();
                    }

                    this.$listNode.find('.peep_cnews_item:last-child .cnews-item-delim').hide();
		},

		reloadItem: function( actionId )
		{
			var action = this.actionsById[actionId];

			if ( !action )
			{
				return false;
			}

			this.loadItemMarkup({actionId: actionId,  cycle: action.cycle}, function($m){
				$(action.containerNode).replaceWith($m);
			});
		},

		loadItemMarkup: function(params, callback)
		{
			var self = this;

			params.feedData = this.data;
			params.cycle = params.cycle || {lastItem: false};

			params = JSON.stringify(params);

			CNEWS_Ajax(window.peep_cnews_const.LOAD_ITEM_RSP, {p: params}, function( markup ) {

				if ( markup.result == 'error' )
				{
					return false;
				}

				var $m = $(markup.html);
				callback.apply(self, [$m]);
				PEEP.bindAutoClicks($m);

				self.processMarkup(markup);
			});
		},

		loadNewItem: function(params, preloader, callback)
		{
			if ( typeof preloader == 'undefined' )
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

			CNEWS_Ajax(window.peep_cnews_const.LOAD_ITEM_LIST_RSP, {p: params}, function( markup ) {

				if ( markup.result == 'error' )
				{
					return false;
				}

				var $m = $(markup.html).filter('li');
				callback.apply(self, [$m]);
				PEEP.bindAutoClicks($m);
				self.processMarkup(markup);
			});
		},

		loadMore: function(callback)
		{
			var self = this;
			var li = this.lastItem;

			this.loadList(function( $m )
			{
				var w = $('<li class="cnews_item_tmp_wrapper"></li>').append($m).hide();
				self.$viewMore.hide();
				li.$delim.show();

				self.$listNode.append(w);

				w.slideDown('normal', function() {
					w.before(w.children()).remove();
					if ( callback )
					{
                                            callback.apply(self);
					}
				});
			})
		},

		getPlaceholder: function()
		{
			return $('<li class="peep_cnews_placeholder peep_preloader"></li>');
		},

		processMarkup: function( markup )
		{
                    if (markup.styleSheets)
                    {
                        $.each(markup.styleSheets, function(i, o)
                        {
                            PEEP.addCssFile(o);
                        });
                    }

                    if (markup.styleDeclarations)
                    {
                        PEEP.addCss(markup.styleDeclarations);
                    }

                    if (markup.beforeIncludes)
                    {
                        PEEP.addScript(markup.beforeIncludes);
                    }

                    if (markup.scriptFiles)
                    {

                        PEEP.addScriptFiles(markup.scriptFiles, function()
                        {
                            if (markup.onloadScript)
                            {
                                PEEP.addScript(markup.onloadScript);
                            }
                        });
                    }
                    else
                    {
                        if (markup.onloadScript)
                        {
                            PEEP.addScript(markup.onloadScript);
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
}


var CNEWS_FeedItem = function(autoId, feed)
{
	this.autoId = autoId;
	this.containerNode = $('#' + autoId).get(0);

	this.feed = feed;
	feed.actionsById[autoId] = this;
	feed.actionsCount++;
	feed.lastItem = this;
};

CNEWS_FeedItem.prototype =
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

			this.cycle = data.cycle || {lastItem: false};

			this.$contextMenu = this.$('.peep_cnews_context_menu');

			this.$('.peep_cnews_context_menu_wrap, .peep_cnews_line').hover(function(){
				self.$contextMenu.show();
			}, function(){
				self.$contextMenu.hide();
			});

			this.$commentBtn = this.$('.cnews_comment_btn');
                        this.$commentBtnCont = this.$('.cnews_comment_btn_cont');
                        this.$commentsCont = this.$('.cnews-comments-cont');

			this.$likeBtn = this.$('.cnews_like_btn');
                        this.$likeBtnCont = this.$('.cnews_like_btn_cont');
                        this.likesInprogress = false;

			this.$removeBtn = this.$('.cnews_remove_btn');
			this.$delim = this.$('.cnews-item-delim');

                        this.$attachment = this.$('.cnews_attachment');
                        this.hasAttachment = this.$attachment.length;

                        this.$attachment.find('.cnews_attachment_remove').click(function(){
                            self.$attachment.animate({opacity: 'hide', height: 'hide'}, 'fast', function() {
                                self.$attachment.remove();
                            });

                            CNEWS_Ajax(window.peep_cnews_const.REMOVE_ATTACHMENT, {actionId: self.id}, '', "POST");

                            return false;
                        });

			this.$commentBtn.click(function()
                        {
                            if ( self.$commentBtn.hasClass('cnews_active_button') )
                            {
                                self.hideComments();
                            }
                            else
                            {
                                self.showComments();
                            }

                            return false;
			});

			this.$likeBtn.click(function()
                        {
                            if ( self.$likeBtn.hasClass('cnews_active_button') )
                            {
                                self.unlike();
                            }
                            else
                            {
                                self.like();
                            }

                            return false;
			});

			this.$removeBtn.click(function()
                        {
                            if ( confirm($(this).data("confirm-msg")) )
                            {
                                self.remove();
                            }

                            return false;
			});
		},

		refreshCounter: function()
                {
			var $likes = this.$('.cnews_counter_likes'),
                            $comments = this.$('.cnews_counter_comments');


			$likes.text(parseInt(this.likes));
                        $comments.text(parseInt(this.comments));
		},

		showComments: function()
		{
                    var $c = this.$commentsCont.slideDown('fast');
                    this.$commentBtn.addClass('cnews_active_button');
                    this.$commentBtnCont.addClass('active');

                    $c.show().find('.peep_cnews_comments').show().find('textarea').focus();
		},

                hideComments: function()
		{
                    this.$commentsCont.slideUp('fast');
                    this.$commentBtn.removeClass('cnews_active_button');
                    this.$commentBtnCont.removeClass('active');
		},

		like: function()
		{
                    if (this.$likeBtn.data('error'))
                    {
                        PEEP.error(this.$likeBtn.data('error'));

                        return false;
                    }

                    if ( this.likesInprogress )
                    {
                        return;
                    }

                    var self = this;

                    this.$likeBtn.addClass('cnews_active_button');
                    this.$likeBtnCont.addClass('active');

                    this.likesInprogress = true;
		    CNEWS_Ajax(window.peep_cnews_const.LIKE_RSP, {entityType: self.entityType, entityId: self.entityId}, function(c)
                    {
                        self.likesInprogress = false;

		    	self.likes = parseInt(c.count);
		        self.showLikes(c.markup);
		        self.refreshCounter();
		    }, "POST");
		},

		unlike: function()
		{
                    if ( this.likesInprogress )
                    {
                        return;
                    }

                    var self = this;

                    this.$likeBtn.removeClass('cnews_active_button');
                    this.$likeBtnCont.removeClass('active');

                    this.likesInprogress = true;
 
		    CNEWS_Ajax(window.peep_cnews_const.UNLIKE_RSP, {entityType: self.entityType, entityId: self.entityId}, function(c)
                    {
                        self.likesInprogress = false;

		    	self.likes = parseInt(c.count);
		        self.showLikes(c.markup);
		        self.refreshCounter();
		    }, "POST");
		},

		showLikes: function( likesHtml )
		{
                    var $likes = this.$('.cnews_likes_string');
                    $likes.empty().html(likesHtml);

                    if ( this.likes > 0 )
                    {
                        $likes.show();
                    }
		},

		remove: function()
		{
			var self = this;

                        CNEWS_Ajax(window.peep_cnews_const.DELETE_RSP, {actionId: this.id}, function(msg)
                        {
                            if ( self.displayType == 'page' )
                            {
                                if ( msg )
                                {
                                    PEEP.info(msg);
                                }

                                self.$removeBtn.hide();
                            }
                        }, "POST");

                        if ( self.displayType != 'page' )
                        {
                            $(this.containerNode).animate({opacity: 'hide', height: 'hide'}, 'fast', function()
                            {
                                $(this).remove();

                                self.feed.adjust();
                            });
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
     