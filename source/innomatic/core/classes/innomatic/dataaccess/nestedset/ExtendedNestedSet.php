<?php
namespace Innomatic\Dataaccess\Nestedset;

interface ExtendedNestedSet extends NestedSetInterface
{
    // All functions with ConditionString, accept other parameters in variable numbers
    function getID($ConditionString);

    function insertChildData($FieldValueArray = array(), $ConditionString = null);

    function insertSiblingData($FieldValueArray = array(), $ConditionString = null);

    function deleteSubtreeConditional($ConditionString);

    function deleteConditional($ConditionString);

    function childrenConditional($ConditionString);

    function descendantsConditional($AbsoluteDepths = false, $ConditionString);

    function leavesConditional($ConditionString = null);

    function pathConditional($ConditionString);

    function depthConditional($ConditionString);

    function parentNodeConditional($ConditionString);

    function siblingConditional($SiblingDistance = 1, $ConditionString);
}
