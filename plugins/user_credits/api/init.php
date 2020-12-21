<?php

PEEP::getRouter()->addRoute(
    new PEEP_Route('usercredits.buy_credits', 'credits/buy', 'USERCREDITS_CTRL_BuyCredits', 'index')
);

USERCREDITS_CLASS_EventHandler::getInstance()->genericInit();