<?php

namespace Drn\WhatsNewHidden\Widget;

use XF\Widget\NewThreads;

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

		$router = $this->app->router('public');

        /** @var \Drn\WhatsNewHidden\Repository\ThreadHidden $repo */
        $threadRepo = $this->repository('Drn\WhatsNewHidden:ThreadHidden');

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

		if ($style == 'full' || $style == 'expanded')
		{
			$threadFinder->forFullView(true);
			if ($style == 'expanded')
			{
				$threadFinder->with('FirstPost');
			}
		}

		/** @var \XF\Entity\Thread $thread */
		foreach ($threads = $threadFinder->fetch() AS $threadId => $thread)
		{
			if (!$thread->canView()
				|| $visitor->isIgnoring($thread->user_id)
			)
			{
				unset($threads[$threadId]);
			}

			if ($options['style'] != 'expanded' && $visitor->isIgnoring($thread->last_post_user_id))
			{
				unset($threads[$threadId]);
			}
		}
		$total = $threads->count();
		$threads = $threads->slice(0, $limit, true);

		$viewParams = [
			'title' => $this->getTitle() ?: $title,
			'link' => $link,
			'threads' => $threads,
			'style' => $options['style'],
			'hasMore' => $total > $threads->count(),
			'showExpandedTitle' => $options['show_expanded_title']
		];
		return $this->renderer('widget_new_threads', $viewParams);
	}
}