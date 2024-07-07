<?php

namespace Drn\WhatsNewHidden\FindNew;

use XF\Entity\FindNew;
use XF\Entity\Thread;
use XF\Finder\ThreadFinder;
use XF\FindNew\ThreadHandler;
use XF\Mvc\Controller;

class ThreadHiddenHandler extends ThreadHandler
{
	public function getRoute()
	{
		// This returns threads, so we attach to the thread content type. However, as it's really individual posts
		// that generally bump things up, we refer to this in the interface as "new posts".
		return 'market-whats-new/posts';
	}

	public function getPageReply(Controller $controller, FindNew $findNew, array $results, $page, $perPage)
	{
		$canInlineMod = false;

		/** @var Thread $thread */
		foreach ($results AS $thread)
		{
			if ($thread->canUseInlineModeration())
			{
				$canInlineMod = true;
				break;
			}
		}

		$viewParams = [
			'findNew' => $findNew,

			'page' => $page,
			'perPage' => $perPage,

			'threads' => $results,
			'canInlineMod' => $canInlineMod
		];
		return $controller->view('Drn\WhatsNewHidden:WhatsNewHidden\Posts', 'whats_new_posts_hidden', $viewParams);
	}

	public function getResultIds(array $filters, $maxResults)
	{
		$visitor = \XF::visitor();

		/** @var ThreadFinder $threadFinder */
		$threadFinder = \XF::finder(ThreadFinder::class)
			->with('Forum', true)
			->with('Forum.Node.Permissions|' . $visitor->permission_combination_id)
            ->where('node_id', $visitor->app()->options()->drnWhatsNewHiddenForums)
            ->where('discussion_type', '<>', 'redirect')
			->where('discussion_state', '<>', 'deleted')
			->order('last_post_date', 'DESC');

		$this->applyFilters($threadFinder, $filters);

		$threads = $threadFinder->fetch($maxResults);
		$threads = $this->filterResults($threads);

		// TODO: consider overfetching or some other permission limits within the query

		return $threads->keys();
	}
}
