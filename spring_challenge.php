<?php
// Last compile time: 03/06/23 18:33 










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
    public function aStart(Cell $start, Cell $destination) : ?Road {
        $open = new SplQueue();
        $close = [];

        $start->g_cost = 0;
        $start->h_cost = $this->heuristique($start,$destination);
        $start->f_cost = $start->g_cost + $start->h_cost;

        $open->enqueue($start);

        while(!$open->isEmpty()) {

            // cellule avec le plus de value dans la pile
            $curentCell = $this->findMostCostCellule($open);

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

                if ($costTentative >= $children->g_cost) {

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
                }
            }

        }
        return null;
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

    /**
     * @var int $countAntCells
     */
    public $countAntCells;

    /**
     * @var int $countCrisCells
     */
    public $countCrisCells;

    public function __construct()
    {
        $this->actions = [];
        $this->countAntCells = 0;
        $this->countCrisCells = 0;
    }

    /**
     * @param Action $action
     * @return $this
     */
    public function add(Action $action) : self {
        /** @var Line $action */
        if ($action->type == 1) {
            $this->countAntCells += 1;
        }

        if ($action->type == 2) {
            $this->countCrisCells +=1;
        }

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









class Helper
{
    public function evaluateRoads(Graph $graph, ListAction $listAction,Cell $myBase, array &$listCell) : array
    {

        $road = $graph->aStart($myBase, $listCell[5]);

        echo error_log(var_export($road->destination->index,true));
        die();
    }


    public function evaluateRoad(Graph $graph, ListAction $listAction, Cell $myBase, array &$listCell, array &$roads) : void {
        $roadToEvaluate = [];

        $resources = 0;
        /**
         * @var  int $key index cell
         * @var Line  $line
         */
        foreach ($listAction->actions as $key => $line) {

            $this->getRoads($graph, $myBase, $listCell[$line->destination], $roadToEvaluate, $roads);
        }
    }

    /**
     * @param Graph $graph
     * @param Cell $myBase
     * @param Cell $destination
     * @param array $roadToEvaluate
     * @param array $roads
     * @param $roadSelected
     * @return array
     */
    public function getRoads(Graph $graph, Cell $myBase,Cell $destination, array $roadToEvaluate, array &$roads): array
    {
        /** @var Road|null $roadSelected */
        $roadSelected = null;

        if (!empty($roadFromMyBase = $graph->aStart($myBase, $destination))) {
            $roadToEvaluate[] = $roadFromMyBase;

            /** @var Road $road */
            foreach ($roads as $road) {
                $roadToEvaluate[] = $graph->aStart($road->destination, $destination);
            }

            /** @var Road $road */
            foreach ($roadToEvaluate as $road) {

                if ($roadSelected === null) {
                    $roadSelected = $roads;
                }

                if ($road->resources > $roadSelected->resources) {
                    $roadSelected = $road;
                }
            }
            $roads[] = $roadSelected;
        }
        return $roads;
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

// trie des actions des plus proche au plus loin
$graph->listAction->sortByDistance();

/**
 * graph prim
 */

$graphPrime = new GraphPrim($listCells);
$graphPrime->prim($myBase);
$roads = [];
/** @var Line $action */
foreach ($graph->listAction->actions as $action) {
    $roads[] = $graphPrime->buildRoad($myBase->index,$action->destination);
}

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

        /**
         * @todo checker si il y a des actions vers des cellules oeufs sinon passer en MODE_RESOURCES
         * @todo créer un service qui supprime les actions vers les cellules oeufs  à partir d'un certains nombre de fourmis
         * @todo mettre en place un recherche du chemin le plus court a partir d'un première ressource vers les autres ressources
         * @todo Mettre en place les calculs pour les puissances sur les beacon en fonction des distances, du nombre de départ de fourmis
         * @todo si les cellules oeufs sont proche mettre un poids ford, si dans les trois plus proche resources il n'y a que des oeufs
         * @todo placer le poids des beacon à 1 lors des actions LINE -> puis utiliser les actions BEACON pour setter le bon poids
         */

        $mode = ($antTotal >= (3 * $startAnt ))? 'MODE_RESOURCES' : 'INIT';
        $graph->listAction->update($cell,$mode);

    }
    $loop += 1;

    $mode = ($antTotal >= (3 * $startAnt ))? 'MODE_RESOURCES' : 'INIT';
    $mode = ($antTotal >= (4 * $startAnt ))? 'MODE_FULL_CRISTAUX' : $mode;

    if ($loop > 1) {
        $graphPrime = new GraphPrim($listCells);
        $graphPrime->setModeResource($mode);
        $graphPrime->prim($myBase);
        $roads = [];
        /** @var Line $action */
        foreach ($graph->listAction->actions as $action) {
            $roads[] = $graphPrime->buildRoad($myBase->index,$action->destination);
        }

    }
    // Write an action using echo(). DON'T FORGET THE TRAILING \n
    // To debug: error_log(var_export($var, true)); (equivalent to var_dump)
    // WAIT | LINE <sourceIdx> <targetIdx> <strength> | BEACON <cellIdx> <strength> | MESSAGE <text>
    
    //echo($graph->listAction->chemins());
   // echo($graph->listAction->outPut($limit) ?? 'MESSAGE EMPTY');

    $output = "";
    foreach ($roads as $road) {
        foreach ($road as $key => $index) {
            $weight = $graphPrime->evaluateWeightByCell($index);
            $output .= "BEACON $index $weight;";
        }
    }
    $output .= "MESSAGE loop N° $loop; MESSAGE mode $mode\n";

    echo($output);
}

/**
 * @param string|array|int|object $data
 */
function displayLog($data) : void {
    echo error_log(var_export($data,true));
}
