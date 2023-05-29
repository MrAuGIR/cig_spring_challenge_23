<?php

namespace App;

use App\Graph\Graph;
use App\Model\Cell;

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
 * @param string|array|int|object $data
 */
function displayLog($data) : void {
    echo error_log(var_export($data,true));
}
