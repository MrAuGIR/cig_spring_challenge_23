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
     * @return string
     */
    public function outPut() : string {

        $return = '';
        foreach ($this->actions as $key => $action) {
            $return .= $action->toString();
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
        return $this;
    }
}