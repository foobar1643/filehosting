<?php

namespace Filehosting\Helper;

class PaginationHelper
{
    private $recordsPerPage;
    private $link;
    private $totalRecords;
    private $totalPages;

    public function __construct($recordsPerPage, $link)
    {
        $this->recordsPerPage = $recordsPerPage;
        $this->link = $link;
    }

    public function setTotalRecords($records)
    {
        $this->totalRecords = $records;
        $this->totalPages = $this->countPages();
    }

    public function getPages()
    {
        return $this->totalPages;
    }

    public function getOffset($page)
    {
        return ($page - 1) * $this->recordsPerPage;
    }

    public function checkPage($page)
    {
        if($page == null || $page > 30) {
            return 1;
        }
        return $page;
    }

    public function getLink($page)
    {
        return $this->link . "&" . http_build_query(["page" => $page]);
    }

    private function countPages()
    {
        return ceil($this->totalRecords / $this->recordsPerPage);
    }
}