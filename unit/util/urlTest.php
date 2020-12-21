<?php

class UrlTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test uri xss preventing
     */
    public function testUriXssPreventing()
    {
        $uriItems = array(
            array(
                'xss' => '/repo/createaccount',
                'cleaned' => '/repo/createaccount' 
            ),
            array(
                'xss' => '/repo/createaccount/"><script>alert(\'s\')</script>',
                'cleaned' => '/repo/createaccount/%22%3E%3Cscript%3Ealert%28%27s%27%29%3C/script%3E' 
            ),
            array(
                'xss' => '/repo/createaccount?test=aa&h=/"><script>alert(10)</script>',
                'cleaned' => '/repo/groups/invitation?test=aa&h=%2F%22%3E%3Cscript%3Ealert%2810%29%3C%2Fscript%3E' 
            ),
            array(
                'xss' => '/repo/createaccount?g/"><script>alert(10)</script>',
                'cleaned' => '/repo/createaccount?g%2F%22%3E%3Cscript%3Ealert%2810%29%3C%2Fscript%3E=' 
            )
        );

        foreach ($uriItems as $uri) 
        {
            $this->assertEquals($uri['cleaned'], UTIL_Url::secureUri($uri['xss']));
        }
    }
}