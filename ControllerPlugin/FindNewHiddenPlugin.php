<?php

namespace Drn\WhatsNewHidden\ControllerPlugin;

use XF\FindNew\AbstractHandler;
use XF\ControllerPlugin\FindNewPlugin;

class FindNewHiddenPlugin extends FindNewPlugin
{
	/**
	 * @param string $contentType;
	 *
	 * @return AbstractHandler|null
	 */
	public function getFindNewHandler($contentType)
	{
        $handlerClass = "Drn\WhatsNewHidden\FindNew\ThreadHiddenHandler";
        $handlerClass = \XF::extendClass($handlerClass);
        return new $handlerClass($contentType);
	}
}
