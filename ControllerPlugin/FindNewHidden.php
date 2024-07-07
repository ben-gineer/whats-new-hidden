<?php

namespace Drn\WhatsNewHidden\ControllerPlugin;

use XF\ControllerPlugin\FindNew;

class FindNewHidden extends FindNew
{
	/**
	 * @param string $contentType;
	 *
	 * @return \XF\FindNew\AbstractHandler|null
	 */
	public function getFindNewHandler($contentType)
	{
        $handlerClass = "Drn\WhatsNewHidden\FindNew\ThreadHidden";
        $handlerClass = \XF::extendClass($handlerClass);
        return new $handlerClass($contentType);
	}
}