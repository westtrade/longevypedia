---
id: claim:rapamycin-extends-lifespan-in-mice
type: claim
title: Rapamycin extends lifespan in mice
summary: Dietary rapamycin increases median and maximum lifespan in genetically heterogeneous mice, including when treatment starts late in adult life. The claim is about mice and does not extend to humans.
status: published
locale: en
topics:
  - interventions
  - research-methods
tags:
  - rapamycin
  - lifespan
  - evidence
aliases:
  - Rapamycin longevity claim
created: 2026-11-20
updated: 2026-11-24
license: CC-BY-SA-4.0
review:
  level: community
claim:
  statement: In genetically heterogeneous mice, dietary rapamycin started in mid or late adult life increases median and maximum lifespan in both sexes.
  confidence: likely
  evidence_direction: supports
  scope: Mice only. The evidence does not establish a lifespan or healthspan effect in humans, nor an optimal dose or schedule.
  as_of: 2026-11-20
relations:
  - type: supported_by
    target: research:harrison-2009-rapamycin-mice
  - type: related_to
    target: compound:rapamycin
  - type: related_to
    target: protein:mtor
sources:
  - citation: Harrison DE, Strong R, Sharp ZD, et al. Rapamycin fed late in life extends lifespan in genetically heterogeneous mice. Nature. 2009;460:392–395.
    doi: 10.1038/nature08221
    url: https://doi.org/10.1038/nature08221
    pmid: "19587680"
---

## The proposition

In genetically heterogeneous mice, dietary rapamycin started in mid or late
adult life increases median and maximum lifespan in both sexes.

The statement is deliberately narrow. It names the species, the intervention, the
timing and the outcome, so that evidence for or against it can be pointed at
directly.

## Evidence in favour

- [[research/harrison-2009-rapamycin-mice|Harrison et al., 2009]] — three-site
  intervention in UM-HET3 mice, effect in both sexes and in the late-start
  cohort.
- Subsequent dose–response work in the same mouse population found lifespan
  effects across a range of doses, with the largest effects not at the highest
  dose.

## Evidence against or complicating

- The effect is not universal across genotypes and husbandry conditions; mouse
  lifespan studies are sensitive to both.
- Chronic mTORC1 inhibition causes glucose intolerance and immune suppression in
  mice and humans, so an unconditional "rapamycin is good for lifespan" reading
  is not supported by the same data.

## What this claim does not say

It does not say that rapamycin extends human lifespan. It does not say that
rapamycin is safe for long-term use in healthy people. It does not identify the
mechanism — reduced translation, increased
[[mechanisms/autophagy|autophagy]] and altered
[[mechanisms/cellular-senescence|senescent-cell]] signalling are all candidates,
and the mouse data cannot separate them.

## How confidence was set

`likely` rather than `established`: the finding replicates across independent
laboratories in mice, but it has not been demonstrated in a second species with
the same rigour, and the human evidence is indirect. `established` is reserved
for findings that would survive a textbook revision.
