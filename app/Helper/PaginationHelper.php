<?php

namespace Filehosting\Helper;

/**
 * Counts total pages for a query, calculate a database offset for a given page.
 *
 * @author foobar1643 <foobar76239@gmail.com>
 */
class PaginationHelper
{
    /** @var int $recordsPerPage Number of records to display per page. */
    private $recordsPerPage;
    /** @var string $link Link template. */
    private $link;
    /** @var int $totalRecords Total number of records in the query. */
    private $totalRecords;
    /** @var int $totalPages Total pages in the query. */
    private $totalPages;

    /**
     * Constructor.
     *
     * @param int $recordsPerPage A number of records to display per page.
     * @param string $link A link template.
     */
    public function __construct($recordsPerPage, $link)
    {
        $this->recordsPerPage = $recordsPerPage;
        $this->link = $link;
    }

    /**
     * Sets total number of records in the query.
     *
     * @param int $records Number of records.
     *
     * @return void
     */
    public function setTotalRecords($records)
    {
        $this->totalRecords = $records;
        $this->totalPages = $this->countPages();
    }

    /**
     * Returns total number of pages available.
     *
     * @return int
     */
    public function getPages()
    {
        return $this->totalPages;
    }

    /**
     * Returns a database offset value for a given page.
     *
     * @param int $page Page number.
     *
     * @return int
     */
    public function getOffset($page)
    {
        return ($page - 1) * $this->recordsPerPage;
    }

    /**
     * Validates a given page, if it's not valid - returns page number 1.
     *
     * @param int $page Page number to check.
     *
     * @return int
     */
    public function checkPage($page)
    {
        return ($page == null || $page > 30) ? 1 : $page;
    }

    /**
     * Returns a URL for a given page.
     *
     * @param int $page Page number.
     *
     * @return string
     */
    public function getLink($page)
    {
        $parsedUrl = parse_url($this->link);
        $pageQuery = http_build_query(["page" => $page]);
        return $this->link . ((isset($parsedUrl['query'])) ? "&" : "?") . $pageQuery;
    }

    /**
     * Calculates total pages using total records and records per page values.
     *
     * @return int
     */
    private function countPages()
    {
        return ceil($this->totalRecords / $this->recordsPerPage);
    }
}