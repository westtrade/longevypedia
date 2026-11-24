---
id: mechanism:cellular-senescence
type: mechanism
title: Cellular senescence
summary: Cellular senescence is a stable arrest of cell division accompanied by a distinctive secretory programme. It is a tumour-suppressing mechanism that also accumulates with age and contributes to inflammation.
status: published
locale: en
topics:
  - aging-mechanisms
tags:
  - SASP
  - senolytics
  - cell-cycle
aliases:
  - Senescence
created: 2026-11-20
updated: 2026-11-24
license: CC-BY-SA-4.0
review:
  level: community
relations:
  - type: part_of
    target: concept:biological-aging
  - type: related_to
    target: proteins/mtor
  - type: related_to
    target: mechanism:autophagy
sources:
  - citation: Hayflick L, Moorhead PS. The serial cultivation of human diploid cell strains. Exp Cell Res. 1961;25:585–621.
    doi: 10.1016/0014-4827(61)90192-6
    url: https://doi.org/10.1016/0014-4827(61)90192-6
  - citation: Zhu Y, Tchkonia T, Pirtskhalava T, et al. The Achilles' heel of senescent cells - from transcriptome to senolytic drugs. Aging Cell. 2015;14(4):644–658.
    doi: 10.1111/acel.12344
    url: https://doi.org/10.1111/acel.12344
---

## Definition

Cellular senescence is a state in which a cell permanently stops dividing but
remains metabolically active. It was described by Hayflick and Moorhead in 1961
as the finite replicative capacity of human fibroblasts in culture — the
"Hayflick limit" — long before any molecular explanation existed.

Senescent cells differ from quiescent or terminally differentiated cells in
three ways:

1. **Stable arrest.** The cell does not re-enter the cycle in response to
   ordinary growth signals. The arrest is maintained by cyclin-dependent kinase
   inhibitors, chiefly p16^INK4a^ and p21^CIP1^.
2. **Resistance to apoptosis.** Senescent cells survive signals that would kill
   a normal cell, which is why they accumulate rather than clear.
3. **A secretory programme.** The senescence-associated secretory phenotype
   (SASP) releases interleukins, chemokines, matrix metalloproteinases and
   growth factors into the surrounding tissue.

The SASP is what makes senescence a tissue-level phenomenon rather than a
curiosity of cell culture. A small number of senescent cells can alter the
behaviour of their neighbours, recruit immune cells and sustain a low-grade
inflammatory state.

## Triggers

Senescence can be induced by:

- **Replicative exhaustion** — telomere shortening after repeated divisions.
- **DNA damage** — radiation, genotoxic drugs, oxidative stress.
- **Oncogene activation** — a protective response that removes potentially
  transformed cells.
- **Metabolic and mechanical stress** — including in obesity and fibrosis.

Because oncogene-induced senescence suppresses tumours, senescent cells are not
simply damage to be removed. Any therapy that eliminates them has to be judged
on cancer risk as well as on inflammation.

## Senolytics and senomorphics

Two pharmacological strategies are studied:

- **Senolytics** kill senescent cells selectively, exploiting their dependence
  on anti-apoptotic pathways. The first such drugs were identified by
  transcriptome analysis of senescent cells, with dasatinib plus quercetin as
  the best-known combination.
- **Senomorphics** suppress the SASP without killing the cell. Rapamycin and
  other [[proteins/mtor|mTOR]] inhibitors fall partly into this class, since
  mTOR activity shapes the secretory programme.

The distinction matters clinically: senolytics reduce cell number but risk
removing cells that restrain tumours; senomorphics keep the cells but blunt
their signalling.

## Open questions

Whether senescent-cell burden is a cause or a consequence of organismal aging is
still unresolved. Interventions that clear senescent cells improve several
age-related phenotypes in mice, which supports a causal contribution in those
tissues. Whether the same holds in humans, and at what dose and schedule, is not
established.
