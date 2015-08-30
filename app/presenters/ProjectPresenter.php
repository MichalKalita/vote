<?php

namespace App\Presenters;

use App\BaseForm;
use App\Form\IDeleteFormFactory;
use App\Model\Project;
use Nette\Application\UI\Form;


/**
 * Homepage presenter.
 */
class ProjectPresenter extends BasePresenter
{

	/** @var Project @inject */
	public $project;

	/** @var IDeleteFormFactory @inject */
	public $deleteFormFactory;

	/**
	 * List of projects
	 */
	public function renderDefault()
	{
		$this->template->projects = $this->project->getAll();
	}

	public function actionShow($id)
	{
		$project = $this->project->get($id);
		if (!$project) {
			$this->flashMessage($this->translator->translate('project.not_found'), 'warning');
			$this->redirect('Project:');
		}

		$this->template->project = $project;
	}

	public function actionAdd()
	{
		$this->requireLogin();
	}

	public function actionEdit($id)
	{
		$this->checkPermission($id);

		$project = $this->project->get($id);
		if (!$project) {
			$this->flashMessage($this->translator->translate('project.not_found'), 'warning');
			$this->redirect('Project:');
		}

		$this['projectForm']->setDefaults([
			'id' => $project->id,
			'name' => $project->name,
			'text' => $project->text,
		]);
	}

	public function createComponentProjectForm()
	{
		$form = new BaseForm;
		$form->setTranslator($this->translator);

		$form->addHidden('id');

		$form->addText('name', 'project.name')
			->setRequired('form.required')
			->addRule(Form::MAX_LENGTH, 'form.max_lenght', 100);

		$form->addTextArea('text', 'project.text')
			->addRule(Form::MAX_LENGTH, 'form.max_length', 2000);

		$form->addSubmit('submit', 'project.submit');

		$form->onSubmit[] = $this->processForm;

		return $form;
	}

	public function processForm(Form $form)
	{
		$this->requireLogin();

		$values = $form->getValues();
		$id = $values->id;
		unset($values->id);


		if ($id) { // edit project
			$this->checkPermission($id);
			$this->project->edit($id, $values);
		} else { // add project
			$values->author = $this->user->id;
			$data = $this->project->add($values);
			$id = $data->id;
		}

		$this->redirect('Project:show', ['id' => $id]);
	}

	public function checkPermission($projectID)
	{
		$this->requireLogin();

		if (!$this->canEdit($projectID)) {
			$this->flashMessage($this->translator->translate('project.not_required_edit'), 'warning');
			$this->redirect('Project:show', ['id' => $projectID]);
		}
	}

	public function canEdit($projectId)
	{
		if (!$this->user->isLoggedIn()) {
			return FALSE;
		}
		if (!$this->user->isInRole('admin')) {
			$project = $this->project->get($projectId);
			if (!$project || $project->author != $this->user->id) {
				return FALSE;
			}
		}
		return TRUE;
	}

	public function actionDelete($id)
	{
		$this->template->project = $project = $this->project->get($id);
		if(!$project) {
			$this->flashMessage($this->translator->translate('project.not_found'), 'warning');
			$this->redirect('default');
		}
		$this['deleteForm']->setDefaults(['id' => $id]);
	}

	public function createComponentDeleteForm(){
		$form = $this->deleteFormFactory->create();
		$form->onSubmit[] = $this->processDelete;
		return $form;
	}

	public function processDelete(Form $form)
	{
		$values = $form->getValues();
		$this->project->delete($values->id);
		$this->flashMessage($this->translator->translate('project.deleted'), 'info');
		$this->redirect('default');
	}
}
