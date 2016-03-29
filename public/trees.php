<?php

class TreeNode {
    public $name;
    public $parrent;
    public $childNodes = [];

    public function __construct($name, $parrent = null) {
        $this->name = $name;
        $this->parrentName = $parrent;
    }

    public function addChild($name) {
        $child = new TreeNode($name, $this->name);
        array_push($this->childNodes, $child);
        return $child;
    }

    public function getParrent() {
        return $this->parrent;
    }

    public function coundDescendants() {
        return count($this->childNodes);
    }

    public function getDescendants() {
        return $this->childNodes;
    }
}

function printTree($tree, $depth = 0) {
    for($i = 0; $i < $depth; $i++) print("...\\");
    print("{$tree->name}");
    if($tree->coundDescendants() > 0) print(" ({$tree->coundDescendants()})");
    print("<br>");
    foreach($tree->getDescendants() as $descendant) {
        printTree($descendant, $depth + 1);
    }
}

$tree = new TreeNode("Category 1");
$product1 = $tree->addChild("Product 1", $tree);
$subProduct1 = $product1->addChild("Sub Product 1", $product1);
$subProduct1->addChild("Sub Sub Product 1", $subProduct1)->addChild("Another!", $subProduct1);
$subProduct1->addChild("Sub Sub Product 2", $subProduct1);
$subProduct1->addChild("Sub Sub Product 3", $subProduct1);
$subProduct1->addChild("Sub Sub Product 4", $subProduct1);
$product1->addChild("Sub Product 2", $product1);
$product2 = $tree->addChild("Product 2", $tree);
$product2->addChild("Sub Product 3", $product2);
$tree->addChild("Product 3", $tree);

printTree($tree);