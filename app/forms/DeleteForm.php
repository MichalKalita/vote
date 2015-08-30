<?php

namespace App\Form;

use App\BaseForm;
use Nette\Localization\ITranslator;


/** Interface for generated factory */
interface IDeleteFormFactory
{
	/** @return DeleteForm */
	function create();
}

class DeleteForm extends BaseForm
{
	function __construct(ITranslator $translator)
	{
		parent::__construct();

		$this->setTranslator($translator);
		$this->addProtection();

		$this->addHidden('id');

		$this->addText('delete', 'main.write_delete')
			->addRule(self::EQUAL, 'form.equal', $translator->translate('main.write_delete_text'));

		$this->addSubmit('submit', 'main.delete');
	}
}
