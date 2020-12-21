<?php

function smarty_modifier_more( $string, $length )
{
    $truncated = UTIL_String::truncate($string, $length);
    
    if ( strlen($string) - strlen($truncated) < 50 )
    {
        return $string;
    }
    
    $uniqId = uniqid("more-");
    $seeMoreEmbed = '<a href="javascript://" class="peep_small" onclick="$(\'#' . $uniqId . '\').attr(\'data-collapsed\', 0);" style="padding-left:4px;">' 
            . PEEP::getLanguage()->text("base", "comments_see_more_label") 
            . '</a>';
    
    return '<span class="peep_more_text" data-collapsed="1" id="' . $uniqId . '">'
            . '<span data-text="full">' . $string . '</span>'
            . '<span data-text="truncated">' . $truncated
            . '...' . $seeMoreEmbed
            . '</span>'
            . '</span>';
}