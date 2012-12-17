<?php
/**
 * Created by De Ontwikkelfabriek.
 * User: Postie
 * Date: 5/23/11
 * Time: 1:25 PM
 * Copyright 2011 De Ontwikkelfabriek
 *
 * Retrieves the data from the URI passed to the static method. The method determinses if curl is used
 * or file_get_contents, where curl is the prefered one
 */
 
abstract class SendRequest {

    // according to a little search on the internet, setting a user agent could resolve the http request failed result

    /**
     * @static Retrieves the result and returns it as a SimpleXMLObject
     * @throws Exception Failing a retrieval throws an Exception
     * @param  $uri String
     * @return SimpleXMLObject Contains the result
     */
    public static function getResult($uri)
    {
        if(function_exists('curl_setopt') && function_exists('curl_exec'))
        {
            $ch = curl_init($uri);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, Config::TIMEOUT);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
            curl_setopt($ch, CURLOPT_USERAGENT, Config::USERAGENT);                  // Have to set user agent to avoid getting a 'failed to open stream: HTTP request failed!'

            if(($result = curl_exec($ch)) === '')
            {
                throw new Exception('Having problems getting result with curl. Please contact webmaster');
            }

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
            {
                throw new Exception('Error retrieving data from Monk Server. Error code 0x42');
            }
        }


        $returnResult = SendRequest::gzdecode($result);

        if(!is_null($returnResult))
            $result = $returnResult;
        

        if(($result = simplexml_load_string(utf8_encode($result))) === false)
        {
            throw new Exception('There is a problem. Apparently Monk is not returning valid XML');
        }

        // try to decompress result


        // if failed return original result
        return $result;

    }

    private static function gzdecode($data,&$filename='',&$error='',$maxlength=null)
    {
        $len = strlen($data);
        if ($len < 18 || strcmp(substr($data,0,2),"\x1f\x8b")) {
            $error = "Not in GZIP format.";
            return null;  // Not GZIP format (See RFC 1952)
        }
        $method = ord(substr($data,2,1));  // Compression method
        $flags  = ord(substr($data,3,1));  // Flags
        if ($flags & 31 != $flags) {
            $error = "Reserved bits not allowed.";
            return null;
        }
        // NOTE: $mtime may be negative (PHP integer limitations)
        $mtime = unpack("V", substr($data,4,4));
        $mtime = $mtime[1];
        $xfl   = substr($data,8,1);
        $os    = substr($data,8,1);
        $headerlen = 10;
        $extralen  = 0;
        $extra     = "";
        if ($flags & 4) {
            // 2-byte length prefixed EXTRA data in header
            if ($len - $headerlen - 2 < 8) {
                return false;  // invalid
            }
            $extralen = unpack("v",substr($data,8,2));
            $extralen = $extralen[1];
            if ($len - $headerlen - 2 - $extralen < 8) {
                return false;  // invalid
            }
            $extra = substr($data,10,$extralen);
            $headerlen += 2 + $extralen;
        }
        $filenamelen = 0;
        $filename = "";
        if ($flags & 8) {
            // C-style string
            if ($len - $headerlen - 1 < 8) {
                return false; // invalid
            }
            $filenamelen = strpos(substr($data,$headerlen),chr(0));
            if ($filenamelen === false || $len - $headerlen - $filenamelen - 1 < 8) {
                return false; // invalid
            }
            $filename = substr($data,$headerlen,$filenamelen);
            $headerlen += $filenamelen + 1;
        }
        $commentlen = 0;
        $comment = "";
        if ($flags & 16) {
            // C-style string COMMENT data in header
            if ($len - $headerlen - 1 < 8) {
                return false;    // invalid
            }
            $commentlen = strpos(substr($data,$headerlen),chr(0));
            if ($commentlen === false || $len - $headerlen - $commentlen - 1 < 8) {
                return false;    // Invalid header format
            }
            $comment = substr($data,$headerlen,$commentlen);
            $headerlen += $commentlen + 1;
        }
        $headercrc = "";
        if ($flags & 2) {
            // 2-bytes (lowest order) of CRC32 on header present
            if ($len - $headerlen - 2 < 8) {
                return false;    // invalid
            }
            $calccrc = crc32(substr($data,0,$headerlen)) & 0xffff;
            $headercrc = unpack("v", substr($data,$headerlen,2));
            $headercrc = $headercrc[1];
            if ($headercrc != $calccrc) {
                $error = "Header checksum failed.";
                return false;    // Bad header CRC
            }
            $headerlen += 2;
        }
        // GZIP FOOTER
        $datacrc = unpack("V",substr($data,-8,4));
        $datacrc = sprintf('%u',$datacrc[1] & 0xFFFFFFFF);
        $isize = unpack("V",substr($data,-4));
        $isize = $isize[1];
        // decompression:
        $bodylen = $len-$headerlen-8;
        if ($bodylen < 1) {
            // IMPLEMENTATION BUG!
            return null;
        }
        $body = substr($data,$headerlen,$bodylen);
        $data = "";
        if ($bodylen > 0) {
            switch ($method) {
            case 8:
                // Currently the only supported compression method:
                $data = gzinflate($body,$maxlength);
                break;
            default:
                $error = "Unknown compression method.";
                return false;
            }
        }  // zero-byte body content is allowed
        // Verifiy CRC32
        $crc   = sprintf("%u",crc32($data));
        $crcOK = $crc == $datacrc;
        $lenOK = $isize == strlen($data);
        if (!$lenOK || !$crcOK) {
            $error = ( $lenOK ? '' : 'Length check FAILED. ') . ( $crcOK ? '' : 'Checksum FAILED.');
            return false;
        }
        return $data;
    }
}
