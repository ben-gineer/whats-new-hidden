<?php

namespace Drn\WhatsNewHidden\Pub\Controller;

use XF\Pub\Controller\WhatsNew;

class WhatsNewHidden extends WhatsNew
{
    public function actionIndex()
    {
        $this->assertCanonicalUrl($this->buildLink('market-whats-new'));

        $viewParams = [];
        return $this->view('XF:WhatsNew\Overview', 'whats_new_hidden', $viewParams);
    }

    public function actionPosts()
    {
        return $this->redirectPermanently(
            $this->buildLink('market-whats-new/posts')
        );
    }
}