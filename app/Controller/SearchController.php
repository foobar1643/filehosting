<?php

namespace Filehosting\Controller;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Filehosting\Model\File;
use \Filehosting\Helper\SearchHelper;
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
                $searchHelper = new SearchHelper();
                $query = $params["query"];
                $searchCount = $searchGateway->countSearchResults($searchHelper->escapeString($query));
                $pager = new PaginationHelper($searchCount, self::RESULTS_PER_PAGE, "/search?query={$query}");
                if(isset($params["page"])) {
                    $page = $pager->checkPage($params["page"]);
                }
                $offset = $pager->getOffset($page);
                $searchIds = $searchGateway->search($searchHelper->escapeString($query), self::RESULTS_PER_PAGE, $offset);
                $searchResults = $searchHelper->filterSearchResults($fileMapper, $searchIds);
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
