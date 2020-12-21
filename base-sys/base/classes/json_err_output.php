<?php

class BASE_CLASS_JsonErrOutput extends BASE_CLASS_ErrOutput
{
    public function printString( $data )
    {
        echo json_encode($data);
    }
}
