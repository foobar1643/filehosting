<?php

namespace Filehosting\Helper;

use Filehosting\Database\FileMapper;
use Filehosting\Database\SearchGateway;
use Filehosting\Entity\File;

/**
 * Preforms a search query, filters deleted files from the search results.
 *
 * @author foobar1643 <foobar76239@gmail.com>
 */
class SearchHelper
{
    /** @var SearchGateway $searchGateway SearchGateway instance. */
    private $searchGateway;
    /** @var FileMapper $fileMapper FileMapper instance. */
    private $fileMapper;

    /**
     * Constructor.
     *
     * @param mixed $searchGateway A search gateway instance.
     * @param mixed $fileMapper A file mapper instance.
     */
    public function __construct(SearchGateway $searchGateway, FileMapper $fileMapper)
    {
        $this->searchGateway = $searchGateway;
        $this->fileMapper = $fileMapper;
    }

    /**
     * Executes given search query with offset and limit.
     *  Returns an array with search results and total number of matches for a query.
     *
     * @param string $query A search query to execute.
     * @param int $offset A offset for a search query.
     * @param int $limit A limit for a search query.
     *
     * @return array
     */
    public function search($query, $offset, $limit)
    {
        $rawSearchResults = $this->searchGateway->searchQuery($this->escapeString($query), $offset, $limit);
        $searchMeta = $this->searchGateway->showMeta();
        $filteredResults = $this->fileMapper->getFilteredFiles($rawSearchResults);
        // This is needed because number of total records for pagination is retrieved
        // from Sphinx metadata. Therefore, search() method should return every item
        // found, even if it's deleted in the actual database.
        $results = $this->markDeleted($rawSearchResults, $filteredResults);
        return ["totalFound" => $searchMeta[0]['Value'], "results" => $results];
    }

    /**
     * Escapes a given string for a search query.
     *
     * @param string $string A string to escape.
     *
     * @return string
     */
    public function escapeString($string)
    {
        $from = array('\\', '(',')','|','-','!','@','~','"','&', '/', '^', '$', '=');
        $to = array('\\\\', '\(','\)','\|','\-','\!','\@','\~','\"', '\&', '\/', '\^', '\$', '\=');
        return str_replace($from, $to, $string);
    }

    /**
     * Checks if there is a deleted files in a given results array.
     *  If there is a deleted files - marks them as such.
     *
     * @param array $rawIds An array with raw file IDs.
     * @param array $filteredFiles An array with filtered file IDs.
     *
     * @return array
     */
    public function markDeleted($rawIds, $filteredFiles)
    {
        $diff = count($rawIds) - count($filteredFiles);
        for($i = 0; $i < $diff; $i++) {
            $deletedFile = new File();
            $deletedFile->setDeleted(true);
            $filteredFiles[] = $deletedFile;
        }
        return $filteredFiles;
    }
}