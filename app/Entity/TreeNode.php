<?php

namespace Filehosting\Entity;

class TreeNode
{
    private $object;
    private $childNodes = [];

    public function __construct(TreeNodeSearchable $object)
    {
        $this->object = $object;
    }

    public function getObject()
    {
        return $this->object;
    }

    public function addChildNode(TreeNode $node)
    {
        array_push($this->childNodes, $node);
        return $node;
    }

    public function getChildren()
    {
        return $this->childNodes;
    }

    public function countChildren()
    {
        return count($this->childNodes);
    }

    public function countDescendants()
    {
        $size = $this->countChildren();
        foreach($this->childNodes as $id => $node) {
            if($node->countChildren() > 0) {
                $size += $node->countDescendants();
            }
        }
        return $size;
    }

    public function findChildByObjectId($objectId)
    {
        foreach($this->childNodes as $id => $node) {
            if($node->object->getId() == $objectId) {
                return $node;
            }
            if($node->countChildren() > 0) {
                $result = $node->findChildByObjectId($objectId);
                if(!is_null($result)) {
                    return $result;
                }
            }
        }
        return null;
    }
}