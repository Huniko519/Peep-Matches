(function( $, params, factory ){'use strict';
    $(function(){factory.call(this, $, params);}.bind(this));
}).call(this, this.jQuery, this.EMOTICONSPARAMS, function( $, params ){'use strict';

var _ = this,
    emoticonsCodes = _.Object.keys(params.emoticons),
    emoticonsCodePregQuote = emoticonsCodes.map(function(code){return new RegExp('(?:' + preg_quote(code) + ')(?:(?![^<]*?>))', 'ig')});

var Panel = (function( smilePanel )
{
    var panel = {
        panel: smilePanel,
        width: smilePanel.width(),
        height: smilePanel.height(),
        category: 0,
        categoryTopHeight: 0,
        categoryBottomHeight: 0,
        smile: null
    };
    var self = bind(panel);
    
    panel.getPositionInfo = self(function()
    {
        var button = this.smile.button,
            pos = $.extend({}, button.offset(), this.smile.getButtonSize()),
            categoryWidth = this.width, categoryHeight = this.height;
        
        if ( categoriesProperty.hasOwnProperty(this.category) )
        {
            categoryWidth = categoriesProperty[this.category].width;
            categoryHeight = categoriesProperty[this.category].height;
        }
        
        return {
            offset: pos,
            panelWidth: categoryWidth,
            panelHeight: categoryHeight
        };
    });
    panel.setPanelToPosition = fluent(self(function( smile )
    {
        if ( !smile && !this.smile ) return;
        
        this.smile = smile || this.smile;
        var positionInfo = this.getPositionInfo();
        var tabs = $('.smile_tab_container_top,.smile_tab_container_bottom', this.panel).hide();
        var pos = {};
        
        if ( positionInfo.offset.top > ($(_.document).scrollTop() + $(_).height() / 2) )
        {
            tabs.filter('.smile_tab_container_bottom').show();
            pos.top = positionInfo.offset.top - positionInfo.panelHeight - this.categoryTopHeight;
        }
        else
        {
            tabs.filter('.smile_tab_container_top').show();
            pos.top = positionInfo.offset.top + positionInfo.offset.height;
        }
        
        if ( positionInfo.offset.left < ($(_.document).scrollLeft() + $(_).width() / 2) )
        {
            pos.left = positionInfo.offset.left;
        }
        else
        {
            pos.left = positionInfo.offset.left + positionInfo.offset.width - positionInfo.panelWidth;
        }
        
        this.panel.css(pos);
    }));
    panel.showPanel = fluent(self(function()
    {
        this.panel.fadeIn();
    }));
    panel.hidePanel = fluent(self(function( event )
    {
        var target = $(event.target);
        
        if ( !target.hasClass('emoticons') || !target.has('.emoticons') )
        {
            this.panel.hide().css({top: -1000, left: -1000});
        }
    }));
    panel.setSmile = fluent(self(function( smile )
    {
        this.smile = smile;
    }));
    panel.insertSmile = fluent(self(function( text )
    {
        if ( !(this.smile instanceof Smile ) )
        {
            return;
        }
        
        this.smile.insertSmile(text);
    }));
    panel.showCategory = fluent(self(function( id )
    {
        if ( +id < 0 ) return;
        
        $('.smilyes_category', this.panel).hide();
        $('#smilyes-category-' + id).show();
        this.category = id;
        this.setPanelToPosition();
    }));
    
    var categoryTabContainer = $('.smile_tab_container_top,.smile_tab_container_bottom', panel.panel);
    
    panel.categoryTopHeight = categoryTabContainer.filter('.smile_tab_container_top').outerHeight();
    panel.categoryBottomHeight = categoryTabContainer.filter('.smile_tab_container_bottom').outerHeight();
    
    var tabs = $('.emoticons_tab', categoryTabContainer);
    
    if ( tabs.length )
    {
        tabs.on('click', function()
        {
            tabs.removeClass('active');
            $(this).addClass('active');
            panel.showCategory(this.getAttribute('data-category-id'));
        });
        $(tabs.get(0)).trigger('click');
    }
    
    var categories = $('.smilyes_category', smilePanel);
    var categoriesProperty = {};
    
    categories.each(function()
    {
        var self = bind(this);
        var emoticons = this.querySelectorAll('img');
        var imgsCount = emoticons.length - 1;
        var loadedCount = 0;
        
        $(emoticons).on('click', function()
        {
            panel.insertSmile(this.getAttribute('data-code'));
        }).tipTip({
            defaultPosition: 'top',
            attribute: 'data-title'
        });
        
        for ( var i = 0; i <= imgsCount; i++)
        {
            setTimeout(self(function( smile )
            {
                var img = new Image();
                
                img.onload = img.onerror = self(function()
                {
                    loadedCount++;
                    
                    if ( loadedCount === imgsCount )
                    {
                        var $this = $(this);
                        
                        categories.hide();
                        $this.show();
                        categoriesProperty[this.getAttribute('data-category-id')] = {
                            width: $this.outerWidth(),
                            height: $this.outerHeight()
                        };
                        $this.hide();
                    }
                });
                img.src = smile.src;
            }), 1, emoticons[i]);
        }
    });

    $(_.document.all || _.document.getElementsByTagName('*')).on('click.smile', panel.hidePanel);
        
    return panel;
})($(_.document.getElementById('emoticons-panel')));

function Smile( button, textarea )
{
    if ( !(this instanceof Smile) )
    {
        return new Smile(button, textarea);
    }
    
    this.button = $(button);
    this.textarea = $(textarea);
}

Smile.prototype.init = fluent(function()
{
    var self = bind(this);
    
    this.button.on('click.smile', self(function( event )
    {
        Panel.setPanelToPosition(this).showCategory(Panel.category).setSmile(this).showPanel();
        
        event.stopImmediatePropagation();
    }));
});

Smile.prototype.insertSmile = fluent(function( smileCode )
{
    this.textarea.insertText(smileCode);
});

Smile.prototype.getButtonSize = function()
{
    return {
        width: this.button.width(),
        height: this.button.height()
    };
};

_.PEEP.bind('emoticons.attachSmile', function( button, textarea )
{
    Smile(button, textarea).init();
});

var API = {
    onCommentInit: function( entityData )
    {
        var context = this.$context.closest('.peep_comments_mipc,.peep_add_comments_form,.ac_reply_form');

        if ( $('.emoticons_btn', context).length )
        {
            return;
        }

        var button = $('<span>', {"class": 'emoticons_btn emoticons_comment_btn'}), textarea;
        
        if ( !!~['base_profile_wall', 'base_index_wall'].indexOf(this.entityType) )
        {
            textarea = $('#cta' + this.cid);
            $('.peep_attachments', '#comments-' + this.cid).prepend(button);
        }
        else
        {
            textarea = $('textarea', context);
            $('.peep_attachments', context).prepend(button);
        }

        _.PEEP.trigger('emoticons.attachSmile', [button, textarea]);
    },
    integrateSmile: function( textarea, tag, property, prepend )
    {
        var buttons = $(tag, property);
        
        $(prepend).prepend(buttons);
        _.PEEP.trigger('emoticons.attachSmile', [buttons, textarea]);
    },
    contentReplace: function( id, node )
    {
        var dialog = _.document.getElementById(id), nodes;

        if ( dialog === null || (nodes = dialog.querySelectorAll(node)).length === 0 ) return;

        for ( var i = 0, j = nodes.length; i < j; i++ )
        {
            var message = nodes[i];
            var html = message.innerHTML;
            var event = {text: html};

            _.PEEP.trigger('emoticons.replace', event);
            
            if ( event.text !== html )
            {
                message.innerHTML = event.text;
            }
        }
    }
};

_.PEEP.bind('base.comments_list_init', API.onCommentInit);

_.PEEP.bind('base.onFormReady.cnews_update_status', function()
{
    API.integrateSmile(this.elements.status.input, '<span>',
        {"class": 'emoticons_btn emoticons_status_btn'}, $('.buttons', this.form)
    );
});

_.PEEP.bind('base.onFormReady.questions_add', function()
{
    API.integrateSmile(this.elements.question.input, '<span>',
        {href: 'javascript://', "class": 'emoticons_btn emoticons_status_btn emoticons_question'},
        $('.buttons', this.form)
    );
});

_.PEEP.bind('base.onFormReady.photo-edit-form', function()
{
    var button = $('<div>', {"class": 'emoticons_btn emoticons_photo_edit'});
    var textarea = this.elements['photo-desc'];
    var smile = Smile(button, textarea.input);
    var editor = textarea.editor;
    var position = {line: 0, ch: 0};
    
    editor.on('blur', function( editor )
    {
        position = editor.getCursor();
    });
    
    smile.init();
    smile.insertSmile = function( code )
    {
        editor.focus();
        editor.setCursor({line: position.line, ch: position.ch});
        editor.replaceSelection(code);
    };
    
    $('.peep_right', this.form).append(button);
});

_.PEEP.bind('base.initjHtmlArea', function()
{    
    var button = $('<ul><li><span class="emoticons_btn emoticons_wysiwyg"></span></li></ul>');
    var smile = Smile(button, this.textarea[0]);
    
    smile.init();
    smile.insertSmile = function( code )
    {
        this.pasteHTML('<img src="' + params.emoticonsUrl + params.emoticons[code] + '" title="' + code + '" />');
    }.bind(this);
    
    this.toolbar.append(button);
    this.iframe[0].contentWindow.focus();
});

_.PEEP.bind('photo.onRenderUploadSlot', function( editor )
{
    var button = $('<div>', {"class": 'emoticons_btn emoticons_photo'});
    var smile = Smile(button, $('textarea.peep_hidden', this));
    var position = {line: 0, ch: 0};
    
    editor.on('blur', function( editor )
    {
        position = editor.getCursor();
    });
    
    smile.init();
    smile.insertSmile = function( code )
    {
        editor.focus();
        editor.setCursor({line: position.line, ch: position.ch});
        editor.replaceSelection(code);
    };
    
    $('.peep_photo_preview_action', this).append(button);
});

_.PEEP.bind('photo.onBeforeLoadFromCache', function( photoId )
{
    var cmp = this.getPhotoCmp(photoId);
    var html = cmp.photo.description;
    var event = {text: html};
    
    _.PEEP.trigger('emoticons.replace', event);
    cmp.photo.description = event.text;
});

_.PEEP.bind('photo.onSetDescription', function( event )
{
    if ( !event || !event.text || (_.browsePhotoParams && _.browsePhotoParams.listType === 'albums')) return;
    
    _.PEEP.trigger('emoticons.replace', event);
});

_.PEEP.bind('base.onAddConsoleItem', function()
{
    var content = this.find('.peep_console_mailbox_txt');
    var html = content.html();
    var event = {text: html};
    
    _.PEEP.trigger('emoticons.replace', event);
    content.html(event.text);
});

_.PEEP.bind('mailbox.application_started', function()
{
    var MailboxAPI = {
        getDialog: function( convId )
        {
            if ( !_.PEEP.Mailbox.hasOwnProperty('contactManagerView') ||
                !_.PEEP.Mailbox.contactManagerView.hasOwnProperty('dialogs') ||
                !_.PEEP.Mailbox.contactManagerView.dialogs.hasOwnProperty(convId) )
            {
                return null;
            }
            
            return _.PEEP.Mailbox.contactManagerView.dialogs[convId];
        }
    };
    var self = bind(MailboxAPI);
    
    MailboxAPI.attachSmile = self(function( convId, data )
    {
        var dialog;
        
        if ( !(dialog = this.getDialog(convId)) )
        {
            return;
        }
        
        if ( $('.peep_attachments a.emoticons_chat_dialog').length !== 0 )
        {
            return;
        }

        var textarea = $(dialog.textareaControl);
        var button = $('<a>', {"class": 'emoticons_btn emoticons_chat_dialog', style: 'background:url(' + params.btnBackground + ')no-repeat scroll center center;'});
        var offset = _.parseInt(textarea.css('padding-right'), 10) + 22;
        
        dialog.attachmentsBtn.before(button);
        textarea.css('padding-right', offset);
        
        _.PEEP.trigger('emoticons.attachSmile', [button, textarea]);
        textarea.on('keypress', function( event )
        {
            if ( event.which === 13 && !event.shiftKey )
            {
                _.PEEP.trigger('mailbox.dialogLogLoaded', data);
            }
        });
    });
    MailboxAPI.replaceMessage = self(function( messageId )
    {
        var content = $('#messageItem' + messageId);
        var html = content.html();
        var event = {text: html};
        
        _.PEEP.trigger('emoticons.replace', event);
        content.html(event.text);
    });
    MailboxAPI.replaceMessages = self(function( convId )
    {
        var dialog;
        
        if ( !(dialog = this.getDialog(convId)) )
        {
            return;
        }
        
        var messages = dialog.messageListControl[0].querySelectorAll('.peep_dialog_in_item p');
        
        if ( !messages.length )
        {
            return;
        }
        
        for ( var i = 0, j = messages.length; i < j; i++ )
        {
            var message = messages[i];
            var html = message.innerHTML;
            var event = {text: html};

            _.PEEP.trigger('emoticons.replace', event);
            
            if (event.text !== html )
            {
                message.innerHTML = event.text;
            }
        }
    });
    
    _.PEEP.bind('mailbox.dialogLogLoaded', function( data )
    {
        API.contentReplace('main_tab_contact_' + data.opponentId, '.peep_dialog_in_item p');
        _.PEEP.trigger('emoticons.updateScroll', data.opponentId);
    });

    _.PEEP.bind('mailbox.open_dialog', function( data )
    {
        MailboxAPI.attachSmile(data.convId, data);
    });
    
    _.PEEP.bind('mailbox.dialog_opened', function( data )
    {
        MailboxAPI.attachSmile(data.convId, data);
    });
    
    _.PEEP.bind('mailbox.mark_message_read', function( data )
    {
        MailboxAPI.replaceMessages(data.message.convId);
    });

    _.PEEP.bind('mailbox.update_chat_message', function( data )
    {
        MailboxAPI.replaceMessages(data.convId);
    });
    
    _.PEEP.bind('mailbox.update_message', function( data )
    {
        MailboxAPI.replaceMessage(data.message.id);
    });
    
    _.PEEP.bind('mailbox.message_was_read', function( data )
    {
        MailboxAPI.replaceMessage(data.message.id);
    });
    
    try
    {
        var observer = _.PEEP.Mailbox.conversationController.model;
    
        observer.subjectSetSubject.addObserver(function()
        {
            var content = $(_.document.getElementById('conversationSubject'));
            var event = {text: content.html()};

            _.PEEP.trigger('emoticons.replace', event);
            content.html(event.text);
        });
        
        observer.logLoadSubject.addObserver(function()
        {
            API.contentReplace('conversationLog', '.peep_mailbox_message_content,.peep_dialog_in_item p');
        });

        _.PEEP.bind('mailbox.history_loaded', function()
        {
            observer.logLoadSubject.notifyObservers();
        });
        
        observer.modeSetSubject.addObserver(function()
        {
            if ( observer.mode !== 'chat') return;
            
            var content = $('#conversationChatFormBlock');
            
            if ( $('.emoticons_btn', content).length !== 0 ) return;
            
            var textarea = $('textarea', content);
            var button = $('<span>', {"class": 'emoticons_btn emoticons_chat_dialog'});
            var offset = _.parseInt(textarea.css('padding-right'), 10) + 22;
            
            $('#dialogAttachmentsBtn', content).before(button);
            textarea.css('padding-right', offset).keydown(function ( event )
            {
                if (event.which === 13 && !event.shiftKey)
                {
                    API.contentReplace('conversationLog', '.peep_mailbox_message_content,.peep_dialog_in_item p');
                }
            });

            _.PEEP.trigger('emoticons.attachSmile', [button, textarea]);
        });
    }
    catch( e ) { }
});

_.PEEP.bind('mailbox.render_conversation_item', function( item )
{
    var content = item.$el.find('#conversationItemPreviewText');
    var event = {text: content.html()};
    
    _.PEEP.trigger('emoticons.replace', event);
    content.html(event.text);
});

_.PEEP.bind('emoticons.replace', function( data )
{
    if ( !data || !data.text ) return;
    
    var text = data.text;
    
    for ( var i = 0, j = emoticonsCodePregQuote.length; i < j; i++ )
    {
        text = text.replace(emoticonsCodePregQuote[i], function( code )
        {
            return '<img src="' + params.emoticonsUrl + params.emoticons[code] + '" style="width: inherit;" />';
        });
    }
    
    data.text = text;
});

_.PEEP.bind('photo.onBeforeLoadFromCache', function()
{
    _.PEEP.bind('base.comments_list_init', API.onCommentInit);
});

_.PEEP.bind('photo.onFloatboxClose', function()
{
    _.PEEP.bind('base.comments_list_init', API.onCommentInit);
});

// -=============================== UTILS ==================================- \\

var ErrorFactory = {
    getAccessDeniedError: function()
    {
        return new Error('Permission denied');
    },
    getTypeError: function( index, need, object )
    {
        return new TypeError(
            'Invalid Argument Exception. Expects parameter {$index} to be {$need}, {$object} given'
                .replace('{$index}', index)
                .replace('{$need}', need)
                .replace('{$object}', getObjectType(object))
        );
    }
};

function getObjectType( object )
{
    return _.Object.prototype.toString.call(object).slice(8, -1);
}
        
function fluent( f )
{
    return function()
    {
        f.apply(this, arguments);

        return this;
    };
}

function bind( context )
{
    return function( f )
    {
        return function()
        {
            return f.apply(this, arguments);
        }.bind(context);
    };
}

function preg_quote( string, delimiter )
{
    return string.toString().replace(new RegExp('[.\\\\+*?\\[\\^\\]$(){}=!<>|:\\' + (delimiter || '') + '-]', 'g'), '\\$&');
}

$.fn.extend({
    insertText: function( text )
    {
        return this.each(function() 
        {
            if ( _.document.selection && this.tagName === 'TEXTAREA' )
            {
                this.focus();
                var sel = _.document.selection.createRange();
                sel.text = text;
                this.focus();
            }
            else if ( this.selectionStart || this.selectionStart === '0') 
            {
                var startPos = this.selectionStart;
                var endPos = this.selectionEnd;
                var scrollTop = this.scrollTop;
                
                this.focus();
                this.value = this.value.substring(0, startPos) + text + this.value.substring(endPos, this.value.length);

                this.selectionStart = startPos + text.length;
                this.selectionEnd = startPos + text.length;
                this.scrollTop = scrollTop;
            } 
            else 
            {
                this.focus();
                this.value += text;
                this.value = this.value;
            }
        });
    }
});

});

 
(function($){$.fn.tipTip=function(options){var defaults={activation:"hover",keepAlive:false,maxWidth:"200px",edgeOffset:3,defaultPosition:"bottom",delay:400,fadeIn:200,fadeOut:200,attribute:"title",content:false,enter:function(){},exit:function(){}};var opts=$.extend(defaults,options);if($("#tiptip_holder").length<=0){var tiptip_holder=$('<div id="tiptip_holder" style="max-width:'+opts.maxWidth+';"></div>');var tiptip_content=$('<div id="tiptip_content"></div>');var tiptip_arrow=$('<div id="tiptip_arrow"></div>');$("body").append(tiptip_holder.html(tiptip_content).prepend(tiptip_arrow.html('<div id="tiptip_arrow_inner"></div>')))}else{var tiptip_holder=$("#tiptip_holder");var tiptip_content=$("#tiptip_content");var tiptip_arrow=$("#tiptip_arrow")}return this.each(function(){var org_elem=$(this);if(opts.content){var org_title=opts.content}else{var org_title=org_elem.attr(opts.attribute)}if(org_title!=""){if(!opts.content){org_elem.removeAttr(opts.attribute)}var timeout=false;if(opts.activation=="hover"){org_elem.hover(function(){active_tiptip()},function(){if(!opts.keepAlive){deactive_tiptip()}});if(opts.keepAlive){tiptip_holder.hover(function(){},function(){deactive_tiptip()})}}else if(opts.activation=="focus"){org_elem.focus(function(){active_tiptip()}).blur(function(){deactive_tiptip()})}else if(opts.activation=="click"){org_elem.click(function(){active_tiptip();return false}).hover(function(){},function(){if(!opts.keepAlive){deactive_tiptip()}});if(opts.keepAlive){tiptip_holder.hover(function(){},function(){deactive_tiptip()})}}function active_tiptip(){opts.enter.call(this);tiptip_content.html(org_title);tiptip_holder.hide().removeAttr("class").css("margin","0");tiptip_arrow.removeAttr("style");var top=parseInt(org_elem.offset()['top']);var left=parseInt(org_elem.offset()['left']);var org_width=parseInt(org_elem.outerWidth());var org_height=parseInt(org_elem.outerHeight());var tip_w=tiptip_holder.outerWidth();var tip_h=tiptip_holder.outerHeight();var w_compare=Math.round((org_width-tip_w)/2);var h_compare=Math.round((org_height-tip_h)/2);var marg_left=Math.round(left+w_compare);var marg_top=Math.round(top+org_height+opts.edgeOffset);var t_class="";var arrow_top="";var arrow_left=Math.round(tip_w-12)/2;if(opts.defaultPosition=="bottom"){t_class="_bottom"}else if(opts.defaultPosition=="top"){t_class="_top"}else if(opts.defaultPosition=="left"){t_class="_left"}else if(opts.defaultPosition=="right"){t_class="_right"}var right_compare=(w_compare+left)<parseInt($(window).scrollLeft());var left_compare=(tip_w+left)>parseInt($(window).width());if((right_compare&&w_compare<0)||(t_class=="_right"&&!left_compare)||(t_class=="_left"&&left<(tip_w+opts.edgeOffset+5))){t_class="_right";arrow_top=Math.round(tip_h-13)/2;arrow_left=-12;marg_left=Math.round(left+org_width+opts.edgeOffset);marg_top=Math.round(top+h_compare)}else if((left_compare&&w_compare<0)||(t_class=="_left"&&!right_compare)){t_class="_left";arrow_top=Math.round(tip_h-13)/2;arrow_left=Math.round(tip_w);marg_left=Math.round(left-(tip_w+opts.edgeOffset+5));marg_top=Math.round(top+h_compare)}var top_compare=(top+org_height+opts.edgeOffset+tip_h+8)>parseInt($(window).height()+$(window).scrollTop());var bottom_compare=((top+org_height)-(opts.edgeOffset+tip_h+8))<0;if(top_compare||(t_class=="_bottom"&&top_compare)||(t_class=="_top"&&!bottom_compare)){if(t_class=="_top"||t_class=="_bottom"){t_class="_top"}else{t_class=t_class+"_top"}arrow_top=tip_h;marg_top=Math.round(top-(tip_h+5+opts.edgeOffset))}else if(bottom_compare|(t_class=="_top"&&bottom_compare)||(t_class=="_bottom"&&!top_compare)){if(t_class=="_top"||t_class=="_bottom"){t_class="_bottom"}else{t_class=t_class+"_bottom"}arrow_top=-12;marg_top=Math.round(top+org_height+opts.edgeOffset)}if(t_class=="_right_top"||t_class=="_left_top"){marg_top=marg_top+5}else if(t_class=="_right_bottom"||t_class=="_left_bottom"){marg_top=marg_top-5}if(t_class=="_left_top"||t_class=="_left_bottom"){marg_left=marg_left+5}tiptip_arrow.css({"margin-left":arrow_left+"px","margin-top":arrow_top+"px"});tiptip_holder.css({"margin-left":marg_left+"px","margin-top":marg_top+"px"}).attr("class","tip"+t_class);if(timeout){clearTimeout(timeout)}timeout=setTimeout(function(){tiptip_holder.stop(true,true).fadeIn(opts.fadeIn)},opts.delay)}function deactive_tiptip(){opts.exit.call(this);if(timeout){clearTimeout(timeout)}tiptip_holder.fadeOut(opts.fadeOut)}}})}})(jQuery);

// ************************* Begin: Deprecated ************************** \\
(function( $ )
{    
    PEEP.bind('emoticons.renderText', function( data )
    {
        if ( !data || !data.text )
        {
            return;
        }

        data.text = data.text.replace(/\[([^/]+\/[^/]+)\]/ig, '<img src="' + window.emoticonsParams.emoticonsUrl + '$1.gif' + '"/>');
        PEEP.trigger('emoticons.replace', data);
    });

    PEEP.bind('emoticons.addEmoticons', function( data )
    {
        setTimeout(function()
        {
            var keys;

            if ( !data || data !== Object(data) || (keys = Object.keys(data)).length === 0 )
            {
                return;
            }

            var required = ['key', 'textarea', 'toolbar'];

            if ( required.some(function( item )
            {
                return keys.indexOf(item) === -1;
            }) )
            {
                return;
            }

            var textarea = $('form[name="' + data.key + '"] textarea[name="' + data.textarea + '"]');
            var button = $('<div>', {class: 'peep_left shoutbox_bold emoticons_btn', style: 'width: 20px;height: 18px;background-position: -242px -1px;'});

            PEEP.trigger('emoticons.attachSmile', [button, textarea]);

            switch ( data.positioin )
            {
                case 'append':
                    button.appendTo($(data.toolbar));
                    break;
                case 'prepend':
                default:
                    button.prependTo($(data.toolbar));
                    break;
            }
        },1000);
    });
})( jQuery );
