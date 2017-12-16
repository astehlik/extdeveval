<?php

namespace Tx\Extdeveval\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 extension "extdeveval".               *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\CMS\Backend\View\BackendTemplateView;
use TYPO3\CMS\Core\FormProtection\FormProtectionFactory;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;

/**
 * Abstract action controller.
 */
class AbstractModuleController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{
    /**
     * Backend Template Container
     *
     * @var string
     */
    protected $defaultViewObjectName = BackendTemplateView::class;

    /**
     * BackendTemplateContainer
     *
     * @var BackendTemplateView
     */
    protected $view;

    protected function initializeView(ViewInterface $view)
    {
        $view->assign('moduleToken', $this->getFormProtectionToken());
    }

    protected function getFormProtectionToken(): string
    {
        return FormProtectionFactory::get()->generateToken('moduleCall', 'tools_ExtdevevalExtdevevalmain');
    }
}
