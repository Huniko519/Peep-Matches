<?php

PEEP::getRegistry()->addToArray('join_connect_hook', 'redisgn_form');

function redisgn_form() {
    $sBirthdateMonth = 'month_' . array_search('birthdate', $_SESSION["join.real_question_list"]);
    $sBirthdateDay = 'day_' . array_search('birthdate', $_SESSION["join.real_question_list"]);
    $sBirthdateYear = 'year_' . array_search('birthdate', $_SESSION["join.real_question_list"]);
    $sMatchGender = array_search('match_sex', $_SESSION["join.real_question_list"]);

    return '<script>jQuery( document ).ready(function() {
        jQuery("input[name=email]").val("'.$_POST['email'].'");
        jQuery("select[name='.$sBirthdateMonth.'] option[value='.$_POST['month_birthday'].']").prop("selected", true);
        jQuery("select[name='.$sBirthdateDay.'] option[value='.$_POST['day_birthday'].']").prop("selected", true);
        jQuery("select[name='.$sBirthdateYear.'] option[value='.$_POST['year_birthday'].']").prop("selected", true);
        jQuery("input[name='.$sMatchGender.'\\\[\\\]][value='.$_POST['match_sex'].']").prop("checked", true);
    });</script>';
}