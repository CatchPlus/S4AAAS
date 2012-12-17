<?php
/**
 * Created by De Ontwikkelfabriek.
 * User: Postie
 * Date: 7/7/11
 * Time: 2:56 PM
 * Copyright 2011 De Ontwikkelfabriek
 */
 
class Line {

    /* id of the line, would be like navis-blabla-line-000 */
    private $id;

    /* URI of the image */
    public $image;

    /* collection of labels that are identified with the line */
    private $labels = array();

    function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * Adds a label to the line
     * @param Label $label
     * @return void
     */
    public function add(Label $label)
    {
        $this->labels[] = $label;
    }


    /**
     * Adds multiple labels to the linme
     * @param  $labels
     * @return void
     */
    public function addLabels($labels)
    {
        foreach($labels as $label)
            if($label instanceof Label)
                $this->add($label);
    }

    /**
     * @return string Returns the id as a JSON
     */
    public function idAsJSON()
    {
        return json_encode($this->id);
    }

    /**
     * Returns the line id
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * returns the labels as JSON
     * @return string JSON
     */
    public function asJSON()
    {
        $labels = array();
        foreach($this->labels as $label)
            $labels[] = $label->getLabel();
        $sorted = usort($labels, array('Line', 'sortByX'));
        return json_encode($labels);
    }

    
    function __toString()
    {
        return $this->id;
    }

    function sortByX($a, $b)
    {
        if($a[1] == $b[1])
            return 0;
        return ($a[1] > $b[1] )? 1 : -1;
    }


}
