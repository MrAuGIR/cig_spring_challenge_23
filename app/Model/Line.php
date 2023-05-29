<?php

namespace App\Model;

class Line implements Action
{
    const CODE = "LINE";

    /**
     * @var int $origine index cellule origine
     */
    public $origine;

    /**
     * @var int $destination index cellule destination
     */
    public $destination;

    public $weight;

    /**
     * @var int $distance distance entre la cellule de dest et le point d'origine
     */
    public $distance;

    /**
     * @var array list des cellules du chemin
     */
    public $chemin;

    /**
     * @var int $type type destination : 0 rien | 1  oeufs | 2 resources
     */
    public $type;

    public function __construct()
    {
        $this->chemin = [];
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        return self::CODE." $this->origine $this->destination $this->weight;";
    }

    /**
     * @param Cell $cell
     * @return void
     */
    public function generateDistance(Cell $cell) : void {

        $this->chemin[] = $cell;

        if (empty($parent = $cell->getParent())) {
            $this->distance = 1;
            return;
        }
        $this->chemin[] = $parent;

        while ($parent !== null) {
            $parent = $parent->getParent();
            if(!empty($parent)) {
                $this->chemin[] = $parent;
            }
        }
        $this->distance = (count($this->chemin) -1) <= 0 ? 1 : count($this->chemin) -1;
    }

    /**
     * @return string
     */
    public function displayChemin() : string {
        $return = '';

        $chemin = array_reverse($this->chemin);

        foreach ($chemin as $key => $cell) {
            $end = '';
            if (isset($chemin[$key+1])) {
                $end = '->';
            }
            $return .= $cell->index.$end;
        }

        return "MESSAGE ".$return.";";
    }
}