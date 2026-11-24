---
id: gene:foxo3
type: gene
title: FOXO3
summary: FOXO3 encodes a forkhead transcription factor that coordinates stress resistance, apoptosis and autophagy. It is the most consistently replicated genetic association with human longevity.
status: published
locale: en
topics:
  - genetics
tags:
  - forkhead
  - longevity
  - stress-resistance
aliases:
  - FOXO3A
  - FKHRL1
identifiers:
  hgnc: HGNC:3821
  uniprot: O43524
created: 2026-11-20
updated: 2026-11-24
license: CC-BY-SA-4.0
review:
  level: community
relations:
  - type: related_to
    target: concept:longevity
  - type: related_to
    target: mechanism:autophagy
  - type: related_to
    target: protein:mtor
sources:
  - citation: Willcox BJ, Donlon TA, He Q, et al. FOXO3A genotype is strongly associated with human longevity. PNAS. 2008;105(37):13987–13992.
    doi: 10.1073/pnas.0801030105
    url: https://doi.org/10.1073/pnas.0801030105
---

## The gene

FOXO3 (forkhead box O3) encodes a member of the forkhead family of
transcription factors. It is one of four FOXO genes in mammals — FOXO1, FOXO3,
FOXO4 and FOXO6 — which share a DNA-binding domain and overlapping target sets.

The protein is regulated mainly by localisation rather than by abundance. In the
presence of insulin and IGF-1 signalling, Akt phosphorylates FOXO3 and excludes
it from the nucleus. When that signalling falls — during nutrient restriction or
stress — FOXO3 enters the nucleus and activates its targets.

## What it does

Nuclear FOXO3 activates genes involved in:

- **Stress resistance** — manganese superoxide dismutase and other antioxidant
  enzymes.
- **Apoptosis** — through Bim and Puma, in cells too damaged to repair.
- **Autophagy** — through direct transcriptional activation of autophagy genes
  and through interaction with the autophagy machinery.
- **Cell-cycle arrest** — through p27^KIP1^.

This places FOXO3 downstream of the same insulin/IGF-1 pathway that carries the
longest-established lifespan effects in worms, flies and mice, where reducing
pathway activity extends lifespan. The human interest follows from that
conservation.

## Human longevity

In 2008, a variant in the FOXO3 locus was associated with longevity in
long-lived men of Japanese ancestry in Hawaii, and the association has been
replicated in several independent cohorts of different ancestries. The effect
sizes are modest: this is a common variant shifting the odds of reaching
extreme old age, not a determinant of lifespan.

Two cautions apply. First, association studies of longevity are vulnerable to
population stratification and to the choice of control group — "young controls"
and "population controls" answer different questions. Second, replication across
cohorts has been uneven, which is typical of small-effect common variants.

## Relationship to mTOR

The insulin/IGF-1 axis that regulates FOXO3 and the
[[proteins/mtor|mTOR]] axis are distinct but connected: Akt, which inhibits
FOXO3, is itself a substrate of mTORC2. Reducing TOR signalling therefore does
not simply "activate FOXO3" — the network has feedback loops, and the direction
of the net effect depends on tissue and duration.
