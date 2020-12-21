String.prototype.nl2br = function(is_xhtml) {
    var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
    return (this + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1'+ breakTag +'$2');
}

String.prototype.width = function(font) {

    var f = font || '12px arial',
        o = $('<div>').text(this)
            .css({'position': 'absolute', 'float': 'left', 'visibility': 'hidden', 'word-wrap': 'break-word', 'word-spacing': 'normal', 'white-space': 'nowrap', 'font': f})
            .appendTo($('body')),
        w = o.width();

    o.remove();

    return w;
}

MAILBOX_Message = Backbone.Model.extend({
    idAttribute: 'id',
});

MAILBOX_MessageCollection = Backbone.Collection.extend({
    model: MAILBOX_Message,
    comparator: function(model){
        return model.get('id');
    }
});

MAILBOX_Conversation = Backbone.Model.extend({
    idAttribute: 'conversationId',
    defaults: {
        conversationId: null,
        opponentId: null,

        conversationRead: 1,
        displayName: '',
        lastMessageTimestamp: 0,
        newMessageCount: 0,
        wasCreatedByScroll: false,
        show: false,
        shortUserData: '',
        messages: new MAILBOX_MessageCollection(),
        firstMessageId: 0,
        lastMessageId: 0
    },

    show: function(){
        this.set('show', true);
    },

    hide: function(){
        this.set('show', false);
    }
});

MAILBOX_ConversationsCollection = Backbone.Collection.extend({
    model: MAILBOX_Conversation,
    comparator: function(model){
        return -model.get('lastMessageTimestamp');
    }
});

MAILBOX_User = Backbone.Model.extend({
    idAttribute: 'opponentId',
    defaults: {
        convId: null,
        opponentId: null,
        status: '',
        lastMessageTimestamp: 0,
        displayName: '',
        profileUrl: '',
        avatarUrl: '',
        wasCorrespondence: false,
        isFriend: false,
        unreadMessagesCount: 0,
    },

    initialize: function(){

        var self = this;

        this.conversation = new MAILBOX_Conversation;

        PEEP.bind('mailbox.application_started', function(){
            if ( self.get('convId') == null ){
                PEEP.Mailbox.conversationsCollection.on('add', self.bindConversation, this);
            }
            else{
                var conversation = PEEP.Mailbox.conversationsCollection.findWhere({opponentId: self.get('opponentId'), mode: 'chat'});
                if (conversation){
                    self.bindConversation(conversation);
                }
            }
        });
    },

    bindConversation: function(conversation){
        if (conversation.get('mode') == 'chat' && conversation.get('opponentId') == this.get('opponentId')){

            this.conversation = conversation;
            this.conversation.on('change:newMessageCount', this.changeNewMessageCount, this);
            this.conversation.on('change:lastMessageTimestamp', this.changeLastMessageTimestamp, this);
            this.set('convId', conversation.get('conversationId'));
            this.changeNewMessageCount();
            this.changeLastMessageTimestamp();
        }
    },

    changeNewMessageCount: function(){
        this.set('unreadMessagesCount', this.conversation.get('newMessageCount'));
    },

    changeLastMessageTimestamp: function(){
        this.set('lastMessageTimestamp', this.conversation.get('lastMessageTimestamp'));
    }

});

MAILBOX_UsersCollection = Backbone.Collection.extend({
    model: MAILBOX_User,
    comparator: function(model){
        return model.get('displayName');
    }
})

PEEPMailbox = {}

PEEPMailbox.Application = function(params){

    var self = this;

    this.pingInterval = params.pingInterval || 5000;
    this.beforePingStatus = true;
    this.sendInProcess = false;
    this.lastMessageTimestamp = params.lastMessageTimestamp || 0;
    this.userOnlineCount = 0;
    this.usersCollection = new MAILBOX_UsersCollection;
    this.conversationsCollection = new MAILBOX_ConversationsCollection;
    this.conversationsCount = 0;

    this.ajaxActionData = [];
    this.ajaxActionCallbacks = {};
    this.markedUnreadConversationList = [];
    this.markedUnreadNotViewedConversationList = [];
    this.appStarted = false;

    var storage = PEEPMailbox.getStorage();
    storage.setItem('lastMessageTimestamp', this.lastMessageTimestamp);

    this.addAjaxData = function(ajaxData){
        this.ajaxActionData.push(ajaxData['actionData']);
        this.ajaxActionCallbacks[ajaxData['actionData']['uniqueId']] = ajaxData['actionCallbacks'];
    }

    this.sendData = function(ajaxData){
        if (typeof ajaxData != 'undefined'){
            this.addAjaxData(ajaxData);
        }

        var requestData = JSON.stringify(self.getParams());

        self.beforePingStatus = false;
        $.ajax({
            url: PEEPMailbox.pingResponderUrl,
            type: 'POST',
            data: {'request': requestData},
            success: function(data){
                self.setData(data);
            },
            complete: function(){
                self.beforePingStatus = true;
            },
            dataType: 'json'
        });
    }

    this.getParams = function(){
        var params = {};

        var date = new Date();
        var time = parseInt(date.getTime() / 1000);

        params.lastRequestTimestamp = time;
        params.lastMessageTimestamp = self.lastMessageTimestamp;
        params.readMessageList = self.contactManager.getReadMessageList();
        params.unreadMessageList = self.contactManager.getUnreadMessageList();
        params.viewedConversationList = self.contactManager.getViewedConversationList();
        params.userOnlineCount = self.userOnlineCount;
        params.userListLength = self.usersCollection.length;
        params.convListLength = self.conversationsCollection.length;
        params.conversationsCount = self.conversationsCount;
        params.ajaxActionData = self.ajaxActionData;
        self.ajaxActionData = [];

        if (params.readMessageList.length != 0){
            self.contactManager.clearReadMessageList();
        }

        if (params.viewedConversationList.length != 0){
            self.contactManager.clearViewedConversationList();
        }

        return params;
    }

    this.setData = function(data){

        
        if (typeof data.ajaxActionResponse != 'undefined'){
            $.each(data.ajaxActionResponse, function(uniqueId, item){
                 var actionCallback = self.ajaxActionCallbacks[uniqueId];
            
                 if (typeof item.message != 'undefined') {
                    if (item.message.mode == 'chat' && typeof actionCallback.tmpMessageUid != 'undefined') {
                         $(".message[data-tmp-id='messageItem" + actionCallback.tmpMessageUid + "']").attr('data-timestamp', item.message.timeStamp);
                    }
                 }
            });
        }

        if (typeof data.userOnlineCount != 'undefined'){
            if (typeof data.userList != 'undefined'){
                self.usersCollection = self.usersCollection.set(data.userList);
            }

            self.userOnlineCount = data.userOnlineCount;
            PEEP.trigger('mailbox.user_online_count_update', {userOnlineCount: data.userOnlineCount});
        }

        if (typeof data.convList != 'undefined'){
            self.conversationsCount = data.conversationsCount;
            self.conversationsCollection.set(data.convList);
        }

        if (typeof data.markedUnreadConversationList != 'undefined'){
            self.markedUnreadConversationList = data.markedUnreadConversationList;
            PEEP.trigger('mailbox.console_update_counter', { unreadMessageList: [] });
            PEEP.MailboxConsole.updateCounter();
        }

        if (typeof data.messageList != 'undefined'){
            ///var tmpLastMessageTimestamp = PEEP.Mailbox.lastMessageTimestamp;
            $.each(data.messageList, function(){
               // if (this.timeStamp != self.lastMessageTimestamp){
                    PEEP.trigger('mailbox.message', this);
                 //   tmpLastMessageTimestamp = parseInt(this.timeStamp);
                //}
            });
            //PEEP.Mailbox.lastMessageTimestamp = tmpLastMessageTimestamp;
        }

        //TODO self.ajaxActionCallbacks.error
        if (typeof data.ajaxActionResponse != 'undefined'){

            var callbacksToDelete = [];
            $.each(data.ajaxActionResponse, function(uniqueId, item){
                self.ajaxActionCallbacks[uniqueId].success(item);
                self.ajaxActionCallbacks[uniqueId].complete();
                callbacksToDelete.push(uniqueId);
            });

            for (var i=0; i<callbacksToDelete.length; i++){
                delete self.ajaxActionCallbacks[callbacksToDelete[i]];
            }
        }

        if (!self.appStarted){
            PEEP.trigger('mailbox.ready');
        }

        PEEP.trigger('mailbox.after_ping');
        PEEP.MailboxConsole.updateCounter();
    }

    PEEP.getPing().addCommand('mailbox_ping', {
        params: {},
        before: function()
        {
            if (!self.beforePingStatus){
                return false;
            }

            if (self.sendInProcess){
                return false;
            }

            this.params = self.getParams();
        },
        after: function( data )
        {
            if (typeof data != 'undefined'){
                self.setData(data);
            }
            else{
                if (im_debug_mode){console.log('Ping data is empty for some reason');}
            }
        }
    }).start(this.pingInterval);
}

PEEPMailbox.getStorage = function(){
    try {
        if ('localStorage' in window && window['localStorage'] !== null){
            return localStorage;
        }
    } catch (e) {
        return {
            getItem: function(key){
                return im_readCookie(key);
            },

            setItem: function(key, value){
                im_createCookie(key, value, 1);
            },

            removeItem: function(key){
                im_eraseCookie(key);
            }
        }
    }
}

PEEPMailbox.getOpenedDialogsCookie = function(){
    var storage = PEEPMailbox.getStorage();
    var openedDialogs = {};
    var openedDialogsJson = storage.getItem('mailbox.openedDialogs');

    if (openedDialogsJson != null){
        openedDialogs = JSON.parse(openedDialogsJson);
    }

    return openedDialogs;
}

PEEPMailbox.setOpenedDialogsCookie = function(value){

    var storage = PEEPMailbox.getStorage();
    var openedDialogsJson = JSON.stringify(value);

    storage.setItem('mailbox.openedDialogs', openedDialogsJson);
}

PEEPMailbox.sortUserList = function(list){

    var sortedUserList = [];
    var usersWithCorrespondence = [];
    var usersFriendsOnline = [];
    var usersFriendsOffline = [];
    var usersMembersOnline = [];
    var usersMembersOffline = [];

    for (i in list)
    {
        var user = list[i];

        if (user.lastMessageTimestamp > 0){
            usersWithCorrespondence.push(user);
        }
        else{
            if (user.isFriend){
                if (user.status != 'offline'){
                    usersFriendsOnline.push(user);
                }
                else{
                    usersFriendsOffline.push(user);
                }
            }
            else{
                if (user.status != 'offline'){
                    usersMembersOnline.push(user);
                }
                else{
                    usersMembersOffline.push(user);
                }
            }
        }
    }

    usersWithCorrespondence.sort(function(user1,user2){
        return user2.lastMessageTimestamp - user1.lastMessageTimestamp;
    });

    for (i in usersWithCorrespondence)
    {
        sortedUserList.push(usersWithCorrespondence[i]);
    }

    usersFriendsOnline.sort(function(user1,user2){
        return user1.displayName.toLowerCase().localeCompare( user2.displayName.toLowerCase() );
    });

    for (i in usersFriendsOnline)
    {
        sortedUserList.push(usersFriendsOnline[i]);
    }

    usersFriendsOffline.sort(function(user1,user2){
        return user1.displayName.toLowerCase().localeCompare( user2.displayName.toLowerCase() );
    });

    for (i in usersFriendsOffline)
    {
        sortedUserList.push(usersFriendsOffline[i]);
    }

    usersMembersOnline.sort(function(user1,user2){
        return user1.displayName.toLowerCase().localeCompare( user2.displayName.toLowerCase() );
    });

    for (i in usersMembersOnline)
    {
        sortedUserList.push(usersMembersOnline[i]);
    }

    usersMembersOffline.sort(function(user1,user2){
        return user1.displayName.toLowerCase().localeCompare( user2.displayName.toLowerCase() );
    });

    for (i in usersMembersOffline)
    {
        sortedUserList.push(usersMembersOffline[i]);
    }

    return sortedUserList;
}

PEEPMailbox.log = function(text){
    if (im_debug_mode){console.log(text);}
}

PEEPMailbox.makeObservableSubject = function(){
    var observers = [];
    var addObserver = function (o) {
        if (typeof o !== 'function') {
            throw new Error('observer must be a function');
        }
        for (var i = 0, ilen = observers.length; i < ilen; i += 1) {
            var observer = observers[i];
            if (observer === o) {
                throw new Error('observer already in the list');
            }
        }
        observers.push(o);
    };
    var removeObserver = function (o) {
        for (var i = 0, ilen = observers.length; i < ilen; i += 1) {
            var observer = observers[i];
            if (observer === o) {
                observers.splice(i, 1);
                return;
            }
        }
        throw new Error('could not find observer in list of observers');
    };
    var notifyObservers = function (data) {
        // Make a copy of observer list in case the list
        // is mutated during the notifications.
        var observersSnapshot = observers.slice(0);
        for (var i = 0, ilen = observersSnapshot.length; i < ilen; i += 1) {
            observersSnapshot[i](data);
        }
    };
    return {
        addObserver: addObserver,
        removeObserver: removeObserver,
        notifyObservers: notifyObservers,
        notify: notifyObservers
    };
}

PEEPMailbox.uniqueId = function(prefix){

    prefix = prefix || '';

    return prefix + Math.random().toString(36).substr(2, 9);
}

PEEPMailbox.formatAMPM = function(date) {
    var hours = date.getHours();
    var minutes = date.getMinutes();
    var strTime = '00:00';

    if (PEEPMailbox.useMilitaryTime){
        minutes = minutes < 10 ? '0'+minutes : minutes;
        hours = hours < 10 ? '0'+hours : hours;
        strTime = hours + ':' + minutes;
    }
    else{
        var ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12;
        hours = hours ? hours : 12;
        minutes = minutes < 10 ? '0'+minutes : minutes;
        hours = hours < 10 ? '0'+hours : hours;
        strTime = hours + ':' + minutes + ampm;
    }

    return strTime;
}

PEEPMailbox.formatAttachmentFileName = function(fileName){
    var str = fileName;

    if (fileName.length > 20){
        str = fileName.substring(0, 10) + '...' + fileName.substring(fileName.length-10);
    }

    return str;
}

PEEPMailbox.formatAttachmentFileSize = function(size){

    if (size >= 1024){
        size = size / 1024;
        return '(' + size + 'MB)';
    }
    return '(' + size + 'KB)';
}

PEEPMailbox.NewMessageForm = {};

PEEPMailbox.NewMessageForm.Model = function(){
    var self = this;

}

PEEPMailbox.NewMessageForm.Controller = function(model){
    var self = this;

    self.model = model;
    self.newMessageWindowControl = $('#newMessageWindow');
    self.newMessageBtn = $('#newMessageBtn');
    self.minimizeBtn = $('#newMessageWindowMinimizeBtn');
    self.closeBtn = $('#newMessageWindowCloseBtn');
    self.deleteBtn = $('#userFieldDeleteBtn');
    self.form = peepForms['mailbox-new-message-form'];

    self.unselectedCapMinimizeBtn = $('#newMessageWindowUnselectedCapMinimizeBtn');
    self.unselectedCapCloseBtn = $('#newMessageWindowUnselectedCapCloseBtn');

    /**
     * Check new message window active mode
     * 
     * @return boolean
     */
    this.isNewMessageWindowActive = function() {
        return self.newMessageWindowControl.length && self.newMessageWindowControl.is(":visible");
    }
 
    /**
     * Close active mailbox window with confirmation
     * 
     * @param integer activeChats
     * @return boolean
     */
    this.closeNewMessageWindowWithConfirmation = function(activeChats) {
        if (this.isNewMessageWindowActive() && !activeChats) {
            var subject = peepForms['mailbox-new-message-form'].elements['subject'].getValue();
            var message = peepForms['mailbox-new-message-form'].elements['message'].getValue();

            // close the window without confirmation
            if (!$.trim(subject) && !$.trim(message)) {
                this.close();
                return true;
            }
 
            var result = confirm(PEEP.getLanguageText('mailbox', 'close_new_message_window_confirmation'));
            if (result) {
                // close the new mailbox window
                this.close();
            }
 
            return result;
        }

        return true;
    }

    this.close = function(){
        PEEP.trigger('mailbox.close_new_message_form');
    };

    this.minimize = function(e){
        if ($(e.target).attr('id') == self.deleteBtn.attr('id')){
            return;
        }

        if (self.newMessageWindowControl.hasClass('peep_active')){
            PEEP.trigger('mailbox.minimize_new_message_form');
        }
        else{
            PEEP.trigger('mailbox.open_new_message_form');
        }
    };

    this.setUser = function( data ){

        $('#userFieldProfileLink', this.newMessageWindowControl).attr('href', data.profileUrl);
        $('#userFieldDisplayname', this.newMessageWindowControl).html( data.displayName );
        $('#userFieldAvatar', this.newMessageWindowControl).attr('src', data.avatarUrl);

        $('.peep_chat_block', this.newMessageWindowControl).removeClass('peep_mailchat_select_user_wrap');
        $('.peep_chat_block', this.newMessageWindowControl).addClass('peep_mailchat_selected_user_wrap');
    };

    this.resetUser = function(){
        $('.peep_chat_block', this.newMessageWindowControl).addClass('peep_mailchat_select_user_wrap');
        $('.peep_chat_block', this.newMessageWindowControl).removeClass('peep_mailchat_selected_user_wrap');
    };
    
    this._prepareOpponent = function(data) {
        var opponent = PEEP.Mailbox.usersCollection.findWhere({opponentId: data.opponentId});

        if (!opponent) {
            PEEP.Mailbox.usersCollection.add(data);
        }
    };
    
    this.openForm = function( data ) {
        self.newMessageWindowControl.addClass('peep_open');
        self.newMessageWindowControl.addClass('peep_active');
        self.newMessageWindowControl.removeClass('peep_chat_dialog_active');

        var storage = PEEPMailbox.getStorage();
        storage.setItem('mailbox.new_message_form_opened', 1);

        if (data) {
            
            self._prepareOpponent(data);
            self.form.elements['opponentId'].setValue(data.opponentId);
        }

        PEEP.trigger('mailbox.new_message_form_opened');
    };
    
    this.openFormMinimized = function( data ) {
        self.newMessageWindowControl.addClass('peep_open');
        self.newMessageWindowControl.addClass('peep_chat_dialog_active');
    };
    
    this.closeForm = function() {
        self.form.elements['opponentId'].resetValue();
        self.form.elements['subject'].resetValue();
        self.form.elements['message'].resetValue();

        self.newMessageWindowControl.removeClass('peep_open');
        self.newMessageWindowControl.removeClass('peep_active');

        var storage = PEEPMailbox.getStorage();
        storage.removeItem('mailbox.new_message_form_opened');
        storage.removeItem('mailbox.new_message_form_opponent_id');
        storage.removeItem('mailbox.new_message_form_opponent_info');
        storage.removeItem('mailbox.new_message_form_subject');
        storage.removeItem('mailbox.new_message_form_message');

        PEEP.trigger('mailbox.new_message_form_closed');
        PEEP.removeScroll( $('#userFieldAutocompleteControl') );
    };

    this.minimizeForm = function() {
        self.newMessageWindowControl.removeClass('peep_active');
        self.newMessageWindowControl.addClass('peep_chat_dialog_active');

        var storage = PEEPMailbox.getStorage();
        storage.setItem('mailbox.new_message_form_opened', 0);

        PEEP.trigger('mailbox.new_message_form_minimized');
    };

    self.deleteBtn.bind('click', function(){
        self.form.elements['opponentId'].resetValue();
        self.form.elements['opponentId'].focus();
    });
    self.newMessageBtn.bind('click', function(){
        PEEP.trigger('mailbox.open_new_message_form');
    });
    self.minimizeBtn.bind('click', this.minimize);
    self.unselectedCapMinimizeBtn.bind('click', this.minimize);

    self.unselectedCapCloseBtn.bind('click', this.close);
    self.closeBtn.bind('click', this.close);

    $('.newMessageWindowSubjectInputControl').keyup(function(ev){

        var storage = PEEPMailbox.getStorage();
        storage.setItem('mailbox.new_message_form_subject', $(this).val());

    });

    $('.newMessageWindowMessageInputControl').keyup(function(ev){

        if (ev.which === 13 && !ev.ctrlKey && !ev.shiftKey) {
            ev.preventDefault();
            return false;
        }

        var storage = PEEPMailbox.getStorage();
        storage.setItem('mailbox.new_message_form_message', $(this).val());
    });

//
//    $(self.form.elements['subject'].input).bind('blur.invitation', {formElement:self.form.elements['subject']},
//        function(e){
//            el = $(this);
//            if( el.val() == '' || el.val() == e.data.formElement.invitationString){
//                el.addClass('invitation');
//                el.val(e.data.formElement.invitationString);
//            }
//            else{
//                el.unbind('focus.invitation').unbind('blur.invitation');
//            }
//        });

//    $(self.form.elements['message'].input).bind('blur.invitation', {formElement:self.form.elements['message']},
//        function(e){
//            el = $(this);
//            if( el.val() == '' || el.val() == e.data.formElement.invitationString){
//                el.addClass('invitation');
//                el.val(e.data.formElement.invitationString);
//            }
//            else{
//                el.unbind('focus.invitation').unbind('blur.invitation');
//            }
//        });

    // Global Binds
    
    PEEP.bind('mailbox.open_new_message_form', function(data){
        self.openForm(data);
    });

    PEEP.bind('mailbox.open_new_message_form_minimized', function(data) {
        self.openFormMinimized(data);
    });

    PEEP.bind('mailbox.close_new_message_form', function(){
        self.closeForm();
    });

    PEEP.bind('mailbox.minimize_new_message_form', function(){
        self.minimizeForm();
    });
};

PEEPMailbox.NewMessageForm.restoreForm = function(){
    var storage = PEEPMailbox.getStorage();

    var newMessageFormOpened = storage.getItem('mailbox.new_message_form_opened');
    if (typeof newMessageFormOpened != 'undefined' && newMessageFormOpened != null){
        if (newMessageFormOpened == "1"){
            PEEP.trigger('mailbox.open_new_message_form');
        }
        else{
            PEEP.trigger('mailbox.open_new_message_form_minimized');
        }
    }

    var opponentInfo = storage.getItem('mailbox.new_message_form_opponent_info');
    if (typeof opponentInfo != 'undefined' && opponentInfo != null){
        peepForms['mailbox-new-message-form'].elements['opponentId'].setValue(JSON.parse(opponentInfo));
    }
    else{
        var opponentId = storage.getItem('mailbox.new_message_form_opponent_id');
        if (typeof opponentId != 'undefined' && opponentId != null){
            peepForms['mailbox-new-message-form'].elements['opponentId'].setValue(opponentId);
        }

    }

    var subject = storage.getItem('mailbox.new_message_form_subject');
    if (typeof subject != 'undefined' && subject != null){
        peepForms['mailbox-new-message-form'].elements['subject'].setValue(subject);
    }

    var message = storage.getItem('mailbox.new_message_form_message');
    if (typeof message != 'undefined' && message != null){
        peepForms['mailbox-new-message-form'].elements['message'].setValue(message);
    }

}

PEEP_MailboxConsole = function( itemKey, params ){
    var self = this;
    var listLoaded = false;

    self.model = PEEP.Console.getData(itemKey);
    var list = PEEP.Console.getItem(itemKey);
    var counter = new PEEP_DataModel();

    counter.addObserver(this);

    this.onDataChange = function( data ){
        var counterNumber = 0,
            newCount = data.get('counter.new');
        counterNumber = newCount > 0 ? newCount : data.get('counter.all');

        list.setCounter(counterNumber, newCount > 0);

        if ( counterNumber > 0 ){
            list.showItem();
        }
    };

    this.setCounterData = function( data ){
        var counterNumber = 0,
            newCount = data.new;
        counterNumber = newCount > 0 ? newCount : data.all;

        list.setCounter(counterNumber, newCount > 0);

        if ( counterNumber > 0 ){
            list.showItem();
        }
    };

    list.onHide = function(){
        list.setCounter(counter.get('all'), false);
        self.model.set('counter', counter.get());
    };

    list.onShow = function(){
        if ( params.issetMails == false && counter.get('all') <= 0 ){
            this.showNoContent();

            return;
        }

        this.loadList();
    };

    self.model.addObserver(function(){
        if ( !list.opened ){
            counter.set(self.model.get('counter'));
        }
    });

    this.updateCounter = function(conversation){

        var markedUnreadNotViewedConversations = PEEP.Mailbox.conversationsCollection.where({conversationViewed: false});
        var markedUnreadConversations = PEEP.Mailbox.conversationsCollection.where({conversationRead: 0});

        //var all = markedUnreadConversations.length;
        //if (PEEP.Mailbox.markedUnreadConversationList.length > markedUnreadConversations.length){
        //    all = PEEP.Mailbox.markedUnreadConversationList.length;
        //}
        var all = PEEP.Mailbox.markedUnreadConversationList.length;

        var data = {'new': markedUnreadNotViewedConversations.length, 'all': all};
//        this.setCounterData( data );
        this.model.set('counter', data, true);
    }

    PEEP.bind('mailbox.application_started', function(){
        PEEP.Mailbox.conversationsCollection.on('add', self.updateCounter, self);
        PEEP.Mailbox.conversationsCollection.on('change', self.updateCounter, self);

        self.updateCounter();
    });

    this.sendMessageBtn = $('#mailboxConsoleListSendMessageBtn');

    this.sendMessageBtn.bind('click', function(){
        PEEP.trigger('mailbox.open_new_message_form');
        PEEP.Console.getItem('mailbox').hideContent();
    });

}

PEEP.MailboxConsole = null;

var SearchField = function( id, name, invitationString ){

    var self = this;
    var formElement = new PeepFormElement(id, name);
    if( invitationString ){
        addInvitationBeh(formElement, invitationString);

        $(formElement.input).bind('blur.invitation', {formElement:formElement},
        function(e){
             el = $(this);
             if( el.val() == '' || el.val() == e.data.formElement.invitationString){
                 el.addClass('invitation');
                 el.val(e.data.formElement.invitationString);
             }
             else{
                el.unbind('focus.invitation').unbind('blur.invitation');
             }
         });
    }

    formElement.handler = null;

    formElement.setHandler = function(obj){
        formElement.handler = obj;
    }

    $(formElement.input).keydown(function(ev){

        if (ev.which === 13 && !ev.ctrlKey && !ev.shiftKey) {
            ev.preventDefault();

            return false;
        }
    });

    $(formElement.input).keyup(function(ev){

        if (ev.which === 13 && !ev.ctrlKey && !ev.shiftKey) {
            ev.preventDefault();

            return false;
        }

        formElement.handler.updateList($(this).val());
    });

    $('#'+name+'_close_btn_search').click(function(){
        $(formElement.input).val('');
        $(formElement.input).focus();

        formElement.handler.updateList($(formElement.input).val());
        $('#mailboxConvOptionSelectAll').prop('checked', false);
    });

    return formElement;
}
var MailboxUserField = function( id, name, invitationString ){
    var self = this;

    var formElement = new PeepFormElement(id, name);

    var textFormElement = new PeepFormElement('mailbox_new_message_user', 'mailbox_new_message_user');
//    if( invitationString ){
//        addInvitationBeh(textFormElement, invitationString);
//        $(textFormElement.input).bind('blur', {formElement:textFormElement},
//            function(e){
//                el = $(this);
//                if( el.val() == '' || el.val() == e.data.formElement.invitationString){
//                    el.addClass('invitation');
//                    el.val(e.data.formElement.invitationString);
//                }
//
//            });
//    }

    this.contacts = {};
    this.inputControl = $('.userFieldInputControl');
    this.autocompleteControl = $('#userFieldAutocompleteControl');
    this.userList = $('#userFieldUserList');
    this.userListItemPrototype = $('#userFieldUserListItemPrototype');
    this.syncing = false;

    $(document).click(function( e ){
        if ( !$(e.target).is(':visible') ){
            return;
        }

        var isContent = self.autocompleteControl.find(e.target).length;
        if ( !isContent ){
            self.autocompleteControl.hide();
        }
    });

    this.addItem = function( data ) {

        if (typeof this.contacts[data.get('opponentId')] != 'undefined'){
            return $('#userFieldUserListItem-'+data.get('opponentId'));
        }

        var item = $('#userFieldUserListItemPrototype').clone();
        $('#userFieldUserListItemAvatarUrl', item).attr('src', data.get('avatarUrl'));
        $('#userFieldUserListItemUsername', item).html(data.get('displayName'));
        item.attr('id', 'userFieldUserListItem-'+data.get('opponentId'));
        item.data(data);

        item.click(function(){
            var data = $(this).data();
            formElement.setValue(data);
            self.reset();
        });

        this.userList.append(item);

        this.contacts[data.get('opponentId')] = data;

        return item;
    };

    this.reset = function(){
        self.autocompleteControl.hide();
        $.each(self.contacts, function(id, contact){
            self.removeItem(contact.opponentId);
        });
    }

    formElement.setValue = function(value){

        var storage = PEEPMailbox.getStorage();

        if (value == ''){
            $(formElement.input).val(value);
            storage.setItem('mailbox.new_message_form_opponent_id', null);
            storage.setItem('mailbox.new_message_form_opponent_info', null);
        }
        else{
            var user = null;
            var opponentId = parseInt(value);
            if (opponentId > 0){
                user = PEEP.Mailbox.usersCollection.findWhere({opponentId: opponentId});
            }
            else{
                user = value;
            }

            if (user.hasOwnProperty('opponentId')){
                $(formElement.input).val(user.opponentId);
                $(textFormElement.input).val(user.displayName);
                PEEP.Mailbox.newMessageFormController.setUser(user);

                storage.setItem('mailbox.new_message_form_opponent_id', user.opponentId);
                storage.setItem('mailbox.new_message_form_opponent_info', JSON.stringify(user));
                return;
            }
            else{
                if (user){
                    $(formElement.input).val(user.get('opponentId'));
                    $(textFormElement.input).val(user.get('displayName'));
                    PEEP.Mailbox.newMessageFormController.setUser(user.attributes);

                    storage.setItem('mailbox.new_message_form_opponent_id', user.get('opponentId'));
                    storage.setItem('mailbox.new_message_form_opponent_info', JSON.stringify(user.attributes));
                    return;
                }
            }
        }
    }

    formElement.resetValue = function(){
        var storage = PEEPMailbox.getStorage();

        $(formElement.input).val('');
        $(textFormElement.input).val('');
        PEEP.Mailbox.newMessageFormController.resetUser();

        storage.removeItem('mailbox.new_message_form_opponent_id');
        storage.removeItem('mailbox.new_message_form_opponent_info');
    }

    formElement.focus = function(){
        $(textFormElement.input).focus();
    }

    this.removeItem = function( opponentId ){
        $('#userFieldUserListItem-'+opponentId).remove();
        delete self.contacts[opponentId];
//        PEEP.updateScroll(self.autocompleteControl);
    };

    this.updateList = function(name){
        var self = this;
        var contactList = this.contacts;

        $('#userFieldUserListItem-notfound').hide();
//        PEEP.removeScroll(self.autocompleteControl);

        if (name == ''){

            self.reset();
        }
        else{

            if (name.length < 2){
                return;
            }

            self.autocompleteControl.show();

            //TODO refactor from regexp to something more efficient
            var expr = new RegExp('(^'+name+'.*)|(\\s'+name+'.*)', 'i');

            $.each(contactList, function(id, contact){
                if (!expr.test(contact.get('displayName'))){
                    self.removeItem(contact.get('opponentId'));
                }
                else{
                    $('#userFieldUserListItem-'+contact.get('opponentId')).show();
                }
            });

            _.each(PEEP.Mailbox.usersCollection.models, function(user){

                if (expr.test(user.get('displayName'))){
                    if (!contactList.hasOwnProperty(user.get('opponentId'))){
                        var item = self.addItem(user);
                        item.show();
                    }
                }
            });

            if (!self.syncing){
                self.syncing = true;
                $.getJSON(PEEPMailbox.userSearchResponderUrl, {term: name, idList: {}, context: 'user'}, function( data ) {

                    _.each(data, function(user){
                        var usr = new MAILBOX_User(user.data);
                        var item = self.addItem(usr);
                        item.show();
                    });

                    var size = 0;

                    for (key in self.contacts) {
                        if (self.contacts.hasOwnProperty(key)) size++;
                    }

                    //TODO show not found only when it really not found on server after ajax call
                    if (size == 0){
                        $('#userFieldUserListItem-notfound').show();
                    }
                    else{
                        $('#userFieldUserListItem-notfound').hide();
//                        if (size > 8){
//                            if (self.autocompleteControl.hasClass('peep_scrollable')){
//                                PEEP.updateScroll($('#userFieldAutocompleteControl'));
//                            }
//                            else{
//                                PEEP.addScroll($('#userFieldAutocompleteControl'));
//                            }
//                        }
                    }

                    self.syncing = false;
                });
            }

        }
    }

    $("#userFieldUserList").mouseover(function(){
        $("#userFieldUserList li.userFieldUserListItem").removeClass("selected");
    });

//    $('.peep_mailchat_autocomplete_inner').on('scroll', function(){
//
//        if ( $('.peep_mailchat_autocomplete_inner').scrollTop() + $('#userFieldUserList').position().top == 0 )
//        {
//            console.log('load more');
//        }
//    });

    $(textFormElement.input).keydown(function(ev){

        if (ev.keyCode == 13) {
            if ($("#userFieldUserList").is(":visible")) {

                $('#userFieldUserList li.selected').click();

            } else {
                self.autocompleteControl.show();
            }

            ev.preventDefault();
            return false;
        }

        if (ev.keyCode == 38){
            var selected = $("#userFieldUserList li.userFieldUserListItem.selected");
            $("#userFieldUserList li.userFieldUserListItem").removeClass("selected");

            if (selected.prev().length == 0) {
                selected.siblings().last().addClass("selected");
            } else {
                selected.prev().addClass("selected");
            }

            ev.preventDefault();
        }

        if (ev.keyCode == 40) {
            var selected = $("#userFieldUserList li.userFieldUserListItem.selected");

            if (selected.length == 0){
                selected = $("#userFieldUserList li.userFieldUserListItem");
                selected = $(selected[0]);
                selected.addClass("selected");
                return;
            }

            $("#userFieldUserList li.userFieldUserListItem").removeClass("selected");
            if (selected.next().length == 0) {
                var first = $("#userFieldUserList li.userFieldUserListItem");
                first = $(first[0]);
                first.addClass("selected");
            } else {
                selected.next().addClass("selected");
            }

            ev.preventDefault();
        }
    });

    $(textFormElement.input).keyup(function(ev){

        if (ev.which === 13 && !ev.ctrlKey && !ev.shiftKey) {
            ev.preventDefault();

            return false;
        }

        if (ev.which == 38 || ev.which == 40){
            return false;
        }

        self.updateList($(this).val());
    });

    return formElement;
}

function htmlspecialchars(string, quote_style, charset, double_encode) {
    // Convert special characters to HTML entities
    //
    // version: 1109.2015
    // discuss at: http://phpjs.org/functions/htmlspecialchars    // +   original by: Mirek Slugen
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   bugfixed by: Nathan
    // +   bugfixed by: Arno
    // +    revised by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)    // +    bugfixed by: Brett Zamir (http://brett-zamir.me)
    // +      input by: Ratheous
    // +      input by: Mailfaker (http://www.weedem.fr/)
    // +      reimplemented by: Brett Zamir (http://brett-zamir.me)
    // +      input by: felix    // +    bugfixed by: Brett Zamir (http://brett-zamir.me)
    // %        note 1: charset argument not supported
    // *     example 1: htmlspecialchars("<a href='test'>Test</a>", 'ENT_QUOTES');
    // *     returns 1: '&lt;a href=&#039;test&#039;&gt;Test&lt;/a&gt;'
    // *     example 2: htmlspecialchars("ab\"c'd", ['ENT_NOQUOTES', 'ENT_QUOTES']);    // *     returns 2: 'ab"c&#039;d'
    // *     example 3: htmlspecialchars("my "&entity;" is still here", null, null, false);
    // *     returns 3: 'my &quot;&entity;&quot; is still here'
    var optTemp = 0,
        i = 0,        noquotes = false;
    if (typeof quote_style === 'undefined' || quote_style === null) {
        quote_style = 2;
    }
    string = string.toString();
    if (double_encode !== false) { // Put this first to avoid double-encoding
        string = string.replace(/&/g, '&amp;');
    }
    string = string.replace(/</g, '&lt;').replace(/>/g, '&gt;');
    var OPTS = {
        'ENT_NOQUOTES': 0,
        'ENT_HTML_QUOTE_SINGLE': 1,
        'ENT_HTML_QUOTE_DOUBLE': 2,
        'ENT_COMPAT': 2,
        'ENT_QUOTES': 3,
        'ENT_IGNORE': 4
    };
    if (quote_style === 0) {
        noquotes = true;
    }
    if (typeof quote_style !== 'number') { // Allow for a single string or an array of string flags
        quote_style = [].concat(quote_style);
        for (i = 0; i < quote_style.length; i++) {
            // Resolve string input to bitwise e.g. 'ENT_IGNORE' becomes 4
            if (OPTS[quote_style[i]] === 0) {
                noquotes = true;
            }
            else
            if (OPTS[quote_style[i]])
            {
                optTemp = optTemp | OPTS[quote_style[i]];
            }
        }
        quote_style = optTemp;
    }

    if (quote_style & OPTS.ENT_HTML_QUOTE_SINGLE){
        string = string.replace(/'/g, '&#039;');
    }
    if (!noquotes){
        string = string.replace(/"/g, '&quot;');
    }
    string = string.replace(/\n/g, '<br />');
    return string;
}

function im_createCookie(name,value,days) {
    if (days) {
        var date = new Date();
        date.setTime(date.getTime()+(days*24*60*60*1000));
        var expires = "; expires="+date.toGMTString();
    }
    else var expires = "";
    document.cookie = name+"="+value+expires+"; path=/";
}

function im_readCookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for(var i=0;i < ca.length;i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1,c.length);
        if ( c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
    }
    return null;
}

function im_eraseCookie(name) {
    im_createCookie(name,"",-1);
}

//if (typeof window.peepFileAttachments == 'undefined'){
//    $.getScript('/static/plugins/base/js/attachments.js');
//}

$(function(){

    $.fn.extend({
        autolink: function(options){
            var exp =  new RegExp("(\\b(https?|ftp|file)://[-A-Z0-9+&amp;@#\\/%?=~_|!:,.;]*[-A-Z0-9+&amp;@#\\/%=~_|])", "ig");            

            this.each( function(id, item){

                if ($(item).html() == ""){
                    return 1;
                }
                var text = $(item).html().replace(exp,"<a href='$1' target='_blank'>$1</a>");
                $(item).html( text );

            });

            return this;
        },

        dialogAutosize: function(options, action){

            var self = this;

            this.adjust = function(){
                var textWidth = this.val().width(this.css('font'));
                var lines = this.val().split("\n");
                var linesLength = 1;

                if (textWidth > this.width()){
                    linesLength = Math.ceil( textWidth / this.width() );
                    if (linesLength < lines.length){
                        linesLength = lines.length;
                    }
                }
                else{
                    linesLength = lines.length;
                }

                this.attr('rows', linesLength);
                var offset = 0;
                for (var i=1; i<=linesLength; i++){

                    if (i == 2){
                        offset = offset + 12;
                        $('.peep_chat_message', options.control).removeClass('scroll');
                    }
                    else{
                        if (i >= 3 && i <= 6){
                            offset = offset + 17;
                            $('.peep_chat_message', options.control).removeClass('scroll');
                        }
                        else{
                            if (i > 6){
                                $('.peep_chat_message', options.control).addClass('scroll');
                                offset = 80;
                                break;
                            }
                        }
                    }
                }
                this.css('height', options.textareaHeight + offset);
                options.messageListControl.height( options.dialogWindowHeight - offset );
                options.scrollDialog();
            }

            if (!action){

                this.adjust();


                this.bind('paste', function(e){
                    var element = this;
                    setTimeout(function(){
                        self.adjust();
                    }, 50);
                });

                this.bind('cut', function(e){
                    var element = this;
                    setTimeout(function(){
                        self.adjust();
                    }, 50);
                });

                this.keypress(function(ev){
                    self.adjust();
                });

                this.keyup(function (ev) {
                    if (ev.which === 13 && ev.shiftKey){
                        self.adjust();
                    }

                    if (ev.which === 8){
                        self.adjust();
                    }
                });

                this.keydown(function (ev) {

                    if (ev.which === 13 && !ev.shiftKey){
                        ev.preventDefault();

                        var body = $(this).val();

                        if ( $.trim(body) == '')
                            return;

                        options.sendMessage(body);

                        if (options.dialogWindowHeight > 0){
                            options.messageListControl.height( options.dialogWindowHeight );
                        }

                        $(this).attr('rows', 1);
                        $(this).css('height', options.textareaHeight);

                        options.scrollDialog();
                    }
                    else if (ev.which === 13 && ev.shiftKey){
                        self.adjust();
                    }
                    else if (ev.which === 8){
                        self.adjust();
                    }
                    else{
                        self.adjust();
                    }
                });
            }
            else{
                if (action == 'adjust'){
                    this.adjust();
                }
            }
        }
    });

    AudioPlayer.setup(PEEPMailbox.soundSwfUrl, { width: 100 });

    onerror = function(e) {
        return false;
    };

    onunload = function() {
        return false;
    };

    PEEP.bind('mailbox.ready', function(){
        PEEP.trigger('mailbox.application_started');
        PEEP.Mailbox.appStarted = true;
    });

    PEEP.bind('base.online_now_click',
        function(userId){

            if (parseInt(userId) != PEEPMailbox.userDetails.userId){
                $('#peep_chat_now_'+userId).addClass('peep_hidden');
                $('#peep_preloader_content_'+userId).removeClass('peep_hidden');

                $.post(PEEPMailbox.openDialogResponderUrl, {
                    userId: userId
                }, function(data){

                    if ( typeof data != 'undefined'){
                        if ( typeof data['warning'] != 'undefined' && data['warning'] ){
                            PEEP.message(data['message'], data['type']);
                            return;
                        }
                        else{
                            if (data['use_chat'] && data['use_chat'] == 'promoted'){
                                PEEP.Mailbox.contactManagerView.showPromotion();
                            }
                            else{
                                PEEP.Mailbox.usersCollection.add(data);
                                PEEP.trigger('mailbox.open_dialog', {convId: data['convId'], opponentId: data['opponentId'], mode: 'chat'});
                            }
                        }
                    }
                }, 'json').complete(function(){

                        $('#peep_chat_now_'+userId).removeClass('peep_hidden');
                        $('#peep_preloader_content_'+userId).addClass('peep_hidden');
                    });
            }
        });

//    PEEP.bind('base.sign_out_click',
//        function(userId){
//        });

});
