<?php
// Last compile time: 29/05/23 20:29 








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
    public function parcoursEnLargeur(Cell $curentCell)
    {
        $queue = new \SplQueue();

        $queue->enqueue($curentCell);

        $curentCell->color = 'GREY';

        while (!$queue->isEmpty()) {
            /** @var Cell $cell */
            $cell = $queue->dequeue();

            if ($cell->resources > 0) {
                $weight = ($cell->type == 1) ? 1 : 2;

                $action = new Line();
                $action->origine = $curentCell->index;
                $action->destination = $cell->index;
                $action->weight = $weight;
                $action->distance = 1;

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
                    $queue->enqueue($neight);

                    $neight->color = 'GREY';
                }
            }
        }
    }
}



interface Action
{
    public function toString() : string;
}



class Cell 
{
    /**
     * @var int
     */
    public  $index;
    public  $type; // 0 aucune resource, 1 , 2 contient des cristaux
    public  $resources;
    public  $myAnts;
    public  $oppAnts;
    public  $color = 'WHITE'; // Blanc non visité, gris visité une seule fois, noir visité
    public  $prevResource;
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

    /**
     * @var bool $isEmpty si la ressource est épuisé
     */
    public $isEmpty = false;

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

    /**
     * @param int $value
     * @return $this
     */
    public function updateResource(int $value) : self {
        $this->prevResource = $this->resources;
        $this->resources = $value;

        if ($value <= 0 && $this->prevResource > 0) {
            $this->isEmpty = true;
        }

        return $this;
    }

    /**
     * @return int[]
     */
    public function getIndexNeighbors() : array {
        return $this->neightboors;
    }
}



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
     * @return string
     */
    public function toString(): string
    {
        return self::CODE." $this->origine $this->destination $this->weight;";
    }
}



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








/**
 * Auto-generated code below aims at helping you parse
 * the standard input according to the problem statement.
 **/
/** @var Cell[] $listCells */
$listCells = [];

$actions = "";

 /**
  * @var null|Cell $myBase
  */
 $myBase = null;

$listDestination = [];

$listActions = new ListAction();

// $numberOfCells: amount of hexagonal cells in this map
fscanf(STDIN, "%d", $numberOfCells);
for ($i = 0; $i < $numberOfCells; $i++)
{
    // $type: 0 for empty, 1 for eggs, 2 for crystal
    // $initialResources: the initial amount of eggs/crystals on this cell
    // $neigh0: the index of the neighbouring cell for each direction
    fscanf(STDIN, "%d %d %d %d %d %d %d %d", $type, $initialResources, $neigh0, $neigh1, $neigh2, $neigh3, $neigh4, $neigh5);
    $listCells[] = new Cell($i,$type,$initialResources,0,[$neigh0,$neigh1,$neigh2,$neigh3,$neigh4,$neigh5]);
    
}
fscanf(STDIN, "%d", $numberOfBases);
$inputs = explode(" ", fgets(STDIN));
for ($i = 0; $i < $numberOfBases; $i++)
{
    $myBaseIndex = intval($inputs[$i]);
    $myBase = $listCells[$myBaseIndex];
    $myBase->isFriendBase = true;
}
$inputs = explode(" ", fgets(STDIN));
for ($i = 0; $i < $numberOfBases; $i++)
{
    $oppBaseIndex = intval($inputs[$i]);
    $listCells[$oppBaseIndex]->isEnnemyBase = true;
}
/**
 * Nouveau graph
 */
$graph = new Graph($myBase);
$graph->setStackCells($listCells);
$graph->parcoursEnLargeur($myBase);

//parcoure($myBase,$listActions,$listCells,$myBase->index);
//$listActions->sortByDistance();

// game loop
while (TRUE)
{
    for ($i = 0; $i < $numberOfCells; $i++)
    {
        // $resources: the current amount of eggs/crystals on this cell
        // $myAnts: the amount of your ants on this cell
        // $oppAnts: the amount of opponent ants on this cell
        fscanf(STDIN, "%d %d %d", $resources, $myAnts, $oppAnts);
        $cell = $listCells[$i];
        $cell->myAnts = $myAnts;
        $cell->updateResource($resources);
        $cell->oppAnts = $oppAnts;

        if ($cell->isEmpty) {
            $graph->listAction->remove($cell->index);
        }

    }
    // Write an action using echo(). DON'T FORGET THE TRAILING \n
    // To debug: error_log(var_export($var, true)); (equivalent to var_dump)
    // WAIT | LINE <sourceIdx> <targetIdx> <strength> | BEACON <cellIdx> <strength> | MESSAGE <text>
    
   
    echo($graph->listAction->outPut());
}

/**
 * @param Cell $cell
 * @param ListAction $listAction
 * @param array $listCells
 * @param $originIndex
 * @param int $distance
 * @return void
 */
function parcoure(Cell $cell, ListAction $listAction, array $listCells,$originIndex, $distance = 0) {
    $distance += 1;
    foreach ($cell->neightboors as $key => $neighIndex) {
        if ($neighIndex <= 0 ) {
            continue;
        }

        $neigh = $listCells[$neighIndex];

        if ($neigh->color !== "WHITE") {
            continue;
        }

        if ($neigh->resources > 0) {
            $weight = ($neigh->type == 1) ? 1 : 2;

            $action = new Line();
            $action->origine = $originIndex;
            $action->destination = $neigh->index;
            $action->weight = $weight;
            $action->distance = 1;

            $listAction->add($action);

           // $actions .= "LINE $originIndex $neigh->index $weight;";
        }

        $neigh->color = "GRIS";

        if (!empty($neigh->neightboors)) {
            parcoure($neigh,$listAction,$listCells,$originIndex,$distance);
        }
    }

}

/**
 * @param string|array|int|object $data
 */
function displayLog($data) : void {
    echo error_log(var_export($data,true));
}
