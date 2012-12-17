<?php
/**
 * Created by De Ontwikkelfabriek.
 * User: Postie
 * Date: 7/7/11
 * Time: 3:06 PM
 * Copyright 2011 De Ontwikkelfabriek
 */
 
abstract class RemoteCall {
    // according to a little search on the internet, setting a user agent could resolve the http request failed result
    /**
     * @static Retrieves the result and returns it as a SimpleXMLObject
     * @throws Exception Failing a retrieval throws an Exception
     * @param  $uri String
     * @return SimpleXMLObject Contains the result
     */
    public static function call($uri)
    {
        /* supervise a training set */
        /* if pageid exists as file, it's checking a local file */
        if(function_exists('curl_setopt') && function_exists('curl_exec') && function_exists(('curl_init')))
        {
            $ch = curl_init($uri);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, Config::TIMEOUT);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERAGENT, Config::USERAGENT);                  // Have to set user agent to avoid getting a 'failed to open stream: HTTP request failed!'

            if(!($result = curl_exec($ch)))
                throw new Exception('Having problems getting result with curl. Monk could be down. Please contact webmaster');

        }
        else
        {
            // Create the stream context
            $context = stream_context_create(array(
                'http' => array(
                    'header'=>'Connection: close',
                    'timeout' => Config::TIMEOUT      // Timeout in seconds
                )
            ));
            // try to set user agent, attempting to avoid failed http requests
     
            ini_set('user_agent', Config::USERAGENT);                                // Have to set user agent to avoid getting a 'failed to open stream: HTTP request failed!'

            if(($result = file_get_contents($uri, 0, $context)) === false)
                throw new Exception('Error retrieving data from Monk Server. Error code 0x42');
        }
        return $result;
    }
}

