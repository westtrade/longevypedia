<?php

declare(strict_types=1);

/**
 * UI-набор: Источники.
 *
 * Первоисточники с DOI и PMID; отсутствующий идентификатор не выдумывается.
 *
 * Сгенерировано `scripts/ui-kit-pages.php` — правится спецификация там.
 */

$this->layout('components::layout/site-layout', [
	'title' => 'Источники — UI-набор',
	'description' => 'Первоисточники с DOI и PMID; отсутствующий идентификатор не выдумывается.',
	'current' => 'ui-kit',
]);
?>
<?php $this->start('page'); ?>
<?= $this->insert('components::ui-kit/shell', [
	'active' => 'source-list',
	'title' => 'Источники',
	'lead' => 'Первоисточники с DOI и PMID; отсутствующий идентификатор не выдумывается.',
	'body' => function (): void { ?>
		<?= $this->insert('components::ui-kit/specimen', [
			'title' => 'Основное',
			'note' => 'ссылки ведут на doi.org и PubMed',
			'code' => <<<'HTML'
				<?= $this->insert('components::ui/source-list', ['title' => 'Источники', 'sources' => [['citation' => 'Harrison D. E. et al. Rapamycin fed late in life extends lifespan in genetically heterogeneous mice. Nature, 2009.', 'doi' => '10.1038/nature08221', 'pmid' => '19587680'], ['citation' => 'Hayflick L., Moorhead P. S. The serial cultivation of human diploid cell strains. Exp. Cell Res., 1961.', 'doi' => '10.1016/0014-4827(61)90192-6']]]) ?>
				HTML,
			'body' => function (): void { ?>
				<?= $this->insert('components::ui/source-list', ['title' => 'Источники', 'sources' => [['citation' => 'Harrison D. E. et al. Rapamycin fed late in life extends lifespan in genetically heterogeneous mice. Nature, 2009.', 'doi' => '10.1038/nature08221', 'pmid' => '19587680'], ['citation' => 'Hayflick L., Moorhead P. S. The serial cultivation of human diploid cell strains. Exp. Cell Res., 1961.', 'doi' => '10.1016/0014-4827(61)90192-6']]]) ?>
			<?php },
		]) ?>
	<?php },
]) ?>
<?php $this->end(); ?>
