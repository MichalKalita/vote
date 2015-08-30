<?php

namespace App\Presenters;

use App\AdminModule\Model\UserManager;
use App\BaseForm;
use Nette\Application\UI\Form;
use Nette\Utils\Paginator;


/**
 * Presenter pro administraci dat
 */
class UserPresenter extends BasePresenter
{

	/** @persistent */
	public $backlink = '';

	const POLOZEK_NA_STRANKU = 10;

	/** @var UserManager @inject */
	public $uzivateleModel;

	public $opravneni = array('editor' => "Editor dat", 'admin' => "Administrátor");

	protected $formLatteAddress;

	public function startup()
	{
		parent::startup();
		if (!$this->user->isInRole('admin')) {
			$this->flashMessage('Akci nelze provést, protože nemáte oprávnění administrátora. '
				. 'Přihlaste se administrátorským účtem.', 'error');
			$this->redirect("Sign:in", array('backlink' => $this->storeRequest()));
		}
		$this->formLatteAddress = __DIR__ . "/../templates/form.latte";
	}

	/**
	 * Výpis uživatelů
	 * @param int
	 */
	public function renderDefault($page = 1)
	{
		$paginator = new Paginator;
		$pocet = $this->uzivateleModel->pocet();
		$paginator->setPage($page)->setItemsPerPage(self::POLOZEK_NA_STRANKU)->setItemCount($pocet);
		if ($paginator->getPage() != $page) { // je li page nastavena na neexistující, je přesměrována 
			$this->redirect('this', array('page' => $paginator->getPage()));
		}

		$data = FALSE;
		if ($pocet) {
			$data = $this->uzivateleModel->vypis($paginator);
		}

		$this->template->data = $data;
		$this->template->pocetDat = $pocet;
		$this->template->paginator = $paginator;
		$this->template->opravneni = $this->opravneni;
	}

	public function actionAdd()
	{
		$this->template->setFile($this->formLatteAddress);

		$this->template->nadpis = "Přidat uživatele";
		$this['uzivatelForm-heslo']->setRequired("Zadejte heslo");
		$this->template->form = $this['uzivatelForm'];
	}

	public function actionEdit($id)
	{
		$this->template->setFile($this->formLatteAddress);

		$u = $this->uzivateleModel->nacti($id);
		$form = $this['uzivatelForm'];
		$form['heslo']->setOption('description',
			'Chcete-li změnit heslo, zadejte jej. Pokud ponecháte prázdné, heslo nebude změněno.');
		$form->setDefaults(array(
			'id' => (int)$id,
			'jmeno' => $u[UserManager::COLUMN_NAME],
			'opravneni' => $u[UserManager::COLUMN_ROLE],
		));

		$this->template->nadpis = "Upravit uživatele";
		$this->template->form = $form;
	}

	public function createComponentUzivatelForm()
	{
		$form = new BaseForm;
		$form->addHidden("id");
		$form->addText("jmeno", "Jméno:")
			->setRequired("Zadejte jméno uživatele");
		$form->addText("heslo", "Heslo:");
		$form->addSelect('opravneni', "Oprávnění:")
			->setPrompt("Zvolte oprávnění")
			->setItems($this->opravneni)
			->setRequired("Zadejte oprávnění");
		$form->addSubmit("submit", "Uložit");
		$form->onSuccess[] = $this->uzivatelFormSubmit;
		return $form;
	}

	public function uzivatelFormSubmit(Form $form)
	{
		$val = $form->getValues();
		try {
			if ($val->id) {
				$this->uzivateleModel->uprav($val->id, $val->jmeno, $val->heslo,
					$val->opravneni);
				$this->flashMessage($this->translator->translate('admin.user.edited'));
			} else {
				$this->uzivateleModel->pridat($val->jmeno, $val->heslo, $val->opravneni);
				$this->flashMessage($this->translator->translate('admin.user.added'));
			}
			$this->restoreRequest($this->backlink);
			$this->redirect('uzivatele');
		} catch (\PDOException $e) {
			$this->flashMessage("Akce se nezdařila, chyba: {$e->getMessage()}", 'error');
		}
	}

	public function actionDelete($id, $backlink = NULL)
	{
		$d = $this->uzivateleModel->odstran($id);
		if ($d) {
			$this->flashMessage("Odstraněno", 'success');
		} else {
			$this->flashMessage("Nezdařilo se odstranit", 'error');
		}
		$this->restoreRequest($backlink);
		$this->redirect("uzivatele");
	}

}
