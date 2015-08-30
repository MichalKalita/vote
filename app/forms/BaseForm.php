<?php

namespace App;

use Nette\Application\UI\Form,
    Nette\Forms\Controls;


class BaseForm extends Form
{

	private $labelColumns = 3;
	public $countOfColumns = 12;
	public $automaticButtonClass = TRUE;

	public function getLabelColumns()
	{
		return $this->labelColumns;
	}

	public function setLabelColumns($value)
	{
		if ($this->labelColumns >= $this->countOfColumns) {
			throw new \Nette\UnexpectedValueException;
		}
		$this->labelColumns = (int) $value;
	}

	public function render()
	{
		$renderer = $this->getRenderer();
		$renderer->wrappers['controls']['container'] = NULL;
		$renderer->wrappers['pair']['container'] = 'div class=form-group';
		$renderer->wrappers['pair']['.error'] = 'has-error';
		$renderer->wrappers['control']['container'] = 'div class=col-sm-' . ($this->countOfColumns - $this->labelColumns);
		$renderer->wrappers['label']['container'] = 'div class="col-sm-' . $this->labelColumns . ' control-label"';
		$renderer->wrappers['control']['description'] = 'span class=help-block';
		$renderer->wrappers['control']['errorcontainer'] = 'span class=help-block';

// make form and controls compatible with Twitter Bootstrap
		$this->getElementPrototype()->class('form-horizontal');

		foreach ($this->getControls() as $control) {
			if ($control instanceof Controls\Button && $this->automaticButtonClass) {
				$control->setAttribute('class',
					empty($usedPrimary) ? 'btn btn-primary' : 'btn btn-default');
				$usedPrimary = TRUE;
			} elseif ($control instanceof Controls\TextBase || $control instanceof Controls\SelectBox || $control instanceof Controls\MultiSelectBox) {
				$control->setAttribute('class', 'form-control');
			} elseif ($control instanceof Controls\Checkbox || $control instanceof Controls\CheckboxList || $control instanceof Controls\RadioList) {
				$control->getSeparatorPrototype()->setName('div')->class($control->getControlPrototype()->type);
			}
		}
		parent::render();
	}

}
