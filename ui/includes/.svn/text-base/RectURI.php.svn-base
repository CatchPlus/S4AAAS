<?php
/**
 * Created by De Ontwikkelfabriek.
 * User: Postie
 * Date: 8/29/11
 * Time: 10:38 AM
 * Copyright 2011 De Ontwikkelfabriek
 */
 
class RectURI implements ShapeURI {

    public function getURI($label, $shear)
    {

        $uri = array();
        $uri['label']  = $label['txt'];
        $uri['dist']   = $label['dist'];
        $uri['lineid'] = $label['lineid'];
        $uri['roi']    = "" .
                         intval($label['x']) . ',' . intval($label['y']) .
                         '-' .
                         (intval($label['x']) + intval($label['w'])) . ',' . (intval($label['y']) + intval($label['h']));
        $uri['pts']    = PTS::RECT;
        $coordinates   = array();
        $coordinates[] = new Coordinate(intval($label['x']), intval($label['y']));
        $coordinates[] = new Coordinate(intval($label['x']) + intval($label['w']), intval($label['y']));
        $coordinates[] = new Coordinate(intval($label['x']) + intval($label['w']), intval($label['y']) + intval($label['h']));
        $coordinates[] = new Coordinate(intval($label['x']), intval($label['y']) + intval($label['h']));

        $uri['coordinates'] = $coordinates;


        return $uri;
                            
//
//
//        '&x='     . $label['x'] .
//        '&y='     . $label['y'] .
//        '&w='     . $label['w'] .
//        '&h='     . $label['h'] .
//
//
//
//        $uri = '?cmd=newwordlabel-api'               .
//           '&token='  . $user->getToken()            .
//           '&appid='  . Config::APPID                .
//           '&label='  . urlencode($label['txt'])     .
//           '&dist='   . $label['dist']               .
//           '&x='      . $label['x']                  .
//           '&y='      . $label['y']                  .
//           '&w='      . $label['w']                  .
//           '&h='      . $label['h']                  .
//           '&lineid=' . urlencode($label['lineid']);
    }
}
