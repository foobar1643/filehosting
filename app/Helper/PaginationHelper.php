<?php

namespace Filehosting\Helper;

class PaginationHelper
{
    private $records;
    private $recordsPerPage;
    private $totalPages;
    private $link;

    public function __construct($totalRecords, $recordsPerPage, $link)
    {
        $this->records = $totalRecords;
        $this->recordsPerPage = $recordsPerPage;
        $this->totalPages = $this->countPages();
        $this->link = $link;
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
        if($page == null || $page > $this->totalPages) {
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
        $pages = null;
        $page = $this->records;
        while(0 < $page) {
            $page -= $this->recordsPerPage;
            $pages++;
        }
        return $pages;
    }
}