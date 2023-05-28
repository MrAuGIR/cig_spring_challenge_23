<?php

namespace App;

use App\Model\Cell;
use App\Model\Graph;
use App\Model\Line;
use App\Model\ListAction;

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

parcoure($myBase,$listActions,$listCells,$myBase->index);

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
            $listActions->remove($cell->index);
        }

    }
    // Write an action using echo(). DON'T FORGET THE TRAILING \n
    // To debug: error_log(var_export($var, true)); (equivalent to var_dump)
    // WAIT | LINE <sourceIdx> <targetIdx> <strength> | BEACON <cellIdx> <strength> | MESSAGE <text>
    
   
    echo($listActions->outPut());
}

/**
 * @param Cell $cell
 * @param ListAction $listAction
 * @param array $listCells
 * @param $originIndex
 * @return void
 */
function parcoure(Cell $cell, ListAction $listAction, array $listCells,$originIndex) {

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

            $listAction->add($action);

           // $actions .= "LINE $originIndex $neigh->index $weight;";
        }

        $neigh->color = "GRIS";

        if (!empty($neigh->neightboors)) {
            parcoure($neigh,$listAction,$listCells,$originIndex);
        }
    }

}

/**
 * @param string|array|int|object $data
 */
function displayLog($data) : void {
    echo error_log(var_export($data,true));
}
