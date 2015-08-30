<?php

namespace App\Presenters;

use Nette\Application\UI\Presenter;

/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Presenter
{
	/** @persistent */
	public $locale;

	/** @var \Kdyby\Translation\Translator @inject */
	public $translator;

	public function startup()
	{
		parent::startup();

		if (!in_array($this->locale, array('cs', 'en'))) {
			$this->redirect('this', array('locale' => 'en'));
		}
	}

	public function beforeRender()
	{
		parent::beforeRender();

		if ($this->isAjax()) {
			$this->redrawControl('title');
			$this->redrawControl('menu');
			$this->redrawControl('content');
		}
	}

	public function requireLogin(){
		if(!$this->user->isLoggedIn()) {
			$this->flashMessage($this->translator->translate('main.action_require_login'), 'warning');
			$this->redirect('Sign:in', ['backlink' => $this->storeRequest()]);
		}
	}
}
