<?php

class BASE_CLASS_FileLogWriter extends PEEP_LogWriter
{
    /**
     * @var string
     */
    private $path;

    /**
     * Constructor.
     */
    public function __construct( $path )
    {
        $this->path = $path;
    }

    /**
     * @param array $entries
     */
    public function processEntries( array $entries )
    {
        $stringToWrite = "";

        foreach ( $entries as $entry )
        {
            $date = date("D M j G:i:s Y", $entry[PEEP_Log::TIME_STAMP]);
            $stringToWrite .= "[$date] [{$entry[PEEP_Log::TYPE]}] [{$entry[PEEP_Log::KEY]}] {$entry[PEEP_Log::MESSAGE]}" . PHP_EOL;
        }

        $changePerm = !file_exists($this->path);
        file_put_contents($this->path, $stringToWrite, FILE_APPEND);

        if ( $changePerm )
        {
            chmod($this->path, 0666);
        }
    }
}
