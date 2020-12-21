<?php

class MAILBOX_CMP_ConversationList extends PEEP_Component
{
    public function __construct($params = array())
    {
        parent::__construct();

        PEEP::getDocument()->addScript( PEEP::getPluginManager()->getPlugin('mailbox')->getStaticJsUrl().'conversation_list.js', 'text/javascript', 3008 );

        $defaultAvatarUrl = BOL_AvatarService::getInstance()->getDefaultAvatarUrl();
        $this->assign('defaultAvatarUrl', $defaultAvatarUrl);

        $js = "var conversationListModel = new MAILBOX_ConversationListModel;
";

        if (!empty($params['conversationId']))
        {
            $js .= "conversationListModel.set('activeConvId', {$params['conversationId']});";
            $js .= "conversationListModel.set('pageConvId', {$params['conversationId']});";
        }

        $js .= "PEEP.Mailbox.conversationListController = new MAILBOX_ConversationListView({model: conversationListModel});";

        PEEP::getDocument()->addOnloadScript($js, 3009);

        $conversationSearchForm = new Form('conversationSearchForm');
        $search = new MAILBOX_CLASS_SearchField('conversation_search');
        $search->setHasInvitation(true);
        $search->setInvitation( PEEP::getLanguage()->text('mailbox', 'label_invitation_conversation_search') );

        PEEP::getLanguage()->addKeyForJs('mailbox', 'label_invitation_conversation_search');

        $conversationSearchForm->addElement($search);
        $this->addForm($conversationSearchForm);

        $modeList = MAILBOX_BOL_ConversationService::getInstance()->getActiveModeList();
        $singleMode = count($modeList) == 1;
        $this->assign('singleMode', $singleMode);
    }
}