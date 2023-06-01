<?php

namespace App;

use App\Graph\Graph;
use App\Graph\GraphPrim;
use App\Model\Cell;
use App\Model\Line;
use App\Service\Helper;

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
$pred = $graphPrime->prim($myBase);
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
