<?php

namespace App\Service;

use App\Graph\Graph;
use App\Model\Cell;
use App\Model\Line;
use App\Model\ListAction;
use App\Model\Road;

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