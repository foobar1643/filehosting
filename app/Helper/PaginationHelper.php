<?php

namespace Filehosting\Helper;

/**
 * Counts total pages for a query, calculate a database offset for a given page.
 *
 * @author foobar1643 <foobar76239@gmail.com>
 */
class PaginationHelper
{
    /**
     * @var int Number of records to display per page.
     */
    private $recordsPerPage;

    /**
     * @var string Link template.
     */
    private $link;

    /**
     * @var int Total number of records in the query.
     */
    private $totalRecords;

    /**
     * @var int Total pages in the query.
     */
    private $totalPages;

    /**
     * Constructor.
     *
     * @param int $recordsPerPage A number of records to display per page.
     * @param string $link A link template.
     */
    public function __construct(int $recordsPerPage, string $link)
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
    public function getPages(): int
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
    public function getOffset($page): int
    {
        return ($page - 1) * $this->recordsPerPage;
    }

    /**
     * Validates a given page, if it's not valid - returns page number 1.
     *
     * @todo Do something with a maximum page value, right now it's forced number 30, which is not very smart.
     *
     * @param int $page Page number to check.
     *
     * @return int
     */
    public function checkPage(int $page): int
    {
        return ($page == null || $page > 30) ? 1 : $page;
    }

    /**
     * Returns a URL for a given page.
     *
     * @todo Relocate this method to a LinkHelper class.
     *
     * @param int $page Page number.
     *
     * @return string
     */
    public function getLink($page): string
    {
        $parsedUrl = parse_url($this->link);
        $pageQuery = http_build_query(["page" => $page]);

        return (isset($parsedUrl['query'])) ? "{$this->link}&{$pageQuery}" : "{$this->link}?{$pageQuery}";
    }

    /**
     * Calculates total pages using total records and records per page values.
     *
     * @return int
     */
    private function countPages(): int
    {
        return ceil($this->totalRecords / $this->recordsPerPage);
    }
}
