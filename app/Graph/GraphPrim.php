<?php

namespace App\Graph;

use App\Model\Cell;
use App\Model\ListAction;

class GraphPrim
{
    /**
     * @var Cell[] $cells
     */
    public $cells;

    /**
     * @var array $cout
     */
    public $cout;

    /**
     * @var array $pred
     */
    public $pred;

    /**
     * @var array $inQueue
     */
    public $inQueue;

    /**
     * @var string
     */
    public $modeResource;

    /**
     * @param Cell[] $cells
     */
    public function __construct(array $cells) {
        $this->cells = $cells;
        $this->cout = [];
        $this->pred = [];
    }

    /**
     * @param Cell $start
     * @return array
     */
    public function prim(Cell $start) : array {
        $numCells = count($this->cells);
        $this->cout = [];
        $this->pred = [];

        foreach ($this->cells as $index => $cell) {
            $this->cout[$index] = - INF;
            $this->pred[$index] = null;
            $cell->color = "WHITE";
        }

        $this->cout[$start->index] = 0;
        $queue = new \SplPriorityQueue();

        // ajout des sommets a la file
        foreach ($this->cells as $index => $cell) {
            $queue->insert($index, $this->cout[$index]);
            $this->inQueue[$index] = true;
        }
        $counter = 0;
        while (!$queue->isEmpty()) {
            $counter++;
            $index = $queue->extract(); //défilé la cellule de priorité maximal

            if ($this->cells[$index]->color !== "WHITE") {
                continue;
            }

            $this->cells[$index]->color = "GREY";

            foreach ($this->cells[$index]->getIndexNeighbors() as $neighbor) {

                if ($neighbor < 0) {
                    continue;
                }

                if ($this->cells[$neighbor]->color !== 'WHITE') {
                    continue; // Ignorer le voisin déjà inclus dans le chemin
                }

                $weight = $this->evaluatePriorityCell($this->cells[$index]);
                if ($this->inQueue[$neighbor] && ($this->cout[$neighbor] <= $weight)) {
                    $this->pred[$neighbor] = $index;
                    $this->cout[$neighbor] = $weight;

                    $queue->insert($neighbor,$this->cout[$neighbor]);
                }
            }
            $this->cells[$index]->color = "BLACK";
        }
        return $this->pred;
    }

    /**
     * @param $start
     * @param $end
     * @return array
     */
    public function buildRoad($start, $end) : array {
        $path = array();
        $current = $end;

        while ($current !== $start) {
            array_unshift($path, $current);

            $current = $this->pred[$current] ;
            if ($current === null){
                break;
            }
        }

        array_unshift($path, $start);
        return $path;
    }

    /**
     * @param string $mode
     * @return $this
     */
    public function setModeResource(string $mode ="MODE_INIT") : self {
        $this->modeResource = $mode;
        return $this;
    }

    /**
     * @param int $index
     * @return int
     */
    public function evaluateWeightByCell(int $index) : int {
        return 1;
    }

    /**
     * @param int $distance
     * @return int
     */
    public function calculCoefDistance(int $distance) : int {
        // si les ressources cristaux sont plus que 6 -> priorisé celles proches
        // une fois que la quantité diminue ou que le nombre de fourmis augmente baisser la pénalité des cellules distances

        if ($this->modeResource == 'MODE_INIT' && $distance > 8) {
            return 0;
        }

        return 1;
    }

    /**
     * @param Cell $cell
     * @return int
     */
    public function evaluatePriorityCell(Cell $cell) : int {

        $priority = $cell->resources ?? 0;

        foreach ($cell->neighbors as $neighbor) {

            if ($neighbor < 0) {
                continue;
            }

            if ($this->modeResource == 'MODE_FULL_CRISTAUX' && $cell->type === 1) {
                continue;
            } else {
                $priority += isset($this->cells[$neighbor]) ? (int)floor($this->cells[$neighbor]->resources / 6) : 0;
            }

        }

        if ($this->modeResource == 'MODE_FULL_CRISTAUX' && $cell->type === 1) {
            return 0;
        }

        return ($priority * $this->calculCoefDistance($this->generateDistance($cell)));
    }

    /**
     * @param Cell $cell
     * @return void
     */
    private  function generateDistance(Cell $cell) : int {
        $chemin[] = $cell;

        if (empty($parent = $cell->getParent())) {
            return $distance = 1;
        }
        $chemin[] = $parent;

        while ($parent !== null) {
            $parent = $parent->getParent();
            if(!empty($parent)) {
                $chemin[] = $parent;
            }
        }
        return (count($chemin) -1) <= 0 ? 1 : count($chemin) -1;

    }
}