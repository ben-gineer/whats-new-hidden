<?php

namespace Drn\WhatsNewHidden\Repository;

use XF\Repository\Thread;

/**
 * Class ThreadHidden
 * @package Drn\WhatsNewHidden\Repository
 */
class ThreadHidden extends Thread
{
    /**
     * Find latest threads that are hidden.
     * @return \XF\Finder\Thread
     */
    public function findLatestThreads()
    {
        return $this->finder('XF:Thread')
            ->with(['Forum', 'User'])
            ->where('node_id', $this->options()->drnWhatsNewHiddenForums)
            ->where('discussion_state', 'visible')
            ->where('discussion_type', '<>', 'redirect')
            ->order('post_date', 'DESC')
            ->indexHint('FORCE', 'post_date');
    }

    /**
     * Find latest threads with posts that are hidden.
     * @return \XF\Finder\Thread
     */
    public function findThreadsWithLatestPosts()
    {
        return $this->finder('XF:Thread')
            ->with(['Forum', 'User'])
            ->where('node_id', $this->options()->drnWhatsNewHiddenForums)
            ->where('discussion_state', 'visible')
            ->where('discussion_type', '<>', 'redirect')
            ->where('last_post_date', '>', $this->getReadMarkingCutOff())
            ->order('last_post_date', 'DESC')
            ->indexHint('FORCE', 'last_post_date');
    }

}