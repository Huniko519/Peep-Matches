MAILBOX_ConversationItemView = Backbone.View.extend({
    template: function(data){ return _.template($('#conversationItemPrototypeBlock').html(), data); },

    initialize: function(){
        var self = this;

        this.conversationItemListContainer = $('#conversationItemListContainer');

        this.setElement(this.template(this.model.attributes));

        if (this.model.get('mode') == 'chat'){
            this.$el.attr('id', 'chatItem'+this.model.get('opponentId'));
            this.$el.addClass('chats');
        }
        else{
            this.$el.attr('id', 'conversationItem'+this.model.get('conversationId'));
            this.$el.addClass('mails');

            if (this.model.get('hasAttachment')){
                this.$el.addClass('attach');
            }
        }

        this.$el.bind('click', function(e){
            if ($(e.target).is('input.peep_mailbox_conv_option'))
            {
                //e.preventDefault();
                return;
            }

            if (!PEEP.Mailbox.conversationController.someConversationLoading) {
                PEEP.trigger('mailbox.conversation_item_selected', {
                    convId: self.model.get('conversationId'),
                    opponentId: self.model.get('opponentId')
                });
            }
        });

        this.model.on('remove', this.remove, this);

        this.model.on('change:conversationRead', function(){
            if (this.model.get('conversationRead')){
                this.$el.removeClass('peep_mailbox_convers_info_new');
                this.$el.find('.peep_mailbox_convers_count_new').fadeOut(300);
            }
            else{
                this.$el.addClass('peep_mailbox_convers_info_new');
                this.$el.find('.peep_mailbox_convers_count_new').fadeIn(300);
            }
        }, this);

        this.model.on('change:isSelected', function(){
            if (this.model.get('isSelected')){
                this.$el.addClass('peep_mailbox_convers_info_selected');
            }
            else{
                this.$el.removeClass('peep_mailbox_convers_info_selected');
            }
        }, this);

        this.model.on('change:dateLabel', function(){
            $('#conversationItemDateTime', this.$el).html(this.model.get('dateLabel'));
        }, this);

        this.model.on('change:displayName', function(){
            $('#conversationItemProfileUrl b', this.$el).html(this.model.get('displayName'));
        }, this);

        this.model.on('change:reply', function(){
            if (this.model.get('reply')){
                $('#conversationItemHasReply', this.$el).css('display', 'inline-block');
            }
            else{
                $('#conversationItemHasReply', this.$el).hide();
            }
        }, this);

        this.model.on('change:avatarUrl', function(){
            $('#conversationItemAvatarUrl', this.$el).attr('src', this.model.get('avatarUrl'));
        }, this);

        this.model.on('change:show', function(){
            if (this.model.get('show')){
                this.show();
            }
            else{
                if (this.model._changing && typeof this.model.changed['wasCreatedByScroll'] == 'undefined'){
                    this.hide();
                }
            }
        }, this);

        this.model.on('change:wasCreatedByScroll', function(){
            if (this.model.get('wasCreatedByScroll')){
                this.model.set('show', true);
            }
            else{
                this.model.set('wasCreatedByScroll', true);
//                this.model.set('show', false);
            }
        }, this);

        this.model.on('change:previewText', this.changePreviewText, this);
        this.model.on('change:newMessageCount', this.changeNewMessageCount, this);

        PEEP.bind('mailbox.conversation_item_selected', function(data){

            if (self.model.get('conversationId') == null){
                return;
            }

            if (data.convId != self.model.get('conversationId')){
                if (self.model.get('isSelected')){
                    self.model.set('isSelected', false);
                }
            }
            else{
                if (!self.model.get('isSelected')){
                    self.model.set('isSelected', true);
                }
            }

            //var w = $('#messagesContainerControl').width(); I don't know what what is it ??
            //$('#conversationItemListSub').css('width', 0.359375 * w);
        });

        PEEP.bind('mailbox.conversation_marked_unread', function(data){
            if (data.convId == self.model.get('conversationId')){
                self.model.set('conversationRead', 0);
            }
        });

        PEEP.bind('mailbox.conversation_marked_read', function(data){
            if (data.convId == self.model.get('conversationId')){
                self.model.set('conversationRead', 1);
            }
        });

        PEEP.bind('mailbox.conversation_deleted', function(data){
            if (data.convId == self.model.get('conversationId')){
                self.$el.remove();
                PEEP.updateScroll( $('#conversationItemListContainer') );
                var user = PEEP.Mailbox.usersCollection.findWhere({convId: data.convId});
                if (user){
                    user.set('convId', 0);
                }
            }
        });

        PEEP.bind('mailbox.message', function(message){
            if (message.convId != self.model.get('conversationId')){
                return;
            }

            PEEP.trigger('mailbox.set_no_conversation', {show: false});

            self.model.set('lastMessageTimestamp', message.timeStamp);
            self.model.set('dateLabel', message.dateLabel);

            if (self.model.get('mode') == 'chat'){
                if (!message.readMessageAuthorized){
                    self.model.set('previewText', message.previewText);
                }
                else{

                    var previewText = message.text;
                    if (previewText.length > 50)
                    {
                        previewText = previewText.substr(0, 50)+'...';
                    }

                    self.model.set('previewText', previewText);
                }
            }

            self.conversationItemListContainer.find('#conversationItemListSub').prepend(self.$el);
//            self.conversationItemListContainer.find('.jspPane').prepend(self.$el);
        });

        PEEP.bind('mailbox.conversation_data_received_for_'+self.model.get('opponentId'), function(data){
            self.model.set('conversationId', data.conversationId);
            self.model.set('isSelected',true);
        });

        PEEP.bind('mailbox.message_was_authorized', function(message){
            if (message.convId != self.model.get('conversationId')){
                return;
            }

            if (self.model.get('mode') != 'chat'){
                return;
            }

            if (message.timeStamp != self.model.get('lastMessageTimestamp')){
                return;
            }

            self.model.set('previewText', message.text);
        });

        this.$el.data( self.model );
    },

    render: function(){

        this.changePreviewText();

        if (this.model.get('newMessageCount')>0){
            this.$el.find('.peep_mailbox_convers_count_new').html( PEEP.getLanguageText('mailbox', 'new_message_count', {count: this.model.get('newMessageCount')}) );
        }
        else{
            this.$el.find('.peep_mailbox_convers_count_new').html('');
        }
        if (this.model.get('conversationRead') == 0){
            this.$el.addClass('peep_mailbox_convers_info_new');
        }

        if (this.model.get('show')){
            this.$el.show();
        }
        else{
            this.$el.hide();
        }

        PEEP.bindTips(this.$el);

        return this;
    },

    hide: function(){
        this.$el.hide();
    },

    show: function(){
        this.$el.show();
    },

    remove: function(){
        this.$el.remove();
    },

    changeNewMessageCount: function(){
        if (this.model.get('newMessageCount')>0){
            this.model.set('conversationRead', 0);
            if (!this.model.get('conversationViewed'))
            {
                this.model.set('conversationViewed', false);
            }
            this.$el.find('.peep_mailbox_convers_count_new').html( PEEP.getLanguageText('mailbox', 'new_message_count', {count: this.model.get('newMessageCount')}) );
        }
        else{
            this.model.set('conversationRead', 1);
            this.model.set('conversationViewed', true);
            this.$el.find('.peep_mailbox_convers_count_new').html('');
        }
    },

    changePreviewText: function(){
        if (this.model.get('mode') == 'mail'){
            $('#conversationItemPreviewText', this.$el).html( PEEP.getLanguageText('mailbox', this.model.get('mode')+'_subject_prefix') + this.model.get('subject') );
        }

        if (this.model.get('mode') == 'chat'){
            $('#conversationItemPreviewText', this.$el).html( this.model.get('previewText') );
        }

        PEEP.trigger('mailbox.render_conversation_item', this);
    }
});

MAILBOX_ConversationListModel = Backbone.Model.extend({
    defaults: {
        latestConvId: 0,
        activeConvId: null,
        selectedOpponentId: null,
        loadedConvCount: 0,
        pageConvId: null
    },

    loadList: function(numberToLoad){

        var numberOfConvToLoad = numberToLoad || 10; //TODO this is hardcode
        var n =this.get('loadedConvCount') + numberOfConvToLoad;
        if (n > PEEP.Mailbox.conversationsCollection.length){
            n = PEEP.Mailbox.conversationsCollection.length;
        }

        for (var i=this.get('loadedConvCount'); i<n; i++){
            var conversation = PEEP.Mailbox.conversationsCollection.models[i];

            conversation.set('wasCreatedByScroll', true);
        }
        this.set('loadedConvCount', i);
    },

    loadMore: function(options){

        var self = this;

        if (options.searching){

            var ajaxData = {};
            ajaxData['actionData'] = {
                'uniqueId': PEEPMailbox.uniqueId('loadMoreConversations'),
                'name': 'loadMoreConversations',
                'data': {
                    'searching': 1,
                    'from': $('.peep_mailbox_convers_info', $('#conversationItemListSub')).length,
                    'kw': $('#conversation_search').val()
                }
            };
            ajaxData['actionCallbacks'] = {
                success: function(data){

                    if ( typeof data != 'undefined' ){
                        for (var i=0; i<data.length; i++){
                            data[i]['wasCreatedByScroll'] = true;
                            data[i]['show'] = true;
//                            PEEP.Mailbox.conversationsCollection.add(data[i]);

                            var conv = new MAILBOX_Conversation(data[i]);
                            options.createItem(conv);
//                            conv.show();
                        }

//                        var loadedConvCount = self.get('loadedConvCount');
//                        self.set('loadedConvCount', loadedConvCount + data.length);
                        PEEP.updateScroll($('#conversationItemListContainer'));
                    }
                },
                error: function(e){
                    PEEPMailbox.log(e);
                    PEEP.Mailbox.sendInProcess = false;
                },
                complete: function(){
                    PEEP.Mailbox.sendInProcess = false;
                    options.listLoadInProgress = false;
                }
            }

            PEEP.Mailbox.sendData(ajaxData);

        }
        else{
            var ajaxData = {};
            ajaxData['actionData'] = {
                'uniqueId': PEEPMailbox.uniqueId('loadMoreConversations'),
                'name': 'loadMoreConversations',
                'data': {
                    'from': PEEP.Mailbox.conversationsCollection.length
                }
            };
            ajaxData['actionCallbacks'] = {
                success: function(data){

                    if ( typeof data != 'undefined' ){
                        for (var i=0; i<data.length; i++){
                            data[i]['wasCreatedByScroll'] = true;
                            data[i]['show'] = true;
                            PEEP.Mailbox.conversationsCollection.add(data[i]);
                        }

                        var loadedConvCount = self.get('loadedConvCount');
                        self.set('loadedConvCount', loadedConvCount + data.length);
                        PEEP.updateScroll($('#conversationItemListContainer'));
                    }
                },
                error: function(e){
                    PEEPMailbox.log(e);
                    PEEP.Mailbox.sendInProcess = false;
                },
                complete: function(){
                    PEEP.Mailbox.sendInProcess = false;
                    options.listLoadInProgress = false;
                    if (self.get('pageConvId') != null){
                        options.loadMoreToConversationId(self.get('pageConvId'));
                    }
                }
            }

            PEEP.Mailbox.sendData(ajaxData);
        }
    }
});

MAILBOX_ConversationListView = Backbone.View.extend({
    initialize: function(){
        var self = this;

        PEEP.Mailbox.conversationsCollection.on('add', this.createItem, this);
        PEEP.Mailbox.conversationsCollection.on('change', this.onListChange, this);
        PEEP.Mailbox.conversationsCollection.on('remove', this.onListChange, this);

        this.listLoadInProgress = false;

        this.messagesPageControl = $('#messagesContainerControl');
        this.conversationItemListWrapper  =  $('#conversationItemListContainer');
        this.conversationItemListContainer = $('#conversationItemListContainer');
        this.syncing = false;
        this.searching = false;

        this.preloaderControl = $('#conversationListControl');

        PEEP.addScroll(this.conversationItemListWrapper);

        $(document).ready(function(){
            self.searchFormElement = new SearchField("conversation_search", "conversation_search", PEEP.getLanguageText('mailbox', 'label_invitation_conversation_search'));
            self.searchFormElement.setHandler(self);
        });

        this.conversationItemListWrapper.bind('jsp-scroll-y', function(event, scrollPositionY, isAtTop, isAtBottom){
//            if (self.model.get('loadedConvCount') < PEEP.Mailbox.conversationsCollection.length && !self.listLoadInProgress && isAtBottom){
            if (self.searching && !self.listLoadInProgress && isAtBottom){
                self.listLoadInProgress = true;
                self.model.loadMore(self);
            }
            else
            {
                if (PEEP.Mailbox.conversationsCollection.length < PEEP.Mailbox.conversationsCount && !self.listLoadInProgress && isAtBottom){
                    self.listLoadInProgress = true;
                    self.model.loadMore(self);
                }
            }
        });

        $('#closeBulkOptionsBtn').click(function(e){
            self.preloaderControl.removeClass('peep_mailbox_bulk_options');
            return false;
        });

        $('#openBulkOptionsBtn').click(function(){
            self.preloaderControl.addClass('peep_mailbox_bulk_options');
        });

        $('#mailboxConvOptionSelectAll').click(function(){
            if ($(this).prop('checked')) {

                $('.peep_mailbox_conv_option:visible').prop('checked', true);
            }
            else {
                $('.peep_mailbox_conv_option').prop('checked', false);

            }
        });

        $('#mailboxConvOpenActions').click(function(){
            $('mailboxConvOpenActionsContainer').toggleClass('peep_hidden');
        });

        $('#mailboxConvActionMarkUnread').click(function(){

            var list = $('.peep_mailbox_conv_option:checked');
            var convIdList = [];
            _.each(list, function (checkbox) {
                convIdList.push( $(checkbox).attr('id').replace('conversation_', '') );
            });

            self.bulkAction(convIdList, 'markUnread');
        });

        $('#mailboxConvActionMarkRead').click(function(){

            var list = $('.peep_mailbox_conv_option:checked');
            var convIdList = [];
            _.each(list, function (checkbox) {
                convIdList.push( $(checkbox).attr('id').replace('conversation_', '') );
            });

            self.bulkAction(convIdList, 'markRead');
        });

        $('#mailboxConvActionDelete').click(function(){

            var list = $('.peep_mailbox_conv_option:checked');
            var convIdList = [];
            _.each(list, function (checkbox) {
                convIdList.push( $(checkbox).attr('id').replace('conversation_', '') );
            });

            self.bulkAction(convIdList, 'delete');
        });

        PEEP.bind('mailbox.menu_mode_changed', function(data){
            self.model.set('mode', data.mode);
        });

        PEEP.bind('mailbox.conversation_item_selected', function(data){
            self.model.set('activeConvId', data.convId);
            self.model.set('selectedOpponentId', data.opponentId);
        });

        PEEP.bind('mailbox.conversation_deleted', function(data){

            var deletedItem = PEEP.Mailbox.conversationsCollection.findWhere({conversationId: data.convId});

            if (PEEP.Mailbox.conversationsCollection.length > 1){
                var nextItem = PEEP.Mailbox.conversationsCollection.at(PEEP.Mailbox.conversationsCollection.indexOf(deletedItem) + 1);
                if (!nextItem){
                    nextItem = PEEP.Mailbox.conversationsCollection.at(PEEP.Mailbox.conversationsCollection.indexOf(deletedItem) - 1);
                }

                PEEP.trigger('mailbox.conversation_item_selected', {convId: nextItem.get('conversationId'), opponentId: nextItem.get('opponentId')});
                self.model.loadList(1);
            }

//            PEEP.Mailbox.conversationsCollection.remove({conversationId: data.convId});
            PEEP.Mailbox.conversationsCollection.remove(deletedItem);

            if (PEEP.Mailbox.conversationsCollection.length == 0){
                self.showNoConversation();
            }
        });

        PEEP.bind('mailbox.set_no_conversation', function(data){
            if (data.show){
                self.showNoConversation();
            }
            else{
                self.hideNoConversation();
            }
        });

        PEEP.bind('mailbox.application_started', function(){

            if (PEEP.Mailbox.conversationsCollection.length > 0){
                self.hideNoConversation();
                self.model.loadList();
                if (self.model.get('pageConvId') != null){
                    if (!PEEP.Mailbox.conversationsCollection.findWhere({conversationId: self.model.get('pageConvId')})){
                        self.model.loadMore(self);
                    }
                }
            }
            else{
                self.showNoConversation();
            }

            self.hidePreloader();
        });
    },

    loadMoreToConversationId: function(conversationId){
        if (!PEEP.Mailbox.conversationsCollection.findWhere({conversationId: this.model.get('pageConvId')}))
        {
            this.model.loadMore(this);
        }
        else
        {
            //var jsp = this.conversationItemListWrapper.data('jsp');
            //jsp.scrollToBottom();
        }
    },

    bulkAction: function(convIdList, actionName){

        var ajaxData = {};
        ajaxData['actionData'] = {
            'uniqueId': PEEPMailbox.uniqueId('bulkActions'),
            'name': 'bulkActions',
            'data': {
                'convIdList': convIdList,
                'actionName': actionName
            }
        };
        ajaxData['actionCallbacks'] = {
            success: function(data){
                if ( typeof data != 'undefined' )
                {
                    if (actionName == 'markUnread'){
                        _.each(convIdList, function (id) {
                            PEEP.trigger('mailbox.conversation_marked_unread', {convId: id});
                        });
                    }

                    if (actionName == 'markRead'){
                        _.each(convIdList, function (id) {
                            PEEP.trigger('mailbox.conversation_marked_read', {convId: id});
                        });
                    }

                    if (actionName == 'delete'){
                        _.each(convIdList, function (id) {
                            PEEP.trigger('mailbox.conversation_deleted', {convId: parseInt(id)});
                        });
                    }

                    if (data.message){
                        PEEP.info(data.message);
                    }

                    $('.peep_mailbox_conv_option').prop('checked', false);
                    $('#mailboxConvOptionSelectAll').prop('checked', false);
                }
            },
            error: function(e){
                PEEPMailbox.log(e);
                PEEP.Mailbox.sendInProcess = false;
            },
            complete: function(){
                PEEP.Mailbox.sendInProcess = false;
            }
        };

        PEEP.Mailbox.addAjaxData(ajaxData);

        PEEP.Mailbox.sendData();
    },

    hideNoConversation: function(){
        $('#conversationItem-noitems', this.conversationItemListContainer).remove();
        this.messagesPageControl.removeClass('peep_mailbox_table_empty');
    },

    showNoConversation: function(){

        if ($('#conversationItem-noitems', this.conversationItemListContainer).length == 0){
            var noConversationItem = $($('#conversationListNoContentPrototypeBlock').html());
            noConversationItem.attr('id', 'conversationItem-noitems');
            this.conversationItemListContainer.append(noConversationItem);

            this.messagesPageControl.addClass('peep_mailbox_table_empty');
            $('#conversationContainer').removeClass('peep_mailbox_right_loading');
        }
    },

    showConversationNotFound: function(){
        var conversationNotFound = $($('#conversationNotFoundPrototypeBlock').html());

    },

    onListChange: function(){
        if (PEEP.Mailbox.conversationsCollection.length > 0){
            this.hideNoConversation();
        }
        else{
            this.showNoConversation();
        }
        this.hidePreloader();
    },

    createItem: function(conversation){

        var conversationItemView = new MAILBOX_ConversationItemView({model: conversation});

        var itemIndex;
        itemIndex = PEEP.Mailbox.conversationsCollection.indexOf(conversation);

        if (this.conversationItemListContainer.find('#conversationItemListSub').length > 0){
            if (itemIndex == 0){
                this.conversationItemListContainer.find('#conversationItemListSub').prepend(conversationItemView.render().$el);
                conversation.set('wasCreatedByScroll', true);
            }
            else{
                this.conversationItemListContainer.find('#conversationItemListSub').append(conversationItemView.render().$el);
            }
        }

        if (this.model.get('activeConvId') == conversation.get('conversationId')){
            PEEP.trigger('mailbox.conversation_item_selected', {convId: conversation.get('conversationId'), opponentId: conversation.get('opponentId')});
        }
        else{
            if (this.model.get('activeConvId') == null && this.model.get('loadedConvCount') == 0 && itemIndex == 0){
                PEEP.trigger('mailbox.conversation_item_selected', {convId: conversation.get('conversationId'), opponentId: conversation.get('opponentId')});
            }
        }

        PEEP.trigger('mailbox.render_conversation_item', conversationItemView);
    },

    showPreloader: function(){
        this.preloaderControl.addClass('peep_mailbox_left_loading');
    },

    hidePreloader: function(){
        this.preloaderControl.removeClass('peep_mailbox_left_loading');
        PEEP.updateScroll(this.conversationItemListWrapper);
    },

    updateList: function(name){

        var self = this;

        self.model.set('loadedConvCount', 0);
        $('#conversationItemListSub').html('');

        if (name == ''){

            self.searching = false;

            self.model.loadList(PEEP.Mailbox.conversationsCollection.length);

            for (var i=0; i<PEEP.Mailbox.conversationsCollection.length; i++){
                var conv = PEEP.Mailbox.conversationsCollection.models[i];

                self.createItem(conv);
                conv.show();
            }

//            for (var i=0; i<PEEP.Mailbox.conversationsCollection.length; i++){
//                var item = PEEP.Mailbox.conversationsCollection.models[i];
//                if (item.get('wasCreatedByScroll'))
//                {
//                    item.show();
//                }
//                else
//                {
//                    item.hide();
//                }
//            }
            $('.peep_btn_close_search').removeClass('peep_preloader');
        }
        else{

            if (name.length < 2)
            {
                return;
            }

            self.searching = true;
//            var expr = new RegExp('(^'+name+'.*)|(\\s'+name+'.*)', 'i');
//
//            var subjExpr = new RegExp('(^'+name+'.*)|(\\s'+name+'.*)', 'i');
//
//            for (var i=0; i<PEEP.Mailbox.conversationsCollection.length; i++){
//                var item = PEEP.Mailbox.conversationsCollection.models[i];
//
//                if (item.get('mode') == 'mail'){
//                    if ( !expr.test(item.get('displayName')) && !subjExpr.test(item.get('previewText')) ){
//                        item.hide();
//                    }
//                    else{
//                        item.show();
//                    }
//                }
//
//                if (item.get('mode') == 'chat'){
//                    if ( !expr.test(item.get('displayName')) ){
//                        item.hide();
//                    }
//                    else{
//                        item.show();
//                    }
//                }
//            }

            if (!self.syncing){
                $('.peep_btn_close_search').addClass('peep_preloader');
                self.syncing = true;

                setTimeout(function(){

                    var kw = $('#conversation_search').val();
                    self.lastSearchedKeyword = $('#conversation_search').val();

                    $.getJSON(PEEPMailbox.userSearchResponderUrl, {term: kw, idList: {}, context: 'conversation'}, function( data ) {

                        if ($('#conversation_search').val() != data.kw)
                        {
                            self.syncing = false;
                            self.updateList($('#conversation_search').val());
                        }
                        else {
                            _.each(data.list, function (conversation) {

                                if ($('#conversationItem' + conversation.data.conversationId).length == 0) {
                                    var conv = new MAILBOX_Conversation(conversation.data);
                                    self.createItem(conv);
                                    PEEP.Mailbox.conversationsCollection.add(conversation.data);
                                    conv.show();
                                }

                            });

                            PEEP.updateScroll(self.conversationItemListWrapper);
                            self.syncing = false;
                            $('.peep_btn_close_search').removeClass('peep_preloader');
                        }
                    });

                }, 500);
            }
        }

        PEEP.updateScroll(this.conversationItemListWrapper);
    }

});

MAILBOX_ConversationModel1 = Backbone.Model.extend({
    defaults: {}
});

MAILBOX_ConversationModel = function () {
    var self = this;

    this.convId = null;
    this.opponentId = null;
    this.mode = '';
    this.status = '';
    this.firstMessageId = null;
    this.lastMessageTimestamp = 0;
    this.isLogLoaded = false;
    this.displayName = false;
    this.subject = false;
    this.profileUrl = false;
    this.avatarUrl = false;
    this.isSuspended = false;

    this.conversationIdSetSubject = PEEPMailbox.makeObservableSubject();
    this.opponentIdSetSubject = PEEPMailbox.makeObservableSubject();
    this.modeSetSubject = PEEPMailbox.makeObservableSubject();
    this.statusUpdateSubject = PEEPMailbox.makeObservableSubject();
    this.lastMessageTimestampSetSubject = PEEPMailbox.makeObservableSubject();
    this.logLoadSubject = PEEPMailbox.makeObservableSubject();
    this.displayNameSetSubject = PEEPMailbox.makeObservableSubject();
    this.subjectSetSubject = PEEPMailbox.makeObservableSubject();
    this.profileUrlSetSubject = PEEPMailbox.makeObservableSubject();
    this.avatarUrlSetSubject = PEEPMailbox.makeObservableSubject();
    this.isSuspendedSetSubject = PEEPMailbox.makeObservableSubject();
};

MAILBOX_ConversationModel.prototype = {

    setConversationId: function(value){
        this.convId = value;
        this.conversationIdSetSubject.notifyObservers();
    },

    setOpponentId: function(value){
        this.opponentId = value;
        this.opponentIdSetSubject.notifyObservers();
    },

    setMode: function(value){
        this.mode = value;
        this.modeSetSubject.notifyObservers();
    },

    setStatus: function(value){
        this.status = value;
        this.statusUpdateSubject.notifyObservers();
    },

    setLastMessageTimestamp: function(value){
        this.lastMessageTimestamp = value;
        this.lastMessageTimestampSetSubject.notifyObservers();
    },

    setIsLogLoaded: function(value){
        this.isLogLoaded = value;
        this.logLoadSubject.notifyObservers();
    },

    setDisplayName: function(value){
        this.displayName = value;
        this.displayNameSetSubject.notifyObservers();
    },

    setSubject: function(value){
        this.subject = value;
        this.subjectSetSubject.notifyObservers();
    },

    setProfileUrl: function(value){
        this.profileUrl = value;
        this.profileUrlSetSubject.notifyObservers();
    },

    setAvatarUrl: function(value){
        this.avatarUrl = value;
        this.avatarUrlSetSubject.notifyObservers();
    },

    setIsSuspended: function(value, message){
        this.isSuspended = value;
        this.suspendReasonMessage = message;
        this.isSuspendedSetSubject.notifyObservers();
    }
};

MAILBOX_ConversationView = function () {
    var self = this;

    this.conversation = new MAILBOX_Conversation();
    this.conversation.get('messages').on('add', this.messageWrite, this);

    this.model = new MAILBOX_ConversationModel();
    this.control = $('#conversationContainer');
    this.preloaderControl = $('#conversationContainer');
    this.historyLoadAllowed = false;
    this.uid = 'mailboxConversationAttachmentsPreviewContainer';
    this.hasLinkObserver = false;
    this.embedLinkDetected = false;
    this.embedLinkResult = true;
    this.embedAttachmentsValue = '';
    this.embedAttachmentsObject = {};
    this.autolinkEnabled = true;

    this.construct();

    $(document).click(function( e ){
        if ( !$(e.target).is(':visible') ){
            return;
        }

        var isTarget = self.settingsBlock.is(e.target) || self.settingsBlock.find(e.target).length;
        var isBtn = self.settingsBtn.is(e.target) || self.settingsBtn.find(e.target).length;
        if ( !isTarget && !isBtn ){
            self.hideSettingsBlock();
        }

        var isTextarea = $('#conversationMessageFormBlock', self.control).is(e.target) || $('#conversationMessageFormBlock', self.control).find(e.target).length || $('.floatbox_container').find(e.target).length;
        if (!isTextarea){
            //$('#conversationMessageFormBlock').removeClass('continue');

            if (self.textareaControl && self.textareaControl.val() != ''){
                $('#conversationMessageFormBlock').removeClass('active');
                $('#conversationMessageFormBlock').addClass('continue');
                $('.peep_mailbox_log').removeClass('textarea_active');
                var text = $('<div>'+self.textareaControl.val()+'</div>').text();
                var words = text.split(' ');
                var fiveWords = words.slice(-5);
                var textPreview = fiveWords.join(' ');
                $('#fake_conversationTextarea').val(textPreview);
            }
            else
            {
                $('#conversationMessageFormBlock').removeClass('active');
                $('.peep_mailbox_log').removeClass('textarea_active');
            }
            PEEP.updateScroll(self.messageListControl);
        }
    });

    this.bindTextareaControlEvents = function(){

        this.textareaControl.keyup(function(ev){
            var storage = PEEPMailbox.getStorage();
            storage.setItem('mailbox.conversation' + self.model.convId + '_form_message', $(this).val());
        });

        if (this.textareaControl.length > 0){
            if (self.model.mode == 'chat'){
                this.textareaControl.dialogAutosize(self);
            }
            else
            {
                //TODO autosize mail wysiwyg textarea
            }
        }
    }

    this.setConversationId = function(params){
        self.showPreloader();

        if (self.someConversationLoading == 1 || self.someConversationLoading == 2){
            self.someConversationLoading = 2;
        }
        else
        {
            self.someConversationLoading = 1;
        }

        self.reset();

        this.conversation = PEEP.Mailbox.conversationsCollection.findWhere({conversationId: params.convId});

        var ajaxData = {};
        ajaxData['actionData'] = {
            'uniqueId': PEEPMailbox.uniqueId('getLog'),
            'name': 'getLog',
            'data': {
                'convId': params.convId,
                'opponentId': params.opponentId,
                'markRead': true
            }
        };
        ajaxData['actionCallbacks'] = {
            success: function(data){

                if (self.someConversationLoading == 2){
                    return;
                }

                if ( typeof data != 'undefined' )
                {
                    self.model.setConversationId(data.conversationId);
                    self.model.setOpponentId(data.opponentId);
                    self.model.setMode(data.mode);
                    self.model.setDisplayName(data.displayName);
                    self.model.setSubject(data.subject);
                    self.model.setProfileUrl(data.profileUrl);
                    self.model.setAvatarUrl(data.avatarUrl);
                    self.model.setStatus(data.status);
                    self.model.setIsSuspended(data.isSuspended, data.suspendReasonMessage);

                    delete peepFileAttachments[self.uid];

                    var newUid = '';
                    if (data.mode == 'chat'){
                        $('#conversationChatFormBlock #dialogAttachmentsBtn').removeClass('uploading');

                        $('#dialogAttachmentsBtn').find('.mlt_file_input').remove();

                        newUid = PEEPMailbox.uniqueId('mailbox_dialog_'+self.model.convId+'_'+self.model.opponentId+'_');

                        $('.mailboxConversationAttachmentsPreviewContainer').attr('id', newUid);

                        peepFileAttachments[newUid] = new PEEPFileAttachment({
                            'uid': newUid,
                            'submitUrl': PEEPMailbox.attachmentsSubmitUrl,
                            'deleteUrl': PEEPMailbox.attachmentsDeleteUrl,
                            'showPreview': false,
                            'selector': '#conversationChatFormBlock #dialogAttachmentsBtn',
                            'pluginKey': 'mailbox',
                            'multiple': false,
                            'lItems': []
                        });

                        $('#'+newUid+' .peep_file_attachment_preview').html('');
                        peepFileAttachments[newUid].reset(newUid);
                    }
                    else{
                        $('#conversationAttachmentsBtn').find('.mlt_file_input').remove();

                        newUid = PEEPMailbox.uniqueId('mailbox_conversation_'+self.model.convId+'_'+self.model.opponentId+'_');

                        $('.mailboxConversationAttachmentsPreviewContainer').attr('id', newUid);

                        peepFileAttachments[newUid] = new PEEPFileAttachment({
                            'uid': newUid,
                            'submitUrl': PEEPMailbox.attachmentsSubmitUrl,
                            'deleteUrl': PEEPMailbox.attachmentsDeleteUrl,
                            'showPreview': true,
                            'selector': '#conversationMessageFormBlock #conversationAttachmentsBtn',
                            'pluginKey': 'mailbox',
                            'multiple': true,
                            'lItems': []
                        });

                        $('#'+newUid+' .peep_file_attachment_preview').html('');
                        peepFileAttachments[newUid].reset(newUid);
                        $('#fake_conversationTextarea').val('');
                        $('#conversationMessageFormBlock').removeClass('continue');
                        $('#conversationMessageFormBlock').removeClass('active');
                        $('.peep_mailbox_log').removeClass('textarea_active');
                        PEEP.updateScroll(self.messageListControl);
                    }
                    self.uid = newUid;
                    $('#conversationTextarea').val('').keyup();


                    if (data.log.length > 0)
                    {
                        for(var i=0; i<data.log.length; i++){
                            if (i == 0)
                            {
                                self.model.firstMessageId = data.log[i].id;
                            }
                            //self.write(data.log[i], 'history');
                        }
                        self.conversation.get('messages').set(data.log);
                    }

                    var storage = PEEPMailbox.getStorage();
                    var message = storage.getItem('mailbox.conversation' + self.model.convId + '_form_message');
                    if (typeof message != 'undefined' && message != null && message != '')
                    {
                        self.textareaControl.val(message);
                    }
                    else
                    {
                        if (data.mode == 'chat'){
                            self.textareaControl.val(PEEP.getLanguageText('mailbox', 'text_message_invitation'));
                        }
                        else{
                            self.textareaControl.val('');
                        }
                    }

                    if (data.mode == 'chat' && self.textareaControl.length > 0){
                        self.textareaControl.dialogAutosize(self, 'adjust');
                    }

                    PEEP.trigger('mailbox.conversation_marked_read', {convId: self.model.convId});
                }
                self.model.setIsLogLoaded(true);

                self.hidePreloader();
            },
            error: function(e){
                PEEPMailbox.log(e);
                self.historyLoadInProgress = false;
                PEEP.Mailbox.sendInProcess = false;
                self.hidePreloader();
                self.messageListWrapperControl.html(e.responseText);
            },
            complete: function(){
                self.historyLoadInProgress = false;
                PEEP.Mailbox.sendInProcess = false;
                self.someConversationLoading = 0;
            }
        }

        PEEP.Mailbox.addAjaxData(ajaxData);

        var ajaxData2 = {};
        ajaxData2['actionData'] = {
            'uniqueId': PEEPMailbox.uniqueId('markConversationRead'),
            'name': 'markConversationRead',
            'data': { conversationId: params.convId }
        };
        ajaxData2['actionCallbacks'] = {
            success: function( data ){},
            complete: function(){}
        }

        PEEP.Mailbox.addAjaxData(ajaxData2);

        self.historyLoadInProgress = true;
        PEEP.Mailbox.sendData();

    }

    this.messageListControl.bind('jsp-scroll-y', function(event, scrollPositionY, isAtTop, isAtBottom){

        /**/
        var dateCaps = $('.conversationMessageGroup', self.control);

        dateCaps.each(function(){

            var position = $(this).position();

            var scrollPosition = parseInt(scrollPositionY) - 15;

            if (scrollPosition > position.top)
            {
                self.setStickyDateCapValue($(this).data());
            }
            else
            {
                if (scrollPosition < 0)
                {
                    self.hideStickyDateCap();
                }
            }
        });

        /**/

        if (isAtBottom)
        {
            self.historyLoadAllowed = true;
        }

        if (isAtTop && !self.historyLoadInProgress && self.model.firstMessageId != null && self.historyLoadAllowed)
        {
            self.historyLoadInProgress = true;
            PEEP.Mailbox.sendInProcess = true;
            $.ajax({
                url: PEEPMailbox.getHistoryResponderUrl,
                type: 'POST',
                data: {
                    convId: self.model.convId,
                    messageId: self.model.firstMessageId,
                },
                success: function(data){
                    if ( typeof data != 'undefined' )
                    {
                        if (data.log.length > 0)
                        {
                            var heightBefore = self.messageListWrapperControl.height();

                            $(data.log).each(function(){
                                //self.writeHistory(this);
                                self.conversation.get('messages').add(this);
                            });

                            PEEP.trigger('mailbox.history_loaded');

                            var heightAfter = self.messageListWrapperControl.height();

                            PEEP.updateScroll(self.messageListControl);

                            var jsp = self.messageListControl.data('jsp');
                            jsp.scrollByY(heightAfter - heightBefore);
                        }
                        else
                        {
                            self.historyLoadAllowed = false;
                        }
                    }
                },
                error: function(e){
                    PEEPMailbox.log(e);
                    self.messageListWrapperControl.html(e.responseText);
                },
                complete: function(){
                    self.historyLoadInProgress = false;
                    PEEP.Mailbox.sendInProcess = false;
                },
                dataType: 'json'
            });

        }
    });

    this.settingsBtn.bind('click', function(){
        if (self.settingsBlock.hasClass('peep_hidden')){
            self.showSettingsBlock();
        }
        else{
            self.hideSettingsBlock();
        }
    });

    this.deleteBtn.bind('click', function(){

        if (confirm(PEEP.getLanguageText('mailbox', 'confirm_conversation_delete')))
        {
            $.ajax( {
                url: PEEPMailbox.responderUrl,
                type: 'POST',
                data: { function_: 'deleteConversation', conversationId: self.model.convId },
                dataType: 'json',
                success: function( data )
                {
                    if( data.result == true )
                    {
                        PEEP.info( data.notice );
                        self.reset();
                        PEEP.trigger('mailbox.conversation_deleted', {convId: self.model.convId, opponentId: self.model.opponentId});
                    }
                    else if( data.error != undefined )
                    {
                        PEEP.warning( data.error );
                    }
                }
            } );
        }

        self.hideSettingsBlock();
    });

    this.markUnreadBtn.bind('click', function(){

        var ajaxData = {};
        ajaxData['actionData'] = {
            'uniqueId': PEEPMailbox.uniqueId('markConversationUnRead'),
            'name': 'markConversationUnRead',
            'data': { conversationId: self.model.convId }
        };
        ajaxData['actionCallbacks'] = {
            success: function( data )
            {
                if( data.error != undefined ){
                    PEEP.warning( data.error );
                }
                else if( data.result == true ){
                    PEEP.info( data.notice );
                    PEEP.trigger('mailbox.conversation_marked_unread', {convId: self.model.convId});
                }
            },
            complete: function(){}
        }

        PEEP.Mailbox.sendData(ajaxData);

        self.hideSettingsBlock();
    });

    this.switchToChatBtn.bind('click', function(){
        PEEP.trigger('mailbox.open_dialog', {convId: self.model.convId, opponentId: self.model.opponentId, mode: 'chat'});
    });

    this.sendMessageBtn.bind('click', function(){

        var text = self.textareaControl.val();
        var checkText = text;
 
        // process value
        checkText = checkText.replace(/\&nbsp;|&nbsp/ig,'');
        checkText = checkText.replace(/(<([^>]+)>)/ig,''); 

        if ( !$.trim(checkText).length ){
            PEEP.error(PEEP.getLanguageText('mailbox', 'chat_message_empty'));
            return;
        }

        self.sendMessage(text, (new Date()).getTime());
    });

    this.messageListControl.on('click', '.callReadMessage', function(e){
        $.ajax({
            'type': 'POST',
            'url': PEEPMailbox.authorizationResponderUrl,
            'data': {
                'actionParams': $(this).attr('id')
            },
            'success': function(data){
                if (typeof data.error != 'undefined')
                {
                    PEEP.error(data.error);
                }
                else
                {
                    if (typeof data.authorizationActionText != 'undefined')
                    {
                        PEEP.info(data.authorizationActionText);
                    }
                    self.updateMessage(data);
                }
            },
            'dataType': 'json'
        })
    });

    /**/


    this.model.conversationIdSetSubject.addObserver(function(){

    });

    this.model.modeSetSubject.addObserver(function(){
        if (self.model.mode == 'chat')
        {
            self.subjectBloرپkControl.addClass('peep_hidden');
            self.capBlockControl.addClass('peep_mailbox_cap_chat');
            self.control.addClass('peep_mailbox_right_chat');
            self.conversationChatFormBlock.removeClass('peep_hidden');
            self.messageFormBlock.addClass('peep_hidden');
            self.textareaControl = $('#dialogTextarea', self.control);

            $(self.textareaControl).bind('focus.invitation', {},
                function(e){
                    el = $(this);
                    el.removeClass('invitation');
                    if( el.val() == '' || el.val() == PEEP.getLanguageText('mailbox', 'text_message_invitation')){
                        el.val('');
                        //hotfix for media panel
                        if( 'htmlarea' in el.get(0) ){
                            el.unbind('focus.invitation').unbind('blur.invitation');
                            el.get(0).htmlarea();
                            el.get(0).htmlareaFocus();
                        }
                    }
                    else{
                        el.unbind('focus.invitation').unbind('blur.invitation');
                    }
                }
            ).bind('blur.invitation', {},
                function(e){
                    el = $(this);
                    if( el.val() == '' || el.val() == PEEP.getLanguageText('mailbox', 'text_message_invitation')){
                        el.addClass('invitation');
                        el.val(PEEP.getLanguageText('mailbox', 'text_message_invitation'));
                    }
                    else{
                        el.unbind('focus.invitation').unbind('blur.invitation');
                    }
                }
            );
//            self.textareaControl.css('height', self.textareaHeight );

            if (!self.hasLinkObserver){
                PEEPLinkObserver.observeInput('dialogTextarea', function(link){

                    self.embedLinkResult = false;
                    self.embedLinkDetected = true;

                    this.requestResult();

                    this.onResult = function( r ){
                        self.embedLinkResult = true;

                        if (r.type == 'video' || r.type == 'link')
                        {
                            self.embedAttachmentsObject = r;
                            self.embedAttachmentsValue = JSON.stringify(r);
                        }

                        PEEP.trigger('mailbox.conversation_embed_link_request_result', r);
                    }
                });
            }
        }

        if (self.model.mode == 'mail')
        {
            self.subjectBloرپkControl.removeClass('peep_hidden');
            self.capBlockControl.removeClass('peep_mailbox_cap_chat');
            self.control.removeClass('peep_mailbox_right_chat');
            self.conversationChatFormBlock.addClass('peep_hidden');
            self.messageFormBlock.removeClass('peep_hidden');
            self.textareaControl = $('#conversationTextarea', self.control);

            $('.peep_mailbox_form').click(function(){
                $('#conversationMessageFormBlock').removeClass('continue');
                $('#conversationMessageFormBlock').addClass('active');
                $('.peep_mailbox_log').addClass('textarea_active');
                $('#conversationTextarea').focus();
                $('#conversationTextarea').get(0).htmlareaFocus();
                PEEP.updateScroll(self.messageListControl);
                self.scrollDialog(true);
            });
        }

        self.messageListControl.css('height', '');

        self.bindTextareaControlEvents();
    });

    this.model.statusUpdateSubject.addObserver(function(){

        self.statusControl.removeClass();
        self.statusControl.addClass('peep_chat_status');

        if (self.model.status == 'offline')
        {
            self.control.removeClass('userisonline');
        }
        else
        {
            self.control.addClass('userisonline');
            self.statusControl.addClass(self.model.status);
        }

    });

    this.model.displayNameSetSubject.addObserver(function(){
        self.displayNameControl.html(self.model.displayName);
    });

    this.model.subjectSetSubject.addObserver(function(){
        self.subjectControl.html(self.model.subject);
    });

    this.model.profileUrlSetSubject.addObserver(function(){
        self.profileUrlControl.attr('href', self.model.profileUrl);

        if (self.model.convId){
            var conversation = PEEP.Mailbox.conversationsCollection.findWhere({conversationId: self.model.convId});
            if (conversation){
                self.profileUrlControl.attr('title', conversation.get('shortUserData'));
                PEEP.bindTips(self.control);
            }
        }
    });

    this.model.avatarUrlSetSubject.addObserver(function(){
        if (self.model.avatarUrl)
        {
            self.avatarControl.attr('src', self.model.avatarUrl);
        }
        else
        {
            self.avatarControl.attr('src', PEEPMailbox.defaultAvatarUrl);
        }
    });

    this.model.isSuspendedSetSubject.addObserver(function(){
        if (self.model.isSuspended)
        {
            self.userIsUnreachableBlock.show();
            $('#conversationUserIsUnreachableText', self.userIsUnreachableBlock).html( self.model.suspendReasonMessage );

            if (self.model.mode == 'chat')
            {
                self.conversationChatFormBlock.addClass('peep_hidden');
            }

            if (self.model.mode == 'mail')
            {
                self.messageFormBlock.addClass('peep_hidden');
            }
        }
        else
        {
            self.userIsUnreachableBlock.hide();
            $('#conversationUserIsUnreachableText', self.userIsUnreachableBlock).html( '' );

            if (self.model.mode == 'chat')
            {
                self.conversationChatFormBlock.removeClass('peep_hidden');
            }

            if (self.model.mode == 'mail')
            {
                self.messageFormBlock.removeClass('peep_hidden');
            }
        }
    });


    PEEP.bind('mailbox.conversation_item_selected', function(data){
        if (data.convId != self.model.convId)
        {
            self.setConversationId(data);
        }
//        else
//        {
//            if (self.model.opponentId == null && data.opponentId != null)
//            {
//                self.model.setOpponentId(data.opponentId);
//                self.setConversationId(data.convId);
//            }
//        }
    });

    PEEP.bind('mailbox.conversation_item_list_loaded', function(data){

        if (data.list.length == 0)
        {
            self.hidePreloader();
        }

    });

    PEEP.bind('mailbox.message', function(message){
        if (message.convId != self.model.convId)
        {
            return;
        }

        self.conversation.get('messages').add(message);
        //self.write(message);
    });

    PEEP.bind('mailbox.presence', function(presence){
        if (presence.opponentId != self.model.opponentId)
        {
            return;
        }
        self.model.setStatus(presence.status);
    });

    PEEP.bind('base.add_attachment_to_queue', function(data){

        if (self.model.mode != 'chat'){
            return;
        }

        if (data.pluginKey != 'mailbox' || data.uid != self.uid){
            return;
        }

        $('#conversationChatFormBlock #dialogAttachmentsBtn').addClass('uploading');
//        $('#conversationChatFormBlock #dialogAttachmentsBtn input').attr('disabled', 'disabled');
    });

    PEEP.bind('base.update_attachment', function(data){

        if (self.model.mode != 'chat')
        {
            return;
        }

        if (data.pluginKey != 'mailbox' || data.uid != self.uid)
        {
            return;
        }

        $('#conversationChatFormBlock #dialogAttachmentsBtn').removeClass('uploading');
//        $('#conversationChatFormBlock #dialogAttachmentsBtn input').removeAttr('disabled');

        $.each(data.items, function(){
            if (!this.result){
                PEEP.error(this.message);
            }
        });

        var newUid = PEEPMailbox.uniqueId('mailbox_dialog_'+self.model.convId+'_'+self.model.opponentId+'_');

        PEEP.trigger('base.file_attachment', { 'uid': self.uid, 'newUid': newUid });
        self.uid = newUid;

        PEEP.getPing().getCommand('mailbox_ping').start();
    });

    PEEP.bind('mailbox.send_message', function(data){
        if (data.sentFrom != 'conversation' && data.opponentId == self.model.opponentId && data.convId == self.model.convId)
        {
            self.write(data.tmpMessage);
        }
    });

    PEEP.bind('mailbox.update_message', function(data){
        if (data.sentFrom != 'conversation' && data.opponentId == self.model.opponentId && data.convId == self.model.convId)
        {
            self.updateMessage(data.message);
        }
    });

};

MAILBOX_ConversationView.prototype = {

    construct: function(){
        var self = this;

        this.displayNameControl = $('#conversationOpponentDisplayname', this.control);
        this.subjectControl = $('#conversationSubject', this.control);
        this.subjectBloرپkControl = $('#conversationSubjectBlock', this.control);
        this.profileUrlControl = $('#conversationOpponentProfileUrl', this.control);
        this.avatarControl = $('#conversationOpponentAvatar', this.control);
        this.settingsBtn = $('#conversationSettingsBtn', this.control);
        this.settingsBlock = $('#conversationSettingsBlock', this.control);
        this.deleteBtn = $('#conversationDeleteBtn', this.control);
        this.markUnreadBtn = $('#conversationMarkUnreadBtn', this.control);
        this.sendMessageBtn = $('#conversationSendMessageBtn', this.control);
        this.capBlockControl = $('#conversationCapBlock', this.control);
        this.avatarBlockControl = $('#conversationAvatarBlock',this.control);
        this.statusControl = $('#conversationOpponentProfileStatus',this.control);

        this.dialogWindowHeight = 475;
        this.textareaHeight = 42;

        this.messageListControl = $('#conversationLog', this.control);
        PEEP.addScroll(this.messageListControl, {contentWidth: '0px'});

        this.messageListWrapperControl = $('#conversationLog  .jspContainer .jspPane', this.control);

        this.messageGroupStickyBlockControl = $('#conversationMessageGroupStickyBlock', this.control);
        this.switchToChatBtn = $('#conversationSwitchToChatBtn', this.control);

        this.userIsUnreachableBlock = $('#conversationUserIsUnreachable', this.control);
        this.messageFormBlock = $('#conversationMessageFormBlock', this.control);
        this.conversationChatFormBlock = $('#conversationChatFormBlock', this.control);
    },

    reset: function(){

        this.model.setLastMessageTimestamp(0);
        this.model.firstMessageId = null;
        this.model.setStatus('');
        this.model.setIsLogLoaded(false);
        this.model.setDisplayName(false);

        this.model.setSubject(false);
        this.model.setProfileUrl(false);
        this.model.setAvatarUrl(false);

        this.model.firstMessageTimeLabel = '';
        this.model.lastMessageTimeLabel = '';

        this.lastMessageDate = 0;
        this.firstMessageDate = 0;

        this.messageListWrapperControl.html('');
        this.hideStickyDateCap();

        this.messageFormBlock.addClass('peep_hidden');
        this.userIsUnreachableBlock.hide();
    },

    hidePreloader: function(){
        var self = this;

        this.preloaderControl.removeClass('peep_mailbox_right_loading');
        this.scrollDialog();
    },

    hideSettingsBlock: function(){
        this.settingsBtn.removeClass('active');
        this.settingsBlock.addClass('peep_hidden');
    },

    hideStickyDateCap: function(){
        this.messageGroupStickyBlockControl.hide();
    },

    messageWrite: function(message){
        var itemIndex;
        itemIndex = this.conversation.get('messages').indexOf(message);

        if (itemIndex == 0){
            this.writeHistory(message.attributes);
        }
        else{
            this.write(message.attributes);
        }
    },

    scrollDialog: function(scrollToBottom){

        var scrollToBottom = scrollToBottom || false;

        this.historyLoadAllowed = false;
        PEEP.updateScroll(this.messageListControl);

        var jsp = this.messageListControl.data('jsp');
        if (typeof jsp != 'undefined' && jsp != null)
        {
            lastMessage = this.messageListControl.find('.peep_mailbox_log_message').last();
            if (!scrollToBottom && lastMessage.length > 0){
                jsp.scrollToElement(lastMessage, true, true);
            }
            else{
                jsp.scrollToBottom();
            }
        }

    },

    sendMessage: function(text, timeStamp){
        var self = this;

        var tmpMessageUid = PEEPMailbox.uniqueId('tmpMsg_');

        var d = new Date();
        var utc = d.getTime() / 1000 + (d.getTimezoneOffset() * 60);
        var timeStamp = parseInt(utc + PEEPMailbox.serverTimezoneOffset * 3600);

        var timeLabel = PEEPMailbox.formatAMPM(new Date(timeStamp*1000));

        if (!self.embedLinkDetected)
        {
            var tmpMessage = {
                'rawMessage' : true,
                'isSystem': false,
                'date': PEEPMailbox.todayDate,
                'dateLabel': PEEPMailbox.todayDateLabel,
                'id': tmpMessageUid,
                'text': text,
                'attachments': [],
                'senderId': PEEPMailbox.userDetails.userId,
                'recipientId': self.model.opponentId,
                'timeStamp': timeStamp,
                'timeLabel': timeLabel
            };
            PEEP.trigger('mailbox.send_message', {'sentFrom': 'conversation', 'opponentId': self.model.opponentId, 'convId': self.model.convId, 'tmpMessage': tmpMessage});

            var data = {
                'convId': self.model.convId,
                'text': text,
                'uid': self.uid,
                'embedAttachments': self.embedAttachmentsValue
            };

            self.postMessage(tmpMessageUid, data);
        }
        else
        {
            var tmpMessage = {
                'rawMessage' : true,
                'isSystem': true,
                'date': PEEPMailbox.todayDate,
                'dateLabel': PEEPMailbox.todayDateLabel,
                'id': tmpMessageUid,
                'attachments': [],
                'senderId': PEEPMailbox.userDetails.userId,
                'recipientId': self.model.opponentId,
                'timeStamp': timeStamp,
                'timeLabel': timeLabel
            };

            var preloaderContainer = $('#dialogEmbedLinkBlockPrototype').clone();
            $('#dialogMessageText', preloaderContainer).html(text);
            tmpMessage['text'] = preloaderContainer.html();

            PEEP.trigger('mailbox.send_message', {'sentFrom': 'conversation', 'opponentId': self.model.opponentId, 'convId': self.model.convId,  'tmpMessage': tmpMessage});

            if (self.embedLinkResult)
            {
                var data = {
                    'convId': self.model.convId,
                    'text': text,
                    'uid': self.uid,
                    'embedAttachments': self.embedAttachmentsValue
                };

                self.postMessage(tmpMessageUid, data);
            }
            else
            {
                PEEP.bind('mailbox.conversation_embed_link_request_result', function(r){
                    var data = {
                        'convId': self.model.convId,
                        'text': text,
                        'uid': self.uid,
                        'embedAttachments': self.embedAttachmentsValue
                    };

                    self.postMessage(tmpMessageUid, data);
                    PEEP.unbind('mailbox.embed_link_request_result_'+self.model.convId);
                });
            }

//            PEEPLinkObserver.getObserver('conversationTextarea').resetObserver();
            PEEPLinkObserver.getObserver('dialogTextarea').resetObserver();
        }

        tmpMessage.text = tmpMessage.text.nl2br();
        self.write(tmpMessage);

        var storage = PEEPMailbox.getStorage();
        storage.setItem('mailbox.conversation' + self.model.convId + '_form_message', '');
        self.textareaControl.val('');
        $('#conversationTextarea').get(0).htmlareaRefresh();
    },

    postMessage: function(tmpMessageUid, data){

        var self = this;

        var ajaxData = {};
        ajaxData['actionData'] = {
            'uniqueId': PEEPMailbox.uniqueId('postMessage'),
            'name': 'postMessage',
            'data': data
        };
        ajaxData['actionCallbacks'] = {
            'tmpMessageUid' : tmpMessageUid,
            'success': function(data){

                if (typeof data.error == 'undefined' || data.error == null)
                {
                    self.conversation.get('messages').add(data.message, {silent: true});
                    data.message.uniqueId = tmpMessageUid;
                    self.updateMessage(data.message);
                    PEEP.Mailbox.lastMessageTimestamp = parseInt(data.message.timeStamp);
                    PEEP.trigger('mailbox.update_message', {'sentFrom': 'conversation', 'opponentId': self.model.opponentId, 'convId': self.model.convId, 'message': data.message});

                    var newUid = PEEPMailbox.uniqueId('mailbox_conversation_'+self.model.convId+'_'+self.model.opponentId+'_');
                    PEEP.trigger('base.file_attachment', { 'uid': self.uid, 'newUid': newUid });
                    self.uid = newUid;
                }
                else
                {
                    PEEP.error(data.error);
                    self.showSendMessageFailed(tmpMessageUid);
                }
            },
            'error': function(){
                self.showSendMessageFailed(tmpMessageUid);
            },
            'complete': function(){
                PEEP.Mailbox.sendInProcess = false;

                self.embedLinkResult = true;
                self.embedLinkDetected = false;
                self.embedAttachmentsValue = '';
                self.embedAttachmentsObject = {};
            }
        };

        PEEP.Mailbox.sendData(ajaxData);

    },

    showSendMessageFailed: function(messageId){

        var self = this;

        if (self.model.mode == 'mail'){
            $('#messageItem'+messageId, self.control).addClass('errormessage');
            $('#messageItem'+messageId, self.control).prepend('<span class="peep_errormessage_not peep_red peep_small">'+PEEP.getLanguageText('mailbox', 'send_message_failed')+'</span>');
        }

        if (self.model.mode == 'chat'){
            $('#messageItem'+messageId+' .peep_dialog_in_item', self.control).addClass('errormessage');
            $('#messageItem'+messageId+' .peep_dialog_in_item', self.control).prepend('<span class="peep_errormessage_not peep_red peep_small">'+PEEP.getLanguageText('mailbox', 'send_message_failed')+'</span>');
        }
    },

    setStickyDateCapValue: function(data){

        if (data.date == PEEPMailbox.todayDate)
        {
            this.hideStickyDateCap();
        }
        else
        {
            this.showStickyDateCap();
        }

        $('#conversationStickyDateCap', this.messageGroupStickyBlockControl).html(data.dateLabel);
        this.messageGroupStickyBlockControl.data(data);
    },

    showPreloader: function(){
        this.preloaderControl.addClass('peep_mailbox_right_loading');
    },

    showSettingsBlock: function(){
        this.settingsBtn.addClass('active');
        this.settingsBlock.removeClass('peep_hidden');
    },

    showStickyDateCap: function(){
        this.messageGroupStickyBlockControl.show();
    },

    getTimeBlock: function(timeLabel){
        var timeBlock = $('#dialogTimeBlockPrototypeBlock').clone();

        timeBlock.attr('id', 'timeBlock'+this.model.lastMessageTimestamp);

        $('.peep_time_text', timeBlock).html(timeLabel);

        return timeBlock;
    },

    showTimeBlock: function(timeLabel, groupContainer){

        var timeBlock = this.getTimeBlock(timeLabel);

        groupContainer.append(timeBlock);
        this.scrollDialog();

        return this;
    },

    updateChatMessage: function(message){
        if (typeof message.uniqueId != 'undefined')
        {
            var messageContainer = $('#messageItem'+message.uniqueId, this.control);
            messageContainer.attr('id', 'messageItem'+message.id);
        }
        else
        {
            var messageContainer = $('#messageItem'+message.id, this.control);
        }

        var html = '';
        if (message.isSystem)
        {
            html = message.text;

//            $('#dialogMessageWrapper', messageContainer).html( html );
            messageContainer.html( html );
        }
        else
        {
            if (message.attachments.length != 0)
            {
                var i = 0;

                if (message.attachments[i]['type'] == 'image')
                {
                    messageContainer.addClass('peep_dialog_picture_item');
                    $('#dialogMessageText', messageContainer).html( '<a href="'+message.attachments[i]['downloadUrl']+'" target="_blank"><img src="'+message.attachments[i]['downloadUrl']+'" /></a>' );
                }
                else
                {
                    $('.peep_dialog_in_item', messageContainer).addClass('fileattach');

                    var attachment = $('#conversationFileAttachmentBlockPrototype').clone();
                    attachment.removeAttr('id');

                    $('#conversationFileAttachmentFileName', attachment).html( PEEPMailbox.formatAttachmentFileName(message.attachments[i]['fileName']) );
                    $('#conversationFileAttachmentFileName', attachment).attr('href', message.attachments[i]['downloadUrl']);
                    $('#conversationFileAttachmentFileSize', attachment).html( PEEPMailbox.formatAttachmentFileSize(message.attachments[i]['fileSize']) );

                    $('.peep_dialog_in_item', messageContainer).html( attachment.html() );
                }
            }
            else
            {
//                html = htmlspecialchars(message.text, 'ENT_QUOTES');
                html = message.text;

                if ($('#dialogMessageText', messageContainer).length == 0){
                    tmpMessageContainer = $('#dialogChatMessagePrototypeBlock').clone();
                    tmpMessageContainer.attr('id', 'messageItem'+message.id);
                    messageContainer.html(tmpMessageContainer.html());
                }

                $('#dialogMessageText', messageContainer).html( html );
                $('#dialogMessageText', messageContainer).autolink();
            }
        }

        if ( message.senderId != this.model.opponentId ){
            $('div.peep_dialog_item', messageContainer).addClass('even');
        }
        else{
            $('div.peep_dialog_item', messageContainer).addClass('odd');
        }

        this.scrollDialog();

        PEEP.trigger('mailbox.message_was_authorized', message);
    },

    updateMailMessage: function(message){
        if (typeof message.uniqueId != 'undefined')
        {
            var messageContainer = $('#messageItem'+message.uniqueId, this.control);
            messageContainer.attr('id', 'messageItem'+message.id);
        }
        else
        {
            var messageContainer = $('#messageItem'+message.id, this.control);
        }

        if (message.senderId == this.model.opponentId)
        {
            var messageProfileDisplayName = this.model.displayName;
            var messageProfileUrl = this.model.profileUrl;
            var messageProfileAvatarUrl = this.model.avatarUrl;
        }
        else
        {
            var messageProfileDisplayName = PEEPMailbox.userDetails.displayName;
            var messageProfileUrl = PEEPMailbox.userDetails.profileUrl;
            var messageProfileAvatarUrl = PEEPMailbox.userDetails.avatarUrl;
        }

        $('#conversationMessageDateTime', messageContainer).html(message.timeLabel);
        $('#conversationMessageProfile', messageContainer).attr('href', messageProfileUrl);
        $('#conversationMessageProfile', messageContainer).html(messageProfileDisplayName);
        $('#conversationMessageAvatarProfileUrl', messageContainer).attr('href', messageProfileUrl);
        $('#conversationMessageAvatarUrl', messageContainer).attr('src', messageProfileAvatarUrl);

        var html = '';
        if (message.isSystem)
        {
            html = message.text;
            $('#conversationMessageText', messageContainer).html( html );
        }
        else
        {
//            html = htmlspecialchars(message.text, 'ENT_QUOTES');
            html = message.text;
            $('#conversationMessageText', messageContainer).html( html );
            if (self.autolinkEnabled){
                $('#conversationMessageText', messageContainer).autolink();
            }
        }

        if (message.attachments.length != 0)
        {
            var attachmentsBlock = $('#conversationFileAttachmentContentBlockPrototype').clone();
            attachmentsBlock.removeAttr('id');

            for (var i in message.attachments)
            {
                var attachment = $('#conversationFileAttachmentBlockPrototype').clone();

                attachment.removeAttr('id');

                if (parseInt(i) % 2)
                {
                    attachment.addClass('peep_file_attachment_block2');
                }
                else
                {
                    attachment.addClass('peep_file_attachment_block1');
                }

                $('#conversationFileAttachmentFileName', attachment).html( PEEPMailbox.formatAttachmentFileName(message.attachments[i]['fileName']) );
                $('#conversationFileAttachmentFileName', attachment).attr( 'href', message.attachments[i]['downloadUrl'] );
                $('#conversationFileAttachmentFileSize', attachment).html( PEEPMailbox.formatAttachmentFileSize(message.attachments[i]['fileSize']) );

                attachmentsBlock.append(attachment);
            }
            $('#conversationMessageText', messageContainer).append(attachmentsBlock);
        }

        this.scrollDialog();
    },

    updateMessage: function(message){
        if (this.model.mode == 'chat')
        {
            this.updateChatMessage(message);
        }

        if (this.model.mode == 'mail')
        {
            this.updateMailMessage(message);
        }
    },

    writeChatMessage: function(message, css_class){

        var css_class = css_class || null;

        if ($('#messageItem'+message.id, this.control).length > 0)
        {
            return;
        }

        var groupContainer = $('#groupedMessages-'+message.date, this.control);
        if (groupContainer.length == 0)
        {
            groupContainer = $('#conversationMessageGroupPrototypeBlock').clone();
            $('#conversationMessageGroupDate', groupContainer).html(message.dateLabel);

            groupContainer.attr('id', 'groupedMessages-'+message.date);
            groupContainer.data({
                date: message.date,
                dateLabel: message.dateLabel
            });
        }

        var messageContainer = null;

        if (message.isSystem){
            messageContainer = $('#dialogSysMessagePrototypeBlock').clone();
        }
        else{
            messageContainer = $('#dialogChatMessagePrototypeBlock').clone();
        }

        messageContainer.attr('id', 'messageItem'+message.id);
        messageContainer.attr('data-tmp-id', 'messageItem'+message.id);
        messageContainer.attr('data-timestamp', message.timeStamp);
        messageContainer.addClass('message');

        var html = '';
        if (message.isSystem){
            html = message.text;

            messageContainer.html( html );
        }
        else{
            if (message.attachments.length != 0){
                var i = 0;

                if (message.attachments[i]['type'] == 'image'){
                    messageContainer.addClass('peep_dialog_picture_item');
                    $('#dialogMessageText', messageContainer).html( '<a href="'+message.attachments[i]['downloadUrl']+'" target="_blank"><img src="'+message.attachments[i]['downloadUrl']+'" /></a>' );
                }
                else{
                    $('.peep_dialog_in_item', messageContainer).addClass('fileattach');

                    var attachment = $('#conversationFileAttachmentBlockPrototype').clone();
                    attachment.removeAttr('id');

                    $('#conversationFileAttachmentFileName', attachment).html( PEEPMailbox.formatAttachmentFileName(message.attachments[i]['fileName']) );
                    $('#conversationFileAttachmentFileName', attachment).attr('href', message.attachments[i]['downloadUrl']);
                    $('#conversationFileAttachmentFileSize', attachment).html( PEEPMailbox.formatAttachmentFileSize(message.attachments[i]['fileSize']) );

                    $('.peep_dialog_in_item', messageContainer).html( attachment.html() );
                }
            }
            else{
//                html = htmlspecialchars(message.text, 'ENT_QUOTES');
                html = message.text;

                $('#dialogMessageText', messageContainer).html( html );
                $('#dialogMessageText', messageContainer).autolink();
            }
        }

        if ( message.senderId != this.model.opponentId ){
            $('div.peep_dialog_item', messageContainer).addClass('even');
        }
        else{
            $('div.peep_dialog_item', messageContainer).addClass('odd');
        }

        if (css_class != null)
        {
            $('div.peep_dialog_item', messageContainer).addClass(css_class);
        }

        // get last message
        var lastMessage = this.messageListControl.find('.message:last');

        
        if (message.rawMessage || !lastMessage.length || lastMessage.attr('data-timestamp') < message.timeStamp) {
            if (this.lastMessageDate != message.date)
            {
                this.lastMessageDate = message.date;
                this.messageListWrapperControl.append(groupContainer);
            }

            if ( message.timeLabel != this.model.lastMessageTimeLabel )
            {
                this.model.lastMessageTimeLabel = message.timeLabel;
                this.showTimeBlock(message.timeLabel, groupContainer);
            }

            groupContainer.append(messageContainer);
            this.messageListWrapperControl.append(groupContainer);
            this.scrollDialog();

            this.model.setLastMessageTimestamp(message.timeStamp);
            this.model.lastMessageId = message.id;
        }
        else {
            $(messageContainer).insertBefore(lastMessage);
            this.scrollDialog();
        }

        if ( parseInt(im_readCookie('im_soundEnabled')) && css_class == null){
            var audioTag = document.createElement('audio');
            if (!(!!(audioTag.canPlayType) && ("no" != audioTag.canPlayType("audio/mp3")) && ("" != audioTag.canPlayType("audio/mp3")) && ("maybe" != audioTag.canPlayType("audio/mp3")) )) {
                AudioPlayer.embed("im_sound_player_audio", {
                    soundFile: PEEPMailbox.soundUrl,
                    autostart: 'yes'
                });
            }
            else
            {
                $('#im_sound_player_audio')[0].play();
            }
        }

    },

    writeMailMessage: function(message, css_class){

        var self = this;
        var css_class = css_class || null;

        var messageProfileDisplayName;
        var messageProfileUrl;
        var messageProfileAvatarUrl;

        if (message.recipientId == self.model.opponentId){
            messageProfileDisplayName = PEEPMailbox.userDetails.displayName;
            messageProfileUrl = PEEPMailbox.userDetails.profileUrl;
            messageProfileAvatarUrl = PEEPMailbox.userDetails.avatarUrl;
        }

        if (message.senderId == self.model.opponentId){
            messageProfileDisplayName = self.model.displayName;
            messageProfileUrl = self.model.profileUrl;
            messageProfileAvatarUrl = self.model.avatarUrl;
        }

        var groupContainer = $('#groupedMessages-'+message.date, this.control);
        if (groupContainer.length == 0){
            groupContainer = $('#conversationMessageGroupPrototypeBlock').clone();
            $('#conversationMessageGroupDate', groupContainer).html(message.dateLabel);

            groupContainer.attr('id', 'groupedMessages-'+message.date);
            groupContainer.data({
                date: message.date,
                dateLabel: message.dateLabel
            });
        }

        var messageContainer = $('#conversationMessagePrototypeBlock').clone();
        messageContainer.removeAttr('id');
        $('#conversationMessageDateTime', messageContainer).html(message.timeLabel);
        $('#conversationMessageProfile', messageContainer).attr('href', messageProfileUrl);
        $('#conversationMessageProfile', messageContainer).html(messageProfileDisplayName);
        $('#conversationMessageAvatarProfileUrl', messageContainer).attr('href', messageProfileUrl);
        $('#conversationMessageAvatarUrl', messageContainer).attr('src', messageProfileAvatarUrl);

        var html = '';
        if (message.isSystem){
            html = message.text;
            $('#conversationMessageText', messageContainer).html( html );
        }
        else{
//            html = htmlspecialchars(message.text, 'ENT_QUOTES');
            html = message.text;

            $('#conversationMessageText', messageContainer).html( html );
            if (self.autolinkEnabled){
                $('#conversationMessageText', messageContainer).autolink();
            }
        }

        if (message.attachments.length != 0){
            var attachmentsBlock = $('#conversationFileAttachmentContentBlockPrototype').clone();
            attachmentsBlock.removeAttr('id');

            for (var i in message.attachments)
            {
                var attachment = $('#conversationFileAttachmentBlockPrototype').clone();

                attachment.removeAttr('id');

                if (parseInt(i) % 2)
                {
                    attachment.addClass('peep_file_attachment_block2');
                }
                else
                {
                    attachment.addClass('peep_file_attachment_block1');
                }

                $('#conversationFileAttachmentFileName', attachment).html( PEEPMailbox.formatAttachmentFileName(message.attachments[i]['fileName']) );
                $('#conversationFileAttachmentFileName', attachment).attr( 'href', message.attachments[i]['downloadUrl'] );
                $('#conversationFileAttachmentFileSize', attachment).html( PEEPMailbox.formatAttachmentFileSize(message.attachments[i]['fileSize']) );

                attachmentsBlock.append(attachment);
            }
            $('#conversationMessageText', messageContainer).append(attachmentsBlock);
        }

        this.model.setLastMessageTimestamp(message.timeStamp);
        this.model.lastMessageId = message.id;
        messageContainer.attr('id', 'messageItem'+message.id );

        groupContainer.append(messageContainer);
        this.messageListWrapperControl.append(groupContainer);
        this.scrollDialog();

        PEEP.trigger('mailbox.after_write_mail_message', message);
    },

    write: function(message, css_class){

        //if (message.timeStamp < this.model.lastMessageTimestamp)
        //{
        //    return this;
        //}

        if (this.model.mode == 'chat'){
            this.writeChatMessage(message, css_class);
        }

        if (this.model.mode == 'mail'){

            //if (message.timeStamp < PEEPMailbox.pluginUpdateTimestamp){
            this.autolinkEnabled = false;
            //}
            //else{
            //    this.autolinkEnabled = true;
            //}

            this.writeMailMessage(message, css_class);
        }

        if ( message.recipientId == PEEPMailbox.userDetails.userId && message.recipientRead == 0 ){
            PEEP.trigger('mailbox.mark_message_read', {message: message});
        }

        //if ( message.senderId == PEEPMailbox.userDetails.userId ){
        //    PEEP.trigger('mailbox.mark_message_read', {message: message});
        //}

        return this;
    },

    writeHistoryChatMessage: function(message){

        var messageContainer = null;

        if (message.isSystem){
            messageContainer = $('#dialogSysMessagePrototypeBlock').clone();
        }
        else{
            messageContainer = $('#dialogChatMessagePrototypeBlock').clone();
        }

        messageContainer.attr('id', 'messageItem'+message.id);

        var html = '';
        if (message.isSystem){
            html = message.text;

            messageContainer.html( html );
        }
        else{
            if (message.attachments.length != 0){
                var i = 0;

                if (message.attachments[i]['type'] == 'image'){
                    messageContainer.addClass('peep_dialog_picture_item');
                    $('#dialogMessageText', messageContainer).html( '<a href="'+message.attachments[i]['downloadUrl']+'" target="_blank"><img src="'+message.attachments[i]['downloadUrl']+'" /></a>' );
                }
                else{
                    $('.peep_dialog_in_item', messageContainer).addClass('fileattach');

                    var attachment = $('#conversationFileAttachmentBlockPrototype').clone();
                    attachment.removeAttr('id');

                    $('#conversationFileAttachmentFileName', attachment).html( PEEPMailbox.formatAttachmentFileName(message.attachments[i]['fileName']) );
                    $('#conversationFileAttachmentFileName', attachment).attr('href', message.attachments[i]['downloadUrl']);
                    $('#conversationFileAttachmentFileSize', attachment).html( PEEPMailbox.formatAttachmentFileSize(message.attachments[i]['fileSize']) );

                    $('.peep_dialog_in_item', messageContainer).html( attachment.html() );
                }
            }
            else{
//                html = htmlspecialchars(message.text, 'ENT_QUOTES');
                html = message.text;

                $('#dialogMessageText', messageContainer).html( html );
                $('#dialogMessageText', messageContainer).autolink();
            }
        }

        if ( message.senderId != this.model.opponentId ){
            $('div.peep_dialog_item', messageContainer).addClass('even');
        }
        else{
            $('div.peep_dialog_item', messageContainer).addClass('odd');
        }

        var groupContainer = $('#groupedMessages-'+message.date, this.control);
        if (groupContainer.length == 0) {
            groupContainer = $('#conversationMessageGroupPrototypeBlock').clone();

            var timeBlock = $('#dialogTimeBlockPrototypeBlock').clone();
            timeBlock.attr('id', 'timeBlock' + message.timeStamp);
            $('.peep_time_text', timeBlock).html(message.timeLabel);
            groupContainer.append(timeBlock);
            groupContainer.append(messageContainer);

            $('#conversationMessageGroupDate', groupContainer).html(message.dateLabel);

            groupContainer.attr('id', 'groupedMessages-' + message.date);
            groupContainer.data({
                date: message.date,
                dateLabel: message.dateLabel
            });

            this.messageListWrapperControl.prepend(groupContainer);
        }
        else{

            //var firstMessageContainer = $('#messageItem'+this.model.firstMessageId, this.control);
            //firstMessageContainer.before(messageContainer);

            $('.peep_mailbox_date_cap', groupContainer).after(messageContainer);
            if ( message.timeLabel != this.model.firstMessageTimeLabel )
            {
                this.model.firstMessageTimeLabel = message.timeLabel;
                var timeBlock = this.getTimeBlock(message.timeLabel);

                $('.peep_mailbox_date_cap', groupContainer).after(timeBlock);
            }

        }

        this.model.firstMessageId = message.id;
    },

    writeHistoryMailMessage: function(message){
        var self = this;

        var messageProfileDisplayName;
        var messageProfileUrl;
        var messageProfileAvatarUrl;

        if (message.recipientId == self.model.opponentId){
            messageProfileDisplayName = PEEPMailbox.userDetails.displayName;
            messageProfileUrl = PEEPMailbox.userDetails.profileUrl;
            messageProfileAvatarUrl = PEEPMailbox.userDetails.avatarUrl;
        }

        if (message.senderId == self.model.opponentId){
            messageProfileDisplayName = self.model.displayName;
            messageProfileUrl = self.model.profileUrl;
            messageProfileAvatarUrl = self.model.avatarUrl;
        }

        var firstMessageContainer = $('#messageItem'+this.model.firstMessageId);

        var messageContainer = $('#conversationMessagePrototypeBlock').clone();
        messageContainer.removeAttr('id');
        $('#conversationMessageDateTime', messageContainer).html(message.timeLabel);
        $('#conversationMessageProfile', messageContainer).attr('href', messageProfileUrl);
        $('#conversationMessageProfile', messageContainer).html(messageProfileDisplayName);
        $('#conversationMessageAvatarProfileUrl', messageContainer).attr('href', messageProfileUrl);
        $('#conversationMessageAvatarUrl', messageContainer).attr('src', messageProfileAvatarUrl);

        var html = '';
        if (message.isSystem){
            html = message.text;
            $('#conversationMessageText', messageContainer).html( html );
        }
        else{
//            html = htmlspecialchars(message.text, 'ENT_QUOTES');
            html = message.text;
            $('#conversationMessageText', messageContainer).html( html );
            if (self.autolinkEnabled){
                $('#conversationMessageText', messageContainer).autolink();
            }
        }

        if (message.attachments.length != 0){
            var attachmentsBlock = $('#conversationFileAttachmentContentBlockPrototype').clone();
            attachmentsBlock.removeAttr('id');

            for (var i in message.attachments)
            {
                var attachment = $('#conversationFileAttachmentBlockPrototype').clone();

                attachment.removeAttr('id');

                if (parseInt(i) % 2)
                {
                    attachment.addClass('peep_file_attachment_block2');
                }
                else
                {
                    attachment.addClass('peep_file_attachment_block1');
                }

                $('#conversationFileAttachmentFileName', attachment).html( PEEPMailbox.formatAttachmentFileName(message.attachments[i]['fileName']) );
                $('#conversationFileAttachmentFileName', attachment).attr( 'href', message.attachments[i]['downloadUrl'] );
                $('#conversationFileAttachmentFileSize', attachment).html( PEEPMailbox.formatAttachmentFileSize(message.attachments[i]['fileSize']) );

                attachmentsBlock.append(attachment);
            }
            $('#conversationMessageText', messageContainer).append(attachmentsBlock);
        }

        messageContainer.attr('id', 'messageItem'+message.id );
        this.model.firstMessageId = message.id;

        var groupContainer = $('#groupedMessages-'+message.date, this.control);
        if (groupContainer.length == 0){
            groupContainer = $('#conversationMessageGroupPrototypeBlock').clone();
            $('#conversationMessageGroupDate', groupContainer).html(message.dateLabel);

            groupContainer.attr('id', 'groupedMessages-'+message.date);
            groupContainer.data({
                date: message.date,
                dateLabel: message.dateLabel
            });

            groupContainer.append(messageContainer);

            this.messageListWrapperControl.prepend(groupContainer);
        }
        else{
            firstMessageContainer.before(messageContainer);
        }

    },

    writeHistory: function(message){
        if (this.model.mode == 'chat')
        {
            this.writeHistoryChatMessage(message);
        }

        if (this.model.mode == 'mail')
        {
            this.autolinkEnabled = false;
            this.writeHistoryMailMessage(message);
        }

        if ( message.recipientId == PEEPMailbox.userDetails.userId && message.recipientRead == 0 ){
            PEEP.trigger('mailbox.mark_message_read', {message: message});
        }
    }
};