<?php

namespace Filehosting\Controller;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Filehosting\Helper\PaginationHelper;

class SearchController
{
    const RESULTS_PER_PAGE = 15;

    private $view;
    private $searchHelper;

    public function __construct(\Slim\Container $c)
    {
        $this->view = $c->get('view');
        $this->searchHelper = $c->get('SearchHelper');
    }

    public function __invoke(Request $request, Response $response, $args)
    {
        $params = $request->getQueryParams();
        $page = 1;
        $query = null;
        $pager = null;
        $error = false;
        $searchResults = null;
        if(isset($params["query"])) {
            if(trim($params["query"]) == null) {
                $error = true;
            } else {
                $query = $params["query"];
                $pager = new PaginationHelper(self::RESULTS_PER_PAGE, "/search?query={$query}");
                if(isset($params["page"])) {
                    $page = $pager->checkPage($params["page"]);
                }
                $offset = $pager->getOffset($page);
                $searchResults = $this->searchHelper->search($query, $offset, self::RESULTS_PER_PAGE);
                $pager->setTotalRecords($searchResults["totalFound"]);
            }
        }
        return $this->view->render($response, 'search.twig', [
            'error' => $error,
            "query" => $query,
            "page" => intval($page),
            "pager" => $pager,
            "files" => $searchResults["results"]]
        );
    }
}