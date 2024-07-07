<?php

namespace Drn\WhatsNewHidden\Pub\Controller;

use XF\Pub\Controller\WhatsNewPost;

use XF\Mvc\ParameterBag;

class WhatsNewPostHidden extends WhatsNewPost
{

    public function actionIndex(ParameterBag $params)
    {
        if ($this->options()->drnWhatsNewHiddenStyleId) {
            $this->setViewOption('style_id', $this->options()->drnWhatsNewHiddenStyleId);
        }

        /** @var \Drn\WhatsNewHidden\ControllerPlugin\FindNewHidden $findNewPlugin */
        $findNewPlugin = $this->plugin('Drn\WhatsNewHidden:FindNewHidden');
        $contentType = $this->getContentType();

        $handler = $findNewPlugin->getFindNewHandler($contentType);
        if (!$handler)
        {
            return $this->noPermission();
        }

        $findNew = $findNewPlugin->getFindNewRecord($params->find_new_id, $contentType);
        if (!$findNew)
        {
            $filters = $findNewPlugin->getRequestedFilters($handler);
            $reply = $this->triggerNewFindNewAction($handler, $filters);

            if ($this->filter('save', 'bool') && $this->isPost())
            {
                $findNewPlugin->saveDefaultFilters($handler, $filters);
            }

            return $reply;
        }
        else
        {
            $remove = $this->filter('remove', 'str');
            if ($remove)
            {
                $filters = $findNew->filters;
                unset($filters[$remove]);

                return $this->triggerNewFindNewAction($handler, $filters);
            }
        }

        $page = $this->filterPage($params->page);
        $perPage = $handler->getResultsPerPage();

        if (!$findNew->result_count)
        {
            return $handler->getPageReply($this, $findNew, [], 1, $perPage);
        }

        $this->assertValidPage($page, $perPage, $findNew->result_count, $handler->getRoute(), $findNew);

        $pageIds = $findNew->getPageResultIds($page, $perPage);
        $results = $handler->getPageResults($pageIds);

        return $handler->getPageReply(
            $this, $findNew, $results->toArray(), $page, $perPage
        );
    }

    protected function triggerNewFindNewAction(\XF\FindNew\AbstractHandler $handler, array $filters)
    {
        /** @var \Drn\WhatsNewHidden\ControllerPlugin\FindNewHidden $findNewPlugin */
        $findNewPlugin = $this->plugin('Drn\WhatsNewHidden:FindNewHidden');

        $findNew = $findNewPlugin->runFindNewSearch($handler, $filters);
        if (!$findNew->result_count && !$findNew->filters)
        {
            // we can only bail out early without filters, because we need an idea to be able to modify them easily
            return $handler->getPageReply($this, $findNew, [], 1, $handler->getResultsPerPage());
        }

        if (!$findNew->exists())
        {
            $findNew->save();
        }

        return $this->redirect($this->buildLink($handler->getRoute(), $findNew));
    }
}