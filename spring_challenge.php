<?php
// Last compile time: 29/05/23 23:51 








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
        $queue = new \SplQueue();

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
     * @var Cell[] $chilrens
     */
    public $chilrens;

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
     * @return Cell[]
     */
    public function getChildren() : array {
        return $this->chilrens;
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






/**
 * Auto-generated code below aims at helping you parse
 * the standard input according to the problem statement.
 **/
/** @var Cell[] $listCells */
$listCells = [];


 /**
  * @var null|Cell $myBase
  */
 $myBase = null;

 /**
 * @var int $limit nombre d'action au départ
 */
 $limit = 2;

 $antTotal = 0;
 $startAnt = 0;

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

// trie des actions des plus proche au plus court
$graph->listAction->sortByDistance();

$loop = 0;
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

        if ($loop == 0 && $myBase->index == $i) {
            $startAnt = $myAnts;
            $antTotal = $startAnt;
        } elseif ($myBase->index == $i && $loop !== 0) {
            $antTotal += $myAnts;
        }

        if ($cell->isEmpty) {
            $graph->listAction->remove($cell->index);
        }

        $mode = ($antTotal >= (3 * $startAnt ))? 'MODE_RESOURCES' : 'INIT';
        $limit = ($antTotal >= (2 * $startAnt ))? 3: $limit;
        $limit = ($antTotal >= (3 * $startAnt ))? 4: $limit;
        $graph->listAction->update($cell,$mode);

    }
    $loop += 1;
    // Write an action using echo(). DON'T FORGET THE TRAILING \n
    // To debug: error_log(var_export($var, true)); (equivalent to var_dump)
    // WAIT | LINE <sourceIdx> <targetIdx> <strength> | BEACON <cellIdx> <strength> | MESSAGE <text>
    
    //echo($graph->listAction->chemins());
    echo($graph->listAction->outPut($limit));

}

/**
 * @param string|array|int|object $data
 */
function displayLog($data) : void {
    echo error_log(var_export($data,true));
}
