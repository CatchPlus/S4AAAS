<?php
/**
 * User: Postie
 * Date: 17-9-12
 * Time: 7:22
 * Requests an image using an authenticated REST call to monk
 * Header is set as image/jpeg, before
 */
class ImageLoader
{
    protected $lineId;

    function __construct($lineId)
    {
        $this->lineId = $lineId;
    }

    function getImage()
    {
        if(isset($_SESSION[Config::PAGE_STORE]))
        {
            $page = $_SESSION[Config::PAGE_STORE];
            $pest = new Pest(Config::REST_SERVER);
            $imageResponse = null;

//            $url = '/rest/image/' . urlencode($page->institution) . '/' . urlencode($page->collection) . '/' . urlencode($page->book) . '/' . urlencode($page->pageNumber) . '/' . $this->lineId;

//            $urls = array_filter(explode('/', $page->getImageForLine($this->lineId)));
            $url = $page->getImageForLine($this->lineId);
            if($url)
            {
                /* url may contains spaces and other non-url characters so let's urlencode them */
                $urls = explode('/', $url);
//                $url = '/' . implode('/', array_filter(array_map('urlencode', $urls)));
                $url = '/' . implode('/', $urls);

                $imageResponse = $pest->post($url,
                    '<reqtranspage>
                        <authtoken>' . $_SESSION['token']  .  '</authtoken>
                    </reqtranspage>');

                header("Content-type: image/jpeg");
                print_r($imageResponse);

            }
            else
            {
                $im  = imagecreatetruecolor(150, 30);
                $bgc = imagecolorallocate($im, 255, 255, 255);
                $tc  = imagecolorallocate($im, 0, 0, 0);

                imagefilledrectangle($im, 0, 0, 150, 30, $bgc);

                /* Output an error message */
                imagestring($im, 4, 5, 5, 'Image not found', $tc);
                header("Content-type: image/jpeg");
                imagejpeg($im);
            }
        }
    }

}
