<?php

namespace Filehosting\Helper;

use \Filehosting\Database\FileMapper;
use \Filehosting\Entity\File;

class SearchHelper
{
    private $searchGateway;
    private $fileMapper;

    public function __construct($searchGateway, $fileMapper)
    {
        $this->searchGateway = $searchGateway;
        $this->fileMapper = $fileMapper;
    }

    public function search($query, $offset, $limit)
    {
        $rawSearchResults = $this->searchGateway->searchQuery($this->escapeString($query), $offset, $limit);
        $searchMeta = $this->searchGateway->showMeta();
        $filteredResults = $this->fileMapper->getFilteredFiles($rawSearchResults);
        $results = $this->showDeleted($rawSearchResults, $filteredResults);
        return ["totalFound" => $searchMeta[0]['Value'], "results" => $results];
    }

    public function escapeString($string)
    {
        $from = array('\\', '(',')','|','-','!','@','~','"','&', '/', '^', '$', '=');
        $to = array('\\\\', '\(','\)','\|','\-','\!','\@','\~','\"', '\&', '\/', '\^', '\$', '\=');
        return str_replace($from, $to, $string);
    }

    public function showDeleted($ids, $results)
    {
        $newResults = $results;
        if(count($ids) > count($results)) {
            for($i = 0; $i < (count($ids) - count($results)); $i++) {
                $deletedFile = new File();
                $deletedFile->setDeleted(true);
                array_push($newResults, $deletedFile);
            }
        }
        return $newResults;
    }
}