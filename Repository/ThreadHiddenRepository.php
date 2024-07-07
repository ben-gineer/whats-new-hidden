<?php

namespace Drn\WhatsNewHidden\Repository;

use XF\Finder\ThreadFinder;
use XF\Repository\ThreadRepository;

/**
 * Class ThreadHiddenRepository
 * @package Drn\WhatsNewHidden\Repository
 */
class ThreadHiddenRepository extends ThreadRepository
{
    /**
     * Find latest threads that are hidden.
     * @return ThreadFinder
     */
    public function findLatestThreads()
    {
        return $this->finder(ThreadFinder::class)
            ->with(['Forum', 'User'])
            ->where('node_id', $this->options()->drnWhatsNewHiddenForums)
            ->where('discussion_state', 'visible')
            ->where('discussion_type', '<>', 'redirect')
            ->order('post_date', 'DESC')
            ->indexHint('FORCE', 'post_date');
    }

    /**
     * Find latest threads with posts that are hidden.
     * @return ThreadFinder
     */
    public function findThreadsWithLatestPosts()
    {
        return $this->finder(ThreadFinder::class)
            ->with(['Forum', 'User'])
            ->where('node_id', $this->options()->drnWhatsNewHiddenForums)
            ->where('discussion_state', 'visible')
            ->where('discussion_type', '<>', 'redirect')
            ->where('last_post_date', '>', $this->getReadMarkingCutOff())
            ->order('last_post_date', 'DESC')
            ->indexHint('FORCE', 'last_post_date');
    }

}
