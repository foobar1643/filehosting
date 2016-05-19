<?php

namespace Filehosting\Controller;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Filehosting\Helper\PaginationHelper;

class SearchController
{
    const RESULTS_PER_PAGE = 15;

    private $container;

    public function __construct(\Slim\Container $c)
    {
        $this->container = $c;
    }

    public function __invoke(Request $request, Response $response, $args)
    {
        $params = $request->getQueryParams();
        $page = 1;
        $query = null;
        $pager = null;
        $error = false;
        $searchResults = null;
        $files = null;
        if(isset($params["query"])) {
            if(trim($params["query"]) != null) {
                $searchGateway = $this->container->get('SearchGateway');
                $fileMapper = $this->container->get('FileMapper');
                $searchHelper = $this->container->get('SearchHelper');
                $query = $params["query"];
                $pager = new PaginationHelper(self::RESULTS_PER_PAGE, "/search?query={$query}");
                if(isset($params["page"])) {
                    $page = $pager->checkPage($params["page"]);
                }
                $offset = $pager->getOffset($page);
                $searchIds = $searchGateway->search($searchHelper->escapeString($query), self::RESULTS_PER_PAGE, $offset);
                $searchMeta = $searchGateway->showMeta(); // results count
                $pager->setTotalRecords($searchMeta[0]['Value']);
                $filteredResults = $fileMapper->getFilteredFiles($searchIds);
                $searchResults = $searchHelper->showDeleted($searchIds, $filteredResults);
            } else {
                $error = true;
            }
        }
        return $this->container->get('view')->render($response, 'search.twig', [
            'error' => $error,
            "query" => $query,
            "page" => intval($page),
            "pager" => $pager,
            "files" => $searchResults]
        );
    }
}