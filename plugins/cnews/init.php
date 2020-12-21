<?php

$plugin = PEEP::getPluginManager()->getPlugin("cnews");

PEEP::getRouter()->addRoute(new PEEP_Route('cnews_admin_settings', 'admin/plugins/cnews', 'CNEWS_CTRL_Admin', 'index'));
PEEP::getRouter()->addRoute(new PEEP_Route('cnews_admin_customization', 'admin/plugins/cnews/customization', 'CNEWS_CTRL_Admin', 'customization'));

PEEP::getRouter()->addRoute(new PEEP_Route('cnews_view_item', 'cnews/:actionId', 'CNEWS_CTRL_Feed', 'viewItem'));

$eventHandler = CNEWS_CLASS_EventHandler::getInstance();
$eventHandler->genericInit();

PEEP::getEventManager()->bind(PEEP_EventManager::ON_BEFORE_PLUGIN_DEACTIVATE, array($eventHandler, 'onPluginDeactivate'));
PEEP::getEventManager()->bind(PEEP_EventManager::ON_AFTER_PLUGIN_ACTIVATE, array($eventHandler, 'onPluginActivate'));
PEEP::getEventManager()->bind(PEEP_EventManager::ON_BEFORE_PLUGIN_UNINSTALL, array($eventHandler, 'onPluginUninstall'));
PEEP::getEventManager()->bind('feed.on_item_render', array($eventHandler, 'desktopItemRender'));
PEEP::getEventManager()->bind(BASE_CMP_ProfileActionToolbar::EVENT_NAME, array($eventHandler, 'onCollectProfileActions'));
PEEP::getEventManager()->bind('feed.on_item_render', array($eventHandler, 'feedItemRenderFlagBtn'));

// Formats
CNEWS_CLASS_FormatManager::getInstance()->init();

/* Built-in Formats */
CNEWS_CLASS_FormatManager::getInstance()->addFormat("text", "CNEWS_FORMAT_Text");
CNEWS_CLASS_FormatManager::getInstance()->addFormat("image", "CNEWS_FORMAT_Image");
CNEWS_CLASS_FormatManager::getInstance()->addFormat("image_list", "CNEWS_FORMAT_ImageList");
CNEWS_CLASS_FormatManager::getInstance()->addFormat("image_content", "CNEWS_FORMAT_ImageContent");
CNEWS_CLASS_FormatManager::getInstance()->addFormat("content", "CNEWS_FORMAT_Content");
CNEWS_CLASS_FormatManager::getInstance()->addFormat("video", "CNEWS_FORMAT_Video");

function viewmore(){
    $image = PEEP::getPluginManager()->getPlugin('cnews')->getStaticUrl() . 'image/newsloading.gif';

   $config = PEEP::getConfig();
   
   $autoclick = $config->getValue('autoviewmore', 'autoclick');
   
   $script = '<script type="text/javascript">';
   $script .= '$(window).scroll(function() {
         var final = "input[class=' . "'peep_cnews_view_more peep_ic_down_arrow']" . '";
         jQuery( ".peep_cnews_view_more_c .peep_button" ).css( "background", "transparent" );
         jQuery( ".peep_cnews_view_more_c .peep_button" ).css( "border", "none" );
         jQuery( final ).css( "font-size", "0" );
         jQuery( final ).css( "background", "transparent" );
if($(window).scrollTop() + $(window).height() > $(document).height() - ' . $autoclick . ' && $( final ).is(":visible")) {
       $( final ).click();
       $( "#feed1 .peep_cnews_view_more_c" ).append(\'<img src="' . $image . '">\' );
   }
   
if($(window).scrollTop() + $(window).height() < $(document).height() - ' . $autoclick . ' || $( final ).is(":hidden")) {

       $( "#feed1 .peep_cnews_view_more_c img" ).remove();
   }
});' . '</script>';
   
    PEEP::getDocument()->appendBody($script);

}


 if ( !PEEP::getConfig()->configExists('autoviewmore', 'autoclick') ) 
    PEEP::getConfig()->addConfig('autoviewmore', 'autoclick', '150', '');

PEEP::getEventManager()->bind('core.finalize', 'viewmore');