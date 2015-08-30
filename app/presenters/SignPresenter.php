<?php

namespace App\Presenters;

use Nette,
	\App\BaseForm,
	\Nette\Application\UI\Form,
	\Nette\Security\IAuthenticator;


/**
 * Sign in/out presenters.
 */
class SignPresenter extends BasePresenter
{

	/** @persistent */
	public $backlink = '';

	/**
	 * Sign-in form factory.
	 * @return Nette\Application\UI\Form
	 */
	protected function createComponentSignInForm()
	{
		$form = new BaseForm;
		$form->setTranslator($this->translator);
		$form->addText('username', 'sign.username')
				->setRequired('sign.enter_username');

		$form->addPassword('password', 'sign.password')
				->setRequired('sign.enter_password');

		$form->addSubmit('send', 'sign.login');

		// call method signInFormSucceeded() on success
		$form->onSuccess[] = $this->signInFormSucceeded;
		return $form;
	}

	public function signInFormSucceeded(Form $form)
	{
		$values = $form->getValues();

		try {
			$this->getUser()->setExpiration('20 minutes', TRUE);

			$this->getUser()->login($values->username, $values->password);
			$this->restoreRequest($this->backlink); // přesměrování na stránku, odkud uživatel přišel
			$this->redirect('Homepage:');
		} catch (Nette\Security\AuthenticationException $e) {
			if ($e->getCode() == IAuthenticator::IDENTITY_NOT_FOUND) {
				$form->addError($this->translator->translate("sign.user_not_exist"));
			} elseif ($e->getCode() == IAuthenticator::INVALID_CREDENTIAL) {
				$form->addError($this->translator->translate("sign.wrong_password"));
			} else {
				$form->addError($e->getMessage());
			}
		}
	}

	public function actionOut()
	{
		$this->getUser()->logout();
		$this->flashMessage($this->translator->translate('sign.you_are_logout'));
		$this->redirect('in');
	}

}
