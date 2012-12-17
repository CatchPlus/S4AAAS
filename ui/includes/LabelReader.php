<?php
/**
 * Created by De Ontwikkelfabriek.
 * User: Postie
 * Date: 8/29/11
 * Time: 3:53 PM
 * Copyright 2011 De Ontwikkelfabriek
 */

/**
 * New label types have to implement this interface.
 */
interface LabelReader {

    /**
     * @abstract
     * @param $lineID Array Containing the complete navis id and the word
     * @param $shear float The shear of the word
     * @return Label has to return a Label class instance
     */
    public function getLabel($lineID, $shear);

}
