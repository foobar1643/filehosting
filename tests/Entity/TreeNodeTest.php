<?php

namespace Testsuite\Entity;

use PHPUnit\Framework\TestCase;
use Filehosting\Entity\TreeNode;
use Testsuite\Utils\Factory;

class TreeNodeTest extends TestCase
{
    protected $treeRoot;
    protected $searchableObjId;

    protected function setUp()
    {
        $this->treeRoot = Factory::treeNodeFactory();
        $firchBranch = Factory::treeNodeFactory();
        $firchBranch->addChildNode(Factory::treeNodeFactory());
        $secondBranch = Factory::treeNodeFactory();
        $secondBranch->addChildNode(Factory::treeNodeFactory());
        $searchableNode = Factory::treeNodeFactory();
        $this->searchableObjId = $searchableNode->getObject()->getId();
        $secondBranch->addChildNode($searchableNode);
        $this->treeRoot->addChildNode($firchBranch);
        $this->treeRoot->addChildNode($secondBranch);
    }

    public function testChildrenCount()
    {
        $this->assertEquals(2, $this->treeRoot->countChildren());
    }

    public function testDescendantsCount()
    {
        $this->assertEquals(5, $this->treeRoot->countDescendants());
    }

    public function testAddChildNode()
    {
        $expectedChildrenCount = $this->treeRoot->countChildren() + 1;
        $this->treeRoot->addChildNode(Factory::treeNodeFactory());
        $this->assertEquals($expectedChildrenCount, $this->treeRoot->countChildren());
    }

    public function testFindChildByObjectId()
    {
        $object = $this->treeRoot->findChildByObjectId($this->searchableObjId)->getObject();
        $this->assertEquals($this->searchableObjId, $object->getId());
    }
}