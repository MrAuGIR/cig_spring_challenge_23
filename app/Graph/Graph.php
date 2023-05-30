<?php

namespace App\Graph;

use App\Model\Cell;
use App\Model\Line;
use App\Model\ListAction;
use App\Model\Road;
use SplQueue;

class Graph
{
    /**
     * @var Cell[] $cells tableau de cellule
     */
    public $cells;

    /**
     * @var Cell $cell
     */
    public $racine;

    /**
     * @var ListAction $listAction
     */
    public $listAction;

    /**
     * @param Cell $racine
     */
    public function __construct(Cell $racine)
    {
        $this->racine = $racine;
        $this->listAction = new ListAction();
    }

    /**
     * @return Cell
     */
    public function getRacine() : Cell {
        return $this->racine;
    }

    /**
     * @return ListAction
     */
    public function getListAction() : ListAction {
        return $this->listAction;
    }

    /**
     * @return Cell[]
     */
    public function getStackCells() : array {
        return $this->cells;
    }

    /**
     * @param array $cells
     * @return $this
     */
    public function setStackCells(array $cells) : self {
        $this->cells = $cells;
        return $this;
    }

    /**
     * @param Cell $curentCell
     * @return void
     */
    public function parcoursEnLargeur(Cell $curentCell) : void
    {
        $queue = new SplQueue();

        $queue->enqueue($curentCell);

        $curentCell->color = 'GREY';

        while (!$queue->isEmpty()) {
            /** @var Cell $cell */
            $cell = $queue->dequeue();

            if ($cell->resources > 0) {
                $weight = ($cell->type == 1) ? 4 : 1;

                $action = new Line();
                $action->origine = $curentCell->index;
                $action->destination = $cell->index;
                $action->weight = $weight;
                $action->type = $cell->type;
                $action->generateDistance($cell);

                $this->listAction->add($action);
            }

            foreach ($cell->getIndexNeighbors() as $indexNeighbor) {

                if ($indexNeighbor <= 0 ) {
                    continue;
                }

                if (empty($neight = $this->cells[$indexNeighbor] ?? [])) {
                    continue;
                }

                if ($neight->color === 'WHITE') {
                    //set parent sur les voisins
                    $neight->setParent($cell);
                    // add voisin sur le parent
                    $cell->addChildren($neight);

                    $queue->enqueue($neight);

                    $neight->color = 'GREY';
                }
            }
        }
    }

    /**
     * @param Cell $start
     * @param Cell $destination
     * @return void
     */
    public function aStart(Cell $start, Cell $destination) : Road {
        $open = new SplQueue();
        $close = [];

        $start->g_cost = 0;
        $start->h_cost = $this->heuristique($start,$destination);
        $start->f_cost = $start->g_cost + $start->h_cost;

        $open->enqueue($start);

        while(!$open->isEmpty()) {

            // cellule avec le plus de value dans la pile
            $curentCell = $this->findMostCostCellule($open);

//            echo error_log(var_export("destination index ".$destination->index,true));
//            echo error_log(var_export("current index ".$curentCell->index,true));
//            echo error_log(var_export(' ',true));

            if ($curentCell->index === $destination->index) {
                $road =  new Road();
                $road->build($start,$destination);
                return $road;
            }

            $curentCell->colorAs = "BLACK";
            $open->offsetUnset($open->key());

            foreach ($curentCell->getChildren() as $children) {
                if ($children->colorAs == "BLACK") {
                    continue;
                }

                $costTentative = $this->calculCost($curentCell, $children);
//                echo error_log(var_export("cost tentative  ".$costTentative,true));
//                echo error_log(var_export("children cost ".$children->g_cost,true));

                if ($costTentative >= $children->g_cost) {
//                    echo error_log(var_export("parentIndex ".$curentCell->index,true));
//                    echo error_log(var_export("children index ".$children->index,true));
//                    echo error_log(var_export(' ',true));

                    $children->g_cost = $costTentative;
                    $children->h_cost = $this->heuristique($children,$destination);
                   // echo error_log(var_export('cost_h : '.$children->h_cost,true));
                    $children->f_cost = $children->g_cost + $children->h_cost;
                    $children->setParentAs($curentCell);
                   // echo error_log(var_export("",true));
                    if ($children->colorAs === "WHITE") {
                        $children->colorAs = "GRAY";
                        $open->enqueue($children);
                    }
                } else {
//                    echo error_log(var_export("costTentative <= children->g_cost",true));
//                    echo error_log(var_export(" ",true));
                }
            }

        }
    }

    /**
     * @param SplQueue $open
     * @return Cell
     */
    public function findMostCostCellule(SplQueue $open) : Cell {
        $cellMAx = null;
        $max = null;

        /** @var Cell $cell */
        foreach ($open as $cell) {

            if ($cellMAx == null) {
                $cellMAx = $cell;
            }

            if ($cell->resources > $max) {
                $cellMAx = $cell;
                $max = $cell->resources;
            }
        }
        return $cellMAx;
    }

    /**
     * @param Cell $start
     * @param Cell $end
     * @return float
     */
    private function heuristique(Cell $start, Cell $end) : float{
        // Parcours en profondeur pour calculer le nombre de ressources
        $visited = array();
        return $this->parcoursEnProfondeur($start, $end, $visited);
    }

    /**
     * @param Cell $start
     * @param Cell $end
     * @return float
     */
    private function calculCost(Cell $start, Cell $end) : float {

        return (($start->resources + $end->resources) == 0) ? 1 : $start->resources + $end->resources ;
    }

    private function parcoursEnProfondeur(Cell $start, Cell $end, array &$visited) : float {
        if ($start->index === $end->index) {
            return $end->resources;
        }

        $visited[$start->index] = true;
        $count = $start->resources;

        foreach ($start->getChildren() as $child) {
            if (!isset($visited[$child->index])) {
                $count += $this->parcoursEnProfondeur($child,$end,$visited);
            }
        }
        return $count;
    }
}