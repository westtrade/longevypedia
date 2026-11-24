---
id: protein:mtor
type: protein
title: mTOR
summary: mTOR is a kinase that sits at the centre of nutrient sensing, growth and autophagy. Its two complexes, mTORC1 and mTORC2, have different inputs and different jobs.
status: published
locale: en
topics:
  - aging-mechanisms
tags:
  - mTORC1
  - mTORC2
  - nutrient-sensing
  - TOR
aliases:
  - Mechanistic target of rapamycin
  - MTOR
  - mTORC1
identifiers:
  hgnc: HGNC:3942
  uniprot: P42345
created: 2026-11-24
updated: 2026-11-24
license: CC-BY-SA-4.0
review:
  level: community
relations:
  - type: regulates
    target: mechanism:autophagy
  - type: targeted_by
    target: compound:rapamycin
  - type: related_to
    target: mechanism:cellular-senescence
sources:
  - citation: Saxton RA, Sabatini DM. mTOR signaling in growth, metabolism, and disease. Cell. 2017;168(6):960–976.
    doi: 10.1016/j.cell.2017.02.004
    url: https://doi.org/10.1016/j.cell.2017.02.004
---

## What it is

mTOR — mechanistic target of rapamycin — is a serine/threonine protein kinase
that belongs to the PI3K-related kinase family. It is conserved from yeast, where
it was identified through mutants resistant to rapamycin, to humans.

mTOR does not act alone. It is the catalytic subunit of two distinct complexes:

| Complex | Core components | Sensitive to acute rapamycin |
|---|---|---|
| mTORC1 | mTOR, Raptor, mLST8, PRAS40, DEPTOR | Yes |
| mTORC2 | mTOR, Rictor, mLST8, mSIN1, Protor | No (chronic exposure can affect it) |

That difference is the source of much confusion in the literature: an experiment
using acute rapamycin tests mTORC1, not mTOR as a whole.

## What mTORC1 does

mTORC1 is a growth switch. When amino acids, energy and growth factors are
available, it promotes translation, lipid synthesis and nucleotide synthesis, and
inhibits catabolic processes. Two consequences matter for aging biology:

- It **inhibits [[mechanisms/autophagy|autophagy]]** through ULK1 phosphorylation.
- It **promotes cell growth** through S6K1 and 4E-BP1.

mTORC1 is inhibited by rapamycin, by amino-acid restriction (particularly
leucine), by energy stress via AMPK, and by hypoxia through REDD1.

## What mTORC2 does

mTORC2 phosphorylates Akt at Ser473, among other substrates, and thereby shapes
insulin signalling and cytoskeletal organisation. Because Akt is pro-growth, the
net effect of inhibiting both complexes is not simply "less growth" — the two
arms partly oppose each other, which is one reason chronic rapamycin has
different effects from genetic mTOR reduction.

## Why it matters for aging

Reduced mTOR signalling extends lifespan in yeast, worms, flies and mice. In
mice, the effect has been shown pharmacologically with
[[compounds/rapamycin|rapamycin]] and genetically with mTOR or S6K1 deletion, and
it is one of the most replicated pharmacological lifespan effects in a mammal.

The mechanism is not settled. Proposed contributors include increased autophagy,
reduced translation and protein synthesis, altered immune function and reduced
senescent-cell signalling. These are not mutually exclusive, and separating them
in vivo is difficult because mTORC1 sits upstream of all of them.

## Open questions

- How much of the lifespan effect is mTORC1 versus mTORC2?
- Is there a dose or schedule that preserves immune benefit without the metabolic
  side effects of chronic inhibition?
- Do the effects in mice translate to humans, and on what endpoint could that be
  tested?
