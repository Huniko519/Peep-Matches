<?php
$languageService = Updater::getLanguageService();

$languages = $languageService->getLanguages();
$langId = null;

foreach ($languages as $lang)
{
    if ($lang->tag == 'en')
    {
        $langId = $lang->id;
        break;
    }
}

if ($langId !== null)
{
    $languageService->addOrUpdateValue($langId, 'corevisitor', 'visitor_users_count', '&lt;div class="clearfix"&gt;&#13;&lt;b&gt;&#13;{$count}&lt;/b&gt;&#13;&lt;p&gt;&#13; Joined&lt;/p&gt;&#13;&lt;/div&gt;&#13;');
  
    $languageService->addOrUpdateValue($langId, 'corevisitor', 'visitor_promo_txt', '&lt;b>more than dating site!&lt;/b&gt;&#13;
&lt;br/&gt;&#13;
&lt;t&gt;&#13;Here you can add contacts and meet new people for ...&lt;/t&gt;&#13;&lt;div class="base_welcome_icons"&gt;&#13;&lt;f&gt;&#13;Dating&lt;/f&gt;&#13;&lt;f&gt;&#13;Chat&lt;/f&gt;&#13;&lt;f&gt;&#13;Share&lt;/f&gt;&#13;&lt;/div&gt;&#13;');

    $languageService->addOrUpdateValue($langId, 'corevisitor', 'members_found', 'Members Found');
    
}