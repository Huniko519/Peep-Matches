<?php

interface PEEP_ICacheBackend
{
    public function save( $data, $key, array $tags = array(), $expTime );
    public function load( $key );
    public function test( $key );
    public function remove( $key );
    public function clean( array $tags, $mode );
}