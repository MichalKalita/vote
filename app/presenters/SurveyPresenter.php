<?php

namespace App\Presenters;

use App\BaseForm;
use App\Form\IDeleteFormFactory;
use App\Model\Project;
use App\Model\Survey;
use Nette\Application\UI\Form;


/**
 * Homepage presenter.
 */
class SurveyPresenter extends BasePresenter
{

	/** @var Project @inject */
	public $project;

	/** @var Survey @inject */
	public $survey;

	/** @var IDeleteFormFactory @inject */
	public $deleteFormFactory;

	public function actionShow($id)
	{
		$survey = $this->survey->get($id);
		if (!$survey) {
			$this->flashMessage($this->translator->translate('survey.not_found'), 'warning');
			$this->redirect('Project:');
		}

		$this->template->survey = $survey;
		$this->template->project = $this->project->get($survey->project);
	}

	/**
	 * @param $id Project id
	 */
	public function actionAdd($id)
	{
		$this->requireLogin();
		$this->template->project = $project = $this->project->get($id);

		if (!$project) {
			$this->flashMessage($this->translator->translate('project.not_found'), 'warning');
			$this->redirect('Project:');
		}

		$this['surveyForm']->setDefaults(['project' => $id]);
	}

	public function actionEdit($id)
	{
		$this->checkPermission($id);

		$survey = $this->survey->get($id);
		if (!$survey) {
			$this->flashMessage($this->translator->translate('survey.not_found'), 'warning');
			$this->redirect('Project:');
		}

		$this['surveyForm']->setDefaults([
			'id' => $survey->id,
			'name' => $survey->name,
		]);
	}

	public function createComponentSurveyForm()
	{
		$form = new BaseForm;
		$form->setTranslator($this->translator);

		$form->addHidden('id');
		$form->addHidden('project');

		$form->addText('name', 'survey.name')
			->setRequired('form.required')
			->setAttribute('autocomplete', 'off')
			->addRule(Form::MAX_LENGTH, 'form.max_lenght', 100);

		$form->addSubmit('submit', 'survey.submit');

		$form->onSubmit[] = $this->processForm;

		return $form;
	}

	public function processForm(Form $form)
	{
		$this->requireLogin();

		$values = $form->getValues();
		$id = $values->id;
		unset($values->id);


		if ($id) { // edit survey
			$this->checkPermission($id);
			unset($values->project);
			$this->survey->edit($id, $values);
			$this->flashMessage($this->translator->translate('survey.edited'), 'success');
		} else { // add survey
			$data = $this->survey->add($values);
			$this->flashMessage($this->translator->translate('survey.added'), 'success');
			$id = $data->id;
		}

		$this->redirect('Survey:show', ['id' => $id]);
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

	public function canEditSurvey($surveyId)
	{
		$survey = $this->survey->get($surveyId);

		if (!$survey) {
			return FALSE;
		}

		return $this->canEdit($survey->project);
	}

	public function actionDelete($id)
	{
		$this['deleteForm']->setDefaults(['id' => $id]);

		$survey = $this->survey->get($id);
		if (!$survey) {
			$this->flashMessage($this->translator->translate('survey.not_found'), 'warning');
			$this->redirect('default');
		}

		$this->template->survey = $survey;
		$this->template->project = $this->project->get($survey->project);
	}

	public function createComponentDeleteForm()
	{
		$form = $this->deleteFormFactory->create();
		$form->onSubmit[] = $this->processDelete;
		return $form;
	}

	public function processDelete(Form $form)
	{
		$values = $form->getValues();
		$survey = $this->survey->get($values->id);
		if (!$survey) {
			$this->flashMessage($this->translator->translate('survey.not_found'), 'warning');
			$this->redirect('Project:');
		}
		$this->survey->delete($values->id);
		$this->flashMessage($this->translator->translate('survey.deleted'), 'info');
		$this->redirect('Project:show', ['id' => $survey->project]);
	}
}
