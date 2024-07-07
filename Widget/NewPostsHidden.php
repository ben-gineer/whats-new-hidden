<?php

namespace Drn\WhatsNewHidden\Widget;

use XF\Widget\NewPosts;

class NewPostsHidden extends NewPosts
{

    /**
     * Custom render new posts that are hidden.
     * @return \XF\Widget\WidgetRenderer
     */
	public function render()
	{
		$visitor = \XF::visitor();

		$options = $this->options;
		$limit = $options['limit'];
		$filter = $options['filter'];
		$nodeIds = $options['node_ids'];

		if (!$visitor->user_id)
		{
			$filter = 'latest';
		}

		$router = $this->app->router('public');

        /** @var \Drn\WhatsNewHidden\Repository\ThreadHidden $repo */
        $threadRepo = $this->repository('Drn\WhatsNewHidden:ThreadHidden');

		switch ($filter)
		{
			default:
			case 'latest':
				$threadFinder = $threadRepo->findThreadsWithLatestPosts();
				$title = \XF::phrase('widget.latest_posts');
				$link = $router->buildLink('market-whats-new/posts', null, ['skip' => 1]);
				break;

			case 'unread':
				$threadFinder = $threadRepo->findThreadsWithUnreadPosts();
				$title = \XF::phrase('widget.unread_posts');
				$link = $router->buildLink('market-whats-new/posts', null, ['unread' => 1]);
				break;

			case 'watched':
				$threadFinder = $threadRepo->findThreadsForWatchedList();
				$title = \XF::phrase('widget.latest_watched');
				$link = $router->buildLink('market-whats-new/posts', null, ['watched' => 1]);
				break;
		}

		$threadFinder
			->with('Forum.Node.Permissions|' . $visitor->permission_combination_id)
			->limit(max($limit * 2, 10));

		if ($nodeIds && !in_array(0, $nodeIds))
		{
			$threadFinder->where('node_id', $nodeIds);
		}

		if ($options['style'] == 'full')
		{
			$threadFinder->forFullView(true);
		}
		else
		{
			$threadFinder
				->with('LastPoster')
				->withReadData();
		}

		/** @var \XF\Entity\Thread $thread */
		foreach ($threads = $threadFinder->fetch() AS $threadId => $thread)
		{
			if (!$thread->canView()
				|| $visitor->isIgnoring($thread->user_id)
				|| $visitor->isIgnoring($thread->last_post_user_id)
			)
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
			'filter' => $filter,
			'hasMore' => $total > $threads->count()
		];
		return $this->renderer('widget_new_posts', $viewParams);
	}
}