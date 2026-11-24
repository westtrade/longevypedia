---
id: research:harrison-2009-rapamycin-mice
type: research
title: Rapamycin fed late in life extends lifespan in mice
summary: A three-site intervention study in genetically heterogeneous mice found that dietary rapamycin, started in mid or late adult life, extended median and maximum lifespan in both sexes.
status: published
locale: en
topics:
  - research-methods
  - interventions
tags:
  - rapamycin
  - lifespan
  - mice
created: 2026-11-20
updated: 2026-11-24
license: CC-BY-SA-4.0
review:
  level: community
study:
  design: animal_experiment
  species: mouse (Mus musculus)
  population: Genetically heterogeneous UM-HET3 mice of both sexes, housed at three independent test sites under standard husbandry.
  intervention: Encapsulated rapamycin added to the diet at 14 mg per kg of food, started at 9 months of age (early cohort) or 20 months of age (late cohort).
  comparator: The same diet without rapamycin.
  outcomes:
    - name: Median lifespan
      direction: increase
      measure: Kaplan–Meier estimate within each sex and cohort
    - name: Maximum lifespan
      direction: increase
      measure: Age at the 90th percentile of survival
    - name: Body weight
      direction: no_change
      measure: Regular weighing during treatment
  authors:
    - David E. Harrison
    - Randy Strong
    - Zelton Dave Sharp
  journal: Nature
  year: 2009
  doi: 10.1038/nature08221
  pmid: "19587680"
relations:
  - type: studies
    target: compound:rapamycin
  - type: supports
    target: claim:rapamycin-extends-lifespan-in-mice
sources:
  - citation: Harrison DE, Strong R, Sharp ZD, et al. Rapamycin fed late in life extends lifespan in genetically heterogeneous mice. Nature. 2009;460:392–395.
    doi: 10.1038/nature08221
    url: https://doi.org/10.1038/nature08221
    pmid: "19587680"
---

## Why this study matters

Rapamycin was the first pharmacological intervention shown to extend lifespan in
a mammal under conditions that were not designed to make the drug look good. The
study was run as a multi-site experiment with a shared protocol, so an effect had
to survive three separate animal facilities — a deliberate defence against the
single-laboratory artefacts that plague lifespan research.

## Design

Genetically heterogeneous UM-HET3 mice were used rather than an inbred strain.
This matters for interpretation: an inbred strain is a single genotype, and a
lifespan effect in one genotype says little about the next. Heterogeneous mice
carry a spread of genotypes, so the result is less likely to be a strain-specific
curiosity.

Mice were randomised to control diet or to diet containing encapsulated
rapamycin, beginning at 9 months of age in one cohort and at 20 months in
another. The late-start cohort is the more striking of the two: the intervention
began in animals already well into adult life, not in juveniles whose development
could be altered.

## Findings

Rapamycin treatment was associated with longer median and maximum lifespan in
both sexes, and the effect was present in the late-start cohort as well as the
early-start one. Body weight was not markedly different between groups, which
argues against a simple caloric-restriction explanation — although the two
interventions are not mutually exclusive.

The paper is a lifespan study, not a mechanism study. It does not establish that
the effect is mediated by [[mechanisms/autophagy|autophagy]], by reduced
translation, or by any other single downstream process.

## Limitations

- **Species.** Mice are not humans. A lifespan effect in a two-year-lived mammal
  is a hypothesis about human aging, not a demonstration of it.
- **Dose and formulation.** Encapsulated rapamycin at 14 ppm was chosen from
  prior tolerability work; other doses and schedules give different results.
- **Immunosuppression.** The mechanism that extends lifespan in mice is the same
  one that increases infection risk in transplant patients, and this study was
  not designed to measure immune competence.
- **No healthspan battery.** Functional outcomes were not the primary endpoint,
  so the study cannot distinguish "longer life" from "longer healthy life".

## What it supports

The claim that
[[claims/rapamycin-extends-lifespan-in-mice|rapamycin extends lifespan in mice]]
rests substantially on this study together with later
dose–response work. It does not support any claim about human lifespan.
