<?php

namespace App\Model;

class Road
{
    /**
     * @var Cell $origine cellule origine
     */
    public $origine;

    /**
     * @var Cell $destination cellule destination
     */
    public $destination;

    /**
     * @var Cell[] $list
     */
    protected $list;

    /**
     * @var int $distance
     */
    public $distance;

    /**
     * @var int resources
     */
    public $resources;

    /**
     * @param Cell $start
     * @param Cell $end
     * @return self
     */
    public function build(Cell $start, Cell $end) : self {
        $this->list = [];
        $this->origine = $start;
        $this->destination = $end;
        $this->resources = 0;

        $cellActuel = $end;
        $this->resources += $end->resources;

        while ($cellActuel !== $start) {
            $this->list[] = $cellActuel;
            $cellActuel = $cellActuel->getParentAs();
            $this->resources += $cellActuel->resources;
        }
        $this->list[] = $start;

        $this->list = array_reverse($this->list);
        $this->distance = (count($this->list) -1) <= 0 ? 1 : count($this->list) -1;
        return $this;
    }

    /**
     * @return Cell[]
     */
    public function getList() : array {
        return $this->list;
    }

    public function outputAction() : string {
        $return = '';
        foreach ($this->list as $cell) {
            $return .="BEACON $cell->index 1;";
        }
        return $return;
    }
}