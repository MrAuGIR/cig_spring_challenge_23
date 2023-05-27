<?php

namespace App\Model\Cell;

class Cell 
{
    /**
     * @var int
     */
    public  $index;
    public  $type; // 0 aucune resource, 1 , 2 contient des cristaux
    public  $resources;
    public  $myAnts;
    /**
     * @var array
     */
    public $neightboors;
    /**
     * @var bool
     */
    public $isFriendBase;
    /**
     * @var bool
     */
    public $isEnnemyBase;

    public function __construct(int $index, int $type, int $resources, int $myAnts, array $neightboors, bool $isFriendBase = false, bool $isEnnemyBase = false)
    {
        $this->index = $index;
        $this->type = $type;
        $this->resources = $resources;
        $this->myAnts = $myAnts;
        $this->neightboors = $neightboors;
        $this->isFriendBase = $isFriendBase;
        $this->isEnnemyBase = $isEnnemyBase;
    }
}