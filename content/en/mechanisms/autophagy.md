---
id: mechanism:autophagy
type: mechanism
title: Autophagy
summary: Autophagy is the cell's own recycling system — it captures cytoplasmic material in vesicles and delivers it to lysosomes for breakdown. Its decline with age is one of the best-documented changes in aging cells.
status: published
locale: en
topics:
  - aging-mechanisms
tags:
  - lysosome
  - proteostasis
  - macroautophagy
aliases:
  - Macroautophagy
created: 2026-11-20
updated: 2026-11-24
license: CC-BY-SA-4.0
review:
  level: community
relations:
  - type: part_of
    target: concept:biological-aging
  - type: regulated_by
    target: protein:mtor
  - type: related_to
    target: compound:rapamycin
sources:
  - citation: Mizushima N, Komatsu M. Autophagy - renovation of cells and tissues. Cell. 2011;147(4):728–741.
    doi: 10.1016/j.cell.2011.10.026
    url: https://doi.org/10.1016/j.cell.2011.10.026
  - citation: Rubinsztein DC, Mariño G, Kroemer G. Autophagy and aging. Cell. 2011;146(5):682–695.
    doi: 10.1016/j.cell.2011.07.030
    url: https://doi.org/10.1016/j.cell.2011.07.030
---

## Definition

Autophagy is a set of processes by which a cell degrades its own components in
lysosomes. Three forms are distinguished:

- **Macroautophagy** — the form usually meant by the word. A double-membrane
  vesicle, the autophagosome, engulfs cytoplasm, organelles or protein
  aggregates and fuses with a lysosome.
- **Microautophagy** — direct uptake of material into the lysosome by membrane
  invagination.
- **Chaperone-mediated autophagy** — selective import of individual proteins
  carrying a targeting motif.

The purpose is not only waste disposal. Autophagy supplies amino acids and lipids
during nutrient shortage, removes damaged mitochondria (mitophagy) and clears
aggregate-prone proteins. It is simultaneously a recycling system and a
quality-control system.

## Regulation

Autophagy is suppressed when nutrients are abundant and induced when they are
scarce. Two kinase complexes control the switch:

- **mTORC1** inhibits autophagy. When
  [[proteins/mtor|mTOR]] is active, the ULK1 complex is phosphorylated and held
  inactive.
- **AMPK** activates autophagy, sensing a rise in the AMP/ATP ratio.

This is why fasting, caloric restriction and mTOR inhibition all increase
autophagic flux — they converge on the same node. It is also why the effect of
[[compounds/rapamycin|rapamycin]] on autophagy is treated as a plausible, though
unproven, part of its mechanism of action in vivo.

## Autophagy and aging

Autophagic capacity declines with age in many tissues, and genetic ablation of
autophagy genes accelerates age-related phenotypes in mice. Conversely,
upregulating autophagy extends lifespan in yeast, worms, flies and mice in some
genetic models.

The causal chain is less clear than the correlation. Much of the evidence uses
genetic manipulations that also affect development, and measuring autophagic
flux in a living organism is technically demanding: the common marker, LC3-II
levels, reflects both induction and lysosomal turnover, so an increase in the
marker can mean either more autophagy or a block in its completion.

## Measurement

Autophagic flux is assessed by comparing LC3-II in the presence and absence of
lysosomal inhibitors, or with tandem fluorescent reporters that change colour
when a vesicle reaches the acidic lysosome. Studies that report LC3-II alone
should be read cautiously.
