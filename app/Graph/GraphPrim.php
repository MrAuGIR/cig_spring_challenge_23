<?php

namespace App\Graph;

use App\Model\Cell;

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
            $index = $queue->extract(); //defilé la cellule de priorité maximal

            foreach ($this->cells[$index]->getIndexNeighbors() as $neighbor) {

                if ($neighbor < 0) {
                    continue;
                }

                if (in_array($neighbor,$this->pred)) {
                    continue;
                }

                $weight = $this->cells[$index]->resources;
                if ($this->inQueue[$neighbor] && ($this->cout[$neighbor] <= $weight)) {
                    $this->pred[$neighbor] = $index;
                    $this->cout[$neighbor] = $weight;

                    $queue->insert($neighbor,$this->cout[$neighbor]);
                }
            }
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
}