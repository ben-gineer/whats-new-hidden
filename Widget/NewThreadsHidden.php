<?php

namespace Drn\WhatsNewHidden\Widget;

use Drn\WhatsNewHidden\Repository\ThreadHiddenRepository;
use XF\Widget\NewThreads;
use XF\Entity\Thread;

class NewThreadsHidden extends NewThreads
{

    /**
     * Custom render new threads that are hidden.
     * @return \XF\Widget\WidgetRenderer
     */
	public function render()
	{
		$visitor = \XF::visitor();

		$options = $this->options;
		$limit = $options['limit'];
		$style = $options['style'];
		$nodeIds = $options['node_ids'];
        $dateLimit = $options['date_limit_days'] ?? 0;

        $router = $this->app->router('public');

        /** @var ThreadHiddenRepository $repo */
        $threadRepo = $this->repository(ThreadHiddenRepository::class);

		$threadFinder = $threadRepo->findLatestThreads();
		$title = \XF::phrase('latest_threads');
		$link = $router->buildLink('market-whats-new/posts', null, ['skip' => 1]);

		$threadFinder
			->with('Forum.Node.Permissions|' . $visitor->permission_combination_id)
			->limit(max($limit * 2, 10));

		if ($nodeIds && !in_array(0, $nodeIds))
		{
			$threadFinder->where('node_id', $nodeIds);
		}

        if ($dateLimit > 0)
        {
            $threadFinder->where('post_date', '>=', \XF::$time - ($dateLimit * 86400));
        }

        if ($style == 'full')
        {
            $threadFinder->with('fullForum');
        }
        if ($style == 'expanded')
        {
            $threadFinder->with('FirstPost');
        }

		/** @var Thread $thread */
		foreach ($threads = $threadFinder->fetch() AS $threadId => $thread)
		{
			if (!$thread->canView()
                || $thread->isIgnored()
			)
			{
				unset($threads[$threadId]);
			}

			if ($options['style'] != 'expanded' && $visitor->isIgnoring($thread->last_post_user_id))
			{
				unset($threads[$threadId]);
			}
		}
		$threads = $threads->slice(0, $limit, true);

		$viewParams = [
			'title' => $this->getTitle() ?: $title,
			'link' => $link,
			'threads' => $threads,
			'style' => $options['style'],
			'showExpandedTitle' => $options['show_expanded_title']
		];
		return $this->renderer('widget_new_threads', $viewParams);
	}
}
