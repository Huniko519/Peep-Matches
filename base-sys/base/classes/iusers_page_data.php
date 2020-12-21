<?php

//TODO delete interface


interface BASE_CLASS_IusersPageData
{
    public function getMenuItem();

    public function isCase();

    public function getCase();

    public function getData( $first, $count );
}
