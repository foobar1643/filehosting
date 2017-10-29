<?php

namespace Filehosting\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Filehosting\Helper\PaginationHelper;
use Filehosting\Helper\LanguageHelper;
use Slim\Container;

/**
 * Callable, provides a way to search through files.
 *
 * @todo Refactor this code.
 *
 * @package Filehosting\Controller
 * @author foobar1643 <foobar76239@gmail.com>
 */
class SearchController
{
    const RESULTS_PER_PAGE = 15;

    /**
     * @var mixed View object.
     */
    private $view;

    /**
     * @var \Filehosting\Helper\SearchHelper SearchHelper instance.
     */
    private $searchHelper;

    /**
     * Constructor.
     *
     * @param \Slim\Container $c DI container.
     */
    public function __construct(Container $c)
    {
        $this->view = $c->get('view');
        $this->searchHelper = $c->get('SearchHelper');
    }

    /**
     * A method that allows to use this class as a callable.
     *
     * @todo Refactor this code.
     *
     * @param Request $request Slim Framework request instance.
     * @param Response $response Slim Framework response instance.
     * @param array $args Array with additional arguments.
     *
     * @return Response
     */
    public function __invoke(Request $request, Response $response, array $args)
    {
        $page = is_null($request->getQueryParam('page')) ? 1 : $request->getQueryParam('page');
        $query = $request->getQueryParam('query');
        $pager = null;
        $searchResults = null;
        if (!is_null($query)) {
            $pager = new PaginationHelper(self::RESULTS_PER_PAGE, "/{$args['lang']}/search/?query={$query}");
            $page = $pager->checkPage($page);
            $offset = $pager->getOffset($page);
            $searchResults = $this->searchHelper->search($query, $offset, self::RESULTS_PER_PAGE);
            $pager->setTotalRecords($searchResults["totalFound"]);
        }

        return $this->view->render(
            $response,
            'search.twig',
            [
                'query' => $query,
                'page' => intval($page),
                'pager' => $pager,
                'files' => $searchResults["results"],
                'langHelper' => new LanguageHelper($request)
            ]
        );
    }
}
