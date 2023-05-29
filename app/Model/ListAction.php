<?php

namespace App\Model;

class ListAction
{
    /**
     * @var Action[] $actions
     */
    public $actions;

    public function __construct()
    {
        $this->actions = [];
    }

    /**
     * @param Action $action
     * @return $this
     */
    public function add(Action $action) : self {
        $this->actions[$action->destination] = $action;
        return $this;
    }

    /**
     * @param int $index
     * @return self
     */
    public function remove(int $index) : self {

        if (isset($this->actions[$index])) {
            unset($this->actions[$index]);
        }
        return $this;
    }

    /**
     * @param Cell $cell
     * @param string $mode
     * @return $this
     */
    public function update(Cell $cell, string $mode = "INIT") : self {
        if (isset($this->actions[$cell->index])) {

            if (empty($cell->originalResources)) {
                return $this;
            }

            if ($mode === "INIT") {
                if ($this->actions[$cell->index]->type == 1) {
                    $this->actions[$cell->index]->weight = 4;
                } elseif($this->actions[$cell->index]->type == 2) {
                    $this->actions[$cell->index]->weight = 1;
                }
            } else {
                if ($this->actions[$cell->index]->type == 1) {
                    $this->actions[$cell->index]->weight = 1;
                } elseif($this->actions[$cell->index]->type == 2) {
                    $this->actions[$cell->index]->weight = 4;
                }
            }

            if ($cell->getStatuResource() <= 25.0) {
                $this->actions[$cell->index]->weight = 0;
            } elseif ($cell->getStatuResource() <= 35.0) {
                $this->actions[$cell->index]->weight = 1;
            } elseif ($cell->getStatuResource() <= 50) {
                $this->actions[$cell->index]->weight = 2;
            }
        }
        return $this;
    }

    /**
     * @param int $limit
     * @return string
     */
    public function outPut(int $limit = 3) : string {

        $return = '';
        foreach ($this->actions as $key => $action) {
            if ($limit <= 0) {
                continue;
            }
            $return .= $action->toString();
            $limit--;
        }
        return $return."\n";
    }

    /**
     * @return $this
     */
    public function sortByDistance() : self{
        usort($this->actions, function ($a,$b){
            return $a->distance > $b->distance;
        });

        $actionSorted = [];
        foreach ($this->actions as $key => $action) {
            $actionSorted[$action->destination] = $action;
        }
        $this->actions = $actionSorted;

        return $this;
    }

    /**
     * @return string
     */
    public function chemins() : string {
        $return = '';
        foreach ($this->actions as $key => $action) {
            $return .= $action->displayChemin();
        }
        return $return."\n";
    }
}