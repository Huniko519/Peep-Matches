<?php

class EMOTICONS_CLASS_HtmlDocument 
{
    CONST PATTERN = '/<img[^>]*(?:(?<=src=")(?!.+\/storage-2\/plugins\/emoticons\/images\/.+\/.+\.gif")).*>/i';
    
    private static $classInstance;
    
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }
    
    private $document;
    private $plugin;
    
    private function __construct()
    {
        $this->document = PEEP::getDocument();
        $this->plugin = PEEP::getPluginManager()->getPlugin( 'emoticons' );
    }

    public function replaceBaseWysisyg()
    {
        $js = UTIL_JsGenerator::composeJsString( ';window.emoticonsParams = {"url":{$url}, "emoticonsUrl":{$emoticonsUrl}, "adminRsp":{$rsp}, "label":{$label}};', 
                array('url' => PEEP::getRouter()->urlForRoute('emoticons.smileLoader'),
                      'emoticonsUrl' => $this->plugin->getUserFilesUrl() . 'images/',
                      'rsp' => PEEP::getRouter()->urlForRoute('emoticons.admin-rsp'),
                      'label' => PEEP::getLanguage()->text('emoticons', 'emoticons')) );
        $this->document->addScriptDeclarationBeforeIncludes($js);
        
        if ( PEEP::getPluginManager()->isPluginActive('mailbox') )
        {
            $this->document->addScriptDeclaration(';PEEP.bind("mailbox.update_message", function( data )
            {
                $("#" + "main_tab_contact_" + data.opponentId + " .peep_dialog_in_item p").each(function()
                {
                    $(this).html($(this).html().replace(/\[([^/]+\/[^/]+)\]/ig, "<img src=\'" + window.emoticonsParams.emoticonsUrl + "$1.gif" + "\' />"));
                });
            });');
            
            $this->document->addScriptDeclaration(';PEEP.bind("mailbox.after_write_mail_message", function( data )
            {
                $("#conversationLog .peep_dialog_in_item p").each(function()
                {
                    $(this).html($(this).html().replace(/\[([^/]+\/[^/]+)\]/ig, "<img src=\'" + window.emoticonsParams.emoticonsUrl + "$1.gif" + "\' />"));
                });
                
                $("#conversationLog .peep_mailbox_message_content").each(function()
                {
                    $(this).html($(this).html().replace(/\[([^/]+\/[^/]+)\]/ig, "<img src=\'" + window.emoticonsParams.emoticonsUrl + "$1.gif" + "\' />"));
                });
            });');
            
            $this->document->addOnloadScript(';PEEP.bind("mailbox.update_chat_message", function( data )
            {
                $("#" + "main_tab_contact_" + data.recipientId + " .peep_dialog_in_item p").each(function()
                {
                    $(this).html($(this).html().replace(/\[([^/]+\/[^/]+)\]/ig, "<img src=\'" + window.emoticonsParams.emoticonsUrl + "$1.gif" + "\' />"));
                });
            });', 9999);
            
            $this->document->addOnloadScript(';PEEP.bind("mailbox.message", function( data )
            {
                $("#" + "main_tab_contact_" + data.senderId + " .peep_dialog_in_item p").each(function()
                {
                    $(this).html($(this).html().replace(/\[([^/]+\/[^/]+)\]/ig, "<img src=\'" + window.emoticonsParams.emoticonsUrl + "$1.gif" + "\' />"));
                });
            });', 9999);
            
            $this->document->addOnloadScript(';PEEP.bind("emoticons.updateScroll", function( opponentId )
            {
                try {
                for ( var id in PEEP.Mailbox.contactManagerView.dialogs )
                {
                    var dialog = PEEP.Mailbox.contactManagerView.dialogs[id];
                    
                    if ( dialog.model.opponentId == opponentId )
                    {
                    
                        setTimeout(function(){dialog.scrollDialog();}, 200);
                        break;
                    }
                }
                }
                catch (e){}
            });', 9999);
        }
        
        $handler = PEEP::getRequestHandler()->getHandlerAttributes();
        
        if ( $handler[PEEP_RequestHandler::ATTRS_KEY_CTRL] == 'MAILBOX_CTRL_Messages' && $handler[PEEP_RequestHandler::ATTRS_KEY_ACTION] == 'index' )
        {
            $this->document->addOnloadScript(';
                PEEP.bind("mailbox.update_message", function()
                {
                    $("#conversationLog .peep_dialog_in_item p").each(function()
                    {
                        $(this).html($(this).html().replace(/\[([^/]+\/[^/]+)\]/ig, "<img src=\'" + window.emoticonsParams.emoticonsUrl + "$1.gif" + "\' />"));
                    });
                    
                    $("#conversationLog .peep_mailbox_message_content").each(function()
                    {
                        $(this).html($(this).html().replace(/\[([^/]+\/[^/]+)\]/ig, "<img src=\'" + window.emoticonsParams.emoticonsUrl + "$1.gif" + "\' />"));
                    });
                });

                PEEP.bind("mailbox.conversation_marked_read", function()
                {
                    $("#conversationLog .peep_dialog_in_item p").each(function()
                    {
                        $(this).html($(this).html().replace(/\[([^/]+\/[^/]+)\]/ig, "<img src=\'" + window.emoticonsParams.emoticonsUrl + "$1.gif" + "\' />"));
                    });
                    
                    PEEP.trigger("emoticons.scrollDialog");
                });
                
                PEEP.bind("mailbox.mark_message_read", function()
                {
                    $("#conversationLog .peep_dialog_in_item p").each(function()
                    {
                        $(this).html($(this).html().replace(/\[([^/]+\/[^/]+)\]/ig, "<img src=\'" + window.emoticonsParams.emoticonsUrl + "$1.gif" + "\' />"));
                    });
                    
                    PEEP.trigger("emoticons.scrollDialog");
                });
                
                PEEP.bind("mailbox.history_loaded", function()
                {
                    $(".peep_mailbox_message_content,.peep_dialog_in_item p", "#conversationLog").each(function()
                    {
                        $(this).html($(this).html().replace(/\[([^/]+\/[^/]+)\]/ig, "<img src=\'" + window.emoticonsParams.emoticonsUrl + "$1.gif" + "\' />"));
                    });
                });
                
                PEEP.bind("mailbox.render_conversation_item", function( data )
                {
                    var item = data.$el.find(".peep_mailbox_convers_preview");
                    
                    if ( item.length )
                    {
                        item.html(item.html().replace(/\[([^/]+\/[^/]+)\]/ig, "<img src=\'" + window.emoticonsParams.emoticonsUrl + "$1.gif" + "\' />"));
                    }
                });
                ', 9999);
            
                $this->document->addOnloadScript(';PEEP.bind("emoticons.scrollDialog", function()
                {
                    try 
                    {
                        setTimeout(function(){PEEP.Mailbox.conversationController.scrollDialog();}, 200);
                    }
                    catch (e){}
                });', 9999);
        }
    }
    
    public function replaceBaseComment()
    {
        $this->document->addScriptDeclaration( ';PEEP.bind("base.comments_list_init",function()
            {
                $(".peep_comments_content",this.$context).each(function()
                {
                    var self = $(this);
                    self.html(self.html().replace(/\[([^/]+\/[^/]+)\]/ig,"<img src=\'"+window.emoticonsParams.emoticonsUrl+"$1.gif"+"\'/>"));
                });
            });' );
        $this->document->addScriptDeclaration( ';PEEP.bind("base.comments_list_init",function()
            {
                if(["profile-cover", "avatar-change"].indexOf(this.entityType)!==-1)return;
                var closest=$(this.$context).closest(".peep_cnews_body"),content;
                if ( (content=closest.find(".peep_cnews_body_status")).length )
                {
                    content.html(content.html().replace(/\[([^/]+\/[^/]+)\]/ig,"<img src=\'"+window.emoticonsParams.emoticonsUrl+"$1.gif"+"\'/>"));
                }
                else
                {
                    if ( this.entityType == "photo_comments") return;
                    closest.find(".peep_cnews_content").each(function()
                    {
                        var self=$(this);
                        self.data("origCont",self.html());
                        self.html(self.html().replace(/\[([^/]+\/[^/]+)\]/ig,"<img src=\'"+window.emoticonsParams.emoticonsUrl+"$1.gif"+"\'/>"));
                    });
                }
            });');
        $this->document->addScriptDeclaration( ';PEEP.bind("onChatAppendMessage",function(message)
            {
                var p=message.find("p");
                p.html(p.html().replace(/\[([^/]+\/[^/]+)\]/ig,"<img src=\'"+window.emoticonsParams.emoticonsUrl+"$1.gif"+"\'/>"));
            });' );
        $this->document->addScriptDeclaration( ';PEEP.bind("consoleAddItem",function(items)
            {
                for(var item in items)
                {
                    items[item].html=items[item].html.replace(/\[([^/]+\/[^/]+)\]/ig,"<img src=\'"+window.emoticonsParams.emoticonsUrl+"$1.gif"+"\'/>");
                }
            });');
        $this->document->addScriptDeclaration( ';PEEP.bind("photo.onBeforeLoadFromCache",function(items)
            {
                PEEP.bind("base.comments_list_init",function()
                {
                    $(".peep_comments_content",this.$context).each(function()
                    {
                        var self = $(this);
                        self.data("origCont", self.html());
                        self.html(self.html().replace(/\[([^/]+\/[^/]+)\]/ig,"<img src=\'"+window.emoticonsParams.emoticonsUrl+"$1.gif"+"\'/>"));
                    });
                });
            });
            
            PEEP.bind("photo.onFloatboxClose",function(items)
            {
                PEEP.bind("base.comments_list_init",function()
                {
                    $(".peep_comments_content",this.$context).each(function()
                    {
                        var self = $(this);
                        self.data("origCont", self.html());
                        self.html(self.html().replace(/\[([^/]+\/[^/]+)\]/ig,"<img src=\'"+window.emoticonsParams.emoticonsUrl+"$1.gif"+"\'/>"));
                    });
                });
            });');
    }
}
