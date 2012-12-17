<?php
/**
 * Created by De Ontwikkelfabriek.
 * User: Postie
 * Date: 8/29/11
 * Time: 10:37 AM
 * Copyright 2011 De Ontwikkelfabriek
 */


/**
 * Interface to create new shapes. Implement this interface to use with the transcribe interface when creating new shapes
 * Name should be <pts-name>URI
 * function has to return an array with the label options.
 * Example:
 * $uri = array();
 *  $uri['label']  = $label['txt'];
 *  $uri['dist']   = $label['dist'];
 *  $uri['lineid'] = $label['lineid'];
 *  $uri['roi']    = "" .
 *                   intval($label['x']) . ',' . intval($label['y']) .
 *                   '-' .
 *                   (intval($label['x']) + intval($label['w'])) . ',' . (intval($label['y']) + intval($label['h']));
 *  $uri['pts']    = PTS::RECT;
 */
interface ShapeURI {

    public function getURI($label, $shear);
}
