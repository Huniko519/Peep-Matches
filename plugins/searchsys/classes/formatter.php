<?php

class SEARCHSYS_CLASS_Formatter
{
    private $delimiter = " &hellip; ";
    
    private $maxlength = 300;
    
    private $highlightClass = "peep_highbox";
    
    private $text;
    
    private $words = array();
    
    private $wordsCount;
    
    
    public function __construct() { }
    
    public function setDelimiter( $delim )
    {
        $this->delimiter = $delim;
    }
    
    public function setHighlightClass( $class )
    {
        $this->highlightClass = $class;
    }
    
    public function setMaxlength( $length )
    {
        $this->maxlength = $length;
    }
    
    public function formatResult( $text, array $words )
    {
        $this->text = $text;
        $this->words = $words;
        
        $excerption = $this->getExcerption($this->text, $this->words);
        
        return $this->highlightText($excerption);
    }
    
    private function getExcerption( $text, array $words )
    {
        if ( mb_strlen($text) < $this->maxlength * 1.15 ) // too short
        {
            return $text;
        } 
        else 
        {
            switch ( $this->wordsCount = $this->getWordsCount() ) 
            {
                case 0:
                    return $this->getStartExcerption();

                case 1:
                    return $this->getSingleWordExcerption();
                
                case 2:
                    return $this->getCoupleWordsExcerption();
                
                default:
                    return $this->getMultipleWordsExcerption();
            }
        }

        return $results;
    }
    
    private function getWordsCount()
    { 
        foreach ( $this->words as $id => $word ) 
        {
            if ( !preg_match("/(?<![\pL\pN_])".preg_quote($word, '/')."(?![\pL\pN_])/iu", $this->text) ) 
            {
                unset($this->words[$id]);
            }
        }

        return count($this->words);
    }
    
    private function getStartExcerption()
    {
        preg_match("/^(?:.{0,$this->maxlength}[\.;:,]|.{0,$this->maxlength}(?![\pL\pN_]))/smiu", $this->text, $matches);
        
        return $matches[0] . $this->delimiter;
    }
    
    private function getSingleWordExcerption()
    {
        $word = reset($this->words);
        $spacelength = round($this->maxlength / 2);

        preg_match("/(\W|^).{0,$spacelength}(?<![\pL\pN_])".preg_quote($word, '/')."(?![\pL\pN_]).{0,$spacelength}(\W|$)/smiu", $this->text, $matches);

        $noLeftDelim = preg_match('/(?:^|' . "\r|\n" . ')' . preg_quote($matches[0], '/') . '/u', $this->text) ? true : false;
        $noRightDelim = preg_match('/' . preg_quote($matches[2], '/') . '(?:$|' . "\r|\n" . ')/u', $this->text) ? true : false;
        
        return ($noLeftDelim ? "" : $this->delimiter) . $matches[0] . ($noRightDelim ? "" : $this->delimiter);
    }
    
    private function getCoupleWordsExcerption()
    {
        $spacelength = round($this->maxlength / 2.15);
        $word1 = preg_quote(reset($this->words), '/');
        $word2 = preg_quote(next($this->words), '/');

        if ( preg_match("/(\W|^)(?:\w+\W+){3,7}(?:$word1(?![\pL\pN_]).{0,$spacelength}(?<![\pL\pN_])$word2|$word2(?![\pL\pN_]).{0,$spacelength}(?<![\pL\pN_])$word1)(?![\pL\pN_]).{0,$spacelength}(\W|$)/smiu", $this->text, $matches) ) 
        {
            return ($matches[1] != "" ? $this->delimiter : "") . $matches[0] . ($matches[2] != "" ? $this->delimiter : "");
        }
        else 
        {
            $spacelength = round($spacelength / 2.5);
            preg_match("/(\W|^).{0,$spacelength}(?<![\pL\pN_])$word1(?![\pL\pN_]).{0,$spacelength}(?=\W)/smiu", $this->text, $matches);

            $matchedtext = ($matches[1] != "" ? $this->delimiter : "") . $matches[0] . $this->delimiter;
            preg_match("/(\W).{0,$spacelength}(?<![\pL\pN_])$word2(?![\pL\pN_]).{0,$spacelength}(\W|$)/smiu", $this->text, $matches);
            
            return $matchedtext . $matches[0] . ($matches[2] != "" ? $this->delimiter : "");
        }
    }
    
    private function getMultipleWordsExcerption()
    {
        $spacelength = round($this->maxlength / ($this->wordsCount + 0.15 - ($this->wordsCount * .15)));

        if ( preg_match_all("/(\s|^)(?:\w+\s+){3,7}(?:(?<=(?<![\pL\pN_]))(?:" . preg_quote(implode("|", $this->words), '/') . ")(?=(?![\pL\pN_])).{0,$spacelength}){{$this->wordsCount}}(\s|$)/smiu", $this->text, $matches) )
        {
            $maxwords = 0;
            foreach ( $matches[0] as $key => $match ) 
            {
                $foundwords = 0;
                preg_match_all("/(?:(?<![\pL\pN_]))(?:" . preg_quote(implode("|", $this->words), '/') . ")(?:(?![\pL\pN_]))/iu", mb_strtolower($match), $words);
                $wordcount = count(array_unique($words[0])) + count($words[0]) / ($this->wordsCount * 1.6);
                if ( $wordcount > $maxwords ) 
                {
                    $maxwords = $wordcount;
                    $maxkey = $key;
                }
                if ( $wordcount >= $this->wordsCount ) 
                {
                    return ($matches[1][$key] != "" ? $this->delimiter : "") . $match . ($matches[2][$key] != "" ? $this->delimiter : "");
                }
            }
            
            if ( $maxwords > 1 ) 
            {
                return ($matches[1][$maxkey] != "" ? $this->delimiter : "") . $matches[0][$maxkey] . ($matches[2][$maxkey] != "" ? $this->delimiter : "");
            }
        }
        
        return $this->getCoupleWordsExcerption();
    }
    
    private function highlightText( $text ) 
    {
        foreach ( $this->words as $id => $word ) 
        {
           $text = preg_replace('/(?<![\pL\pN_])([\w-]*' . preg_quote($word, '/') . '[\w-]*)(?![\pL\pN_])/iu', '<span class="'.$this->highlightClass.'">\\1</span>', $text);
        }
        
        return $text;
    }
}