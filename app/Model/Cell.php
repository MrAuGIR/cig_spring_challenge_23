<?php

namespace App\Model;

class Cell 
{
    /**
     * @var int
     */
    public  $index;

    /**
     * @var int $type 0 aucune resource, 1 des oeufs, 2 contient des cristaux
     */
    public  $type;

    /**
     * @var int $originalResources // les ressources d'origine
     */
    public $originalResources;
    public  $resources;
    public  $myAnts;
    public  $oppAnts;

    /**
     * @var string $color Blanc non visité, gris visité une seule fois, noir visité
     */
    public  $color = 'WHITE';
    public $colorAs = "WHITE";
    public  $prevResource;
    /**
     * @var array
     */
    public $neighbors;
    /**
     * @var bool
     */
    public $isFriendBase;
    /**
     * @var bool
     */
    public $isEnnemyBase;

    /**
     * @var bool $isEmpty si la ressource est épuisé
     */
    public $isEmpty = false;

    /**
     * @var Cell|null $parent
     */
    public $parent;

    /**
     * @var Cell|null $parentAs parent pour l'algo A-Star
     */
    public $parentAs;

    /**
     * @var Cell[] $chilrens
     */
    public $chilrens;

    /**
     * @var float|int $g_cost
     */
    public $g_cost;

    /**
     * @var float|int $h_cost
     */
    public $h_cost;

    /**
     * @var float|int $f_cost
     */
    public $f_cost;

    /**
     * @param int $index
     * @param int $type
     * @param int $resources
     * @param int $myAnts
     * @param array $neighbors
     * @param bool $isFriendBase
     * @param bool $isEnnemyBase
     */
    public function __construct(int $index, int $type, int $resources, int $myAnts, array $neighbors, bool $isFriendBase = false, bool $isEnnemyBase = false)
    {
        $this->index = $index;
        $this->type = $type;
        $this->resources = $resources;
        $this->myAnts = $myAnts;
        $this->neighbors = $neighbors;
        $this->isFriendBase = $isFriendBase;
        $this->isEnnemyBase = $isEnnemyBase;
        $this->originalResources = $resources;
        $this->g_cost = 0;
        $this->h_cost = 0;
        $this->f_cost = 0;
        $this->childrens = [];
    }

    /**
     * @param int $value
     * @return $this
     */
    public function updateResource(int $value) : self {
        $this->prevResource = $this->resources;
        $this->resources = $value;

        if ($this->getStatuResource() <= 25.0) {
            $this->isEmpty = true;
        }

        return $this;
    }

    /**
     * @return int[]
     */
    public function getIndexNeighbors() : array {
        return $this->neighbors;
    }

    /**
     * @return Cell|null
     */
    public function getParent() : ?Cell {
        return $this->parent;
    }

    /**
     * @param Cell $cell
     * @return $this
     */
    public function setParent(Cell $cell) : self {
        $this->parent = $cell;
        return $this;
    }

    /**
     * @return Cell|null
     */
    public function getParentAs() : ?Cell {
        return $this->parentAs;
    }

    /**
     * @param Cell $parent
     * @return $this
     */
    public function setParentAs(Cell $parent) : self {
        $this->parentAs = $parent;
        return $this;
    }

    /**
     * @return Cell[]
     */
    public function getChildren() : array {
        return $this->chilrens ?? [];
    }

    /**
     * @param Cell $children
     * @return void
     */
    public function addChildren(Cell $children) : void {
        $this->chilrens[] = $children;
    }

    /**
     * @return float entre 0 et 100 %
     */
    public function getStatuResource() : float {
        if ($this->originalResources <= 0) {
            return 0.0;
        }
        return floor(($this->resources * 100) / $this->originalResources);
    }
}