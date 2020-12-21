<?php

PEEP::getRouter()->addRoute(
    new PEEP_Route('usercredits.admin', 'admin/plugins/credits/', 'USERCREDITS_CTRL_Admin', 'index')
);

PEEP::getRouter()->addRoute(
    new PEEP_Route('usercredits.admin_settings', 'admin/plugins/credits/settings', 'USERCREDITS_CTRL_Admin', 'settings')
);

PEEP::getRouter()->addRoute(
    new PEEP_Route('usercredits.admin_packs', 'admin/plugins/credits/packs', 'USERCREDITS_CTRL_Admin', 'packs')
);

PEEP::getRouter()->addRoute(
    new PEEP_Route('usercredits.buy_credits', 'credits/buy', 'USERCREDITS_CTRL_BuyCredits', 'index')
);

PEEP::getRouter()->addRoute(
    new PEEP_Route('usercredits.history', 'credits/history', 'USERCREDITS_CTRL_Credits', 'history')
);

USERCREDITS_CLASS_EventHandler::getInstance()->init();