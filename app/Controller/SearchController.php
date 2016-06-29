<?php

namespace Filehosting\Controller;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Filehosting\Helper\PaginationHelper;
use \Filehosting\Helper\LanguageHelper;

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
        $page = is_null($request->getQueryParam('page')) ? 1 : $request->getQueryParam('page');
        $query = $request->getQueryParam('query');
        $pager = null;
        $searchResults = null;
        if(!is_null($query)) { // query is not empty
            $pager = new PaginationHelper(self::RESULTS_PER_PAGE, "/{$args['lang']}/search/?query={$query}");
            $page = $pager->checkPage($page);
            $offset = $pager->getOffset($page);
            $searchResults = $this->searchHelper->search($query, $offset, self::RESULTS_PER_PAGE);
            $pager->setTotalRecords($searchResults["totalFound"]);
        }
        return $this->view->render($response, 'search.twig', [
            'query' => $query,
            'page' => intval($page),
            'pager' => $pager,
            'files' => $searchResults["results"],
            'lang' => $args['lang'],
            'langHelper' => new LanguageHelper($request)]
        );
    }
}