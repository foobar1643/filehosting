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
    /**
     * @var \Filehosting\Database\SearchGateway SearchGateway instance.
     */
    private $searchGateway;

    /**
     * @var \Filehosting\Database\FileMapper FileMapper instance.
     */
    private $fileMapper;

    /**
     * Constructor.
     *
     * @param \Filehosting\Database\SearchGateway $searchGateway A search gateway instance.
     * @param \Filehosting\Database\FileMapper $fileMapper A file mapper instance.
     */
    public function __construct(SearchGateway $searchGateway, FileMapper $fileMapper)
    {
        $this->searchGateway = $searchGateway;
        $this->fileMapper = $fileMapper;
    }

    /**
     * Executes given search query with offset and limit.
     * Returns an array with search results and total number of matches for a query.
     *
     * @param string $query A search query to execute.
     * @param int $offset A offset for a search query.
     * @param int $limit A limit for a search query.
     *
     * @return array
     */
    public function search(string $query, int $offset, int $limit)
    {
        $rawSearchResults = $this->searchGateway->searchQuery($this->escapeString($query), $offset, $limit);
        $searchMeta = $this->searchGateway->showMeta();
        $filteredResults = $this->fileMapper->getFilteredFiles($rawSearchResults);
        $results = $this->showDeleted($rawSearchResults, $filteredResults);
        return ["totalFound" => $searchMeta[0]['Value'], "results" => $results];
    }

    /**
     * Escapes a given string for a search query.
     *
     * @param string $string A string to escape.
     *
     * @return string
     */
    public function escapeString(string $string)
    {
        $from = ['\\', '(',')','|','-','!','@','~','"','&', '/', '^', '$', '='];
        $to = ['\\\\', '\(','\)','\|','\-','\!','\@','\~','\"', '\&', '\/', '\^', '\$', '\='];
        return str_replace($from, $to, $string);
    }

    /**
     * Checks if there is a deleted files in a given results array.
     * If there are deleted files in results array - marks them as such.
     *
     * @todo Refactor this code.
     *
     * @param array $ids An array with raw file IDs.
     * @param array $results An array with filtered file IDs.
     *
     * @return array
     */
    public function showDeleted(array $ids, array $results)
    {
        $newResults = $results;
        if (count($ids) > count($results)) {
            for ($i = 0; $i < (count($ids) - count($results)); $i++) {
                $deletedFile = new File();
                $deletedFile->setDeleted(true);
                array_push($newResults, $deletedFile);
            }
        }
        return $newResults;
    }
}
