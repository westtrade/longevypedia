---
id: compound:rapamycin
type: compound
title: Rapamycin
summary: Rapamycin is a macrolide that inhibits mTORC1. It is the most replicated pharmacological lifespan extender in mice and an approved immunosuppressant in humans, which makes it the test case for translating aging research.
status: published
locale: en
topics:
  - interventions
tags:
  - sirolimus
  - mTOR
  - immunosuppression
  - lifespan
aliases:
  - Sirolimus
  - Rapamune
identifiers:
  mesh: D020123
created: 2026-11-24
updated: 2026-11-24
license: CC-BY-SA-4.0
review:
  level: community
relations:
  - type: targets
    target: protein:mtor
  - type: related_to
    target: mechanism:autophagy
sources:
  - citation: Harrison DE, Strong R, Sharp ZD, et al. Rapamycin fed late in life extends lifespan in genetically heterogeneous mice. Nature. 2009;460:392–395.
    doi: 10.1038/nature08221
    url: https://doi.org/10.1038/nature08221
    pmid: "19587680"
  - citation: Mannick JB, Del Giudice G, Lattanzi M, et al. mTOR inhibition improves immune function in the elderly. Sci Transl Med. 2014;6(268):268ra179.
    doi: 10.1126/scitranslmed.3009892
    url: https://doi.org/10.1126/scitranslmed.3009892
---

## Chemistry and origin

Rapamycin is a macrolide lactone produced by *Streptomyces hygroscopicus*, first
isolated in 1975 from a soil sample collected on Rapa Nui (Easter Island) — hence
the name. It is the parent compound of the mTOR-inhibitor class; everolimus and
temsirolimus are derivatives with modified pharmacokinetics.

## Mechanism

Rapamycin binds the immunophilin FKBP12, and the resulting complex binds
[[proteins/mtor|mTOR]] at the FRB domain. This does not inhibit the kinase
catalytically. Instead it weakens the interaction between mTOR and Raptor, which
disrupts mTORC1 assembly and reduces phosphorylation of its substrates.

Consequences relevant to aging biology:

- Reduced S6K1 and 4E-BP1 phosphorylation, so less cap-dependent translation.
- Increased [[mechanisms/autophagy|autophagy]], since mTORC1 normally inhibits
  ULK1.
- Partial inhibition of the senescence-associated secretory programme.

mTORC2 is largely insensitive to acute rapamycin, though prolonged exposure can
impair its assembly in some cell types.

## Clinical use

Rapamycin is an approved medicine, not an experimental supplement. Its
established indications include:

- Prevention of organ transplant rejection (as an immunosuppressant).
- Coating of drug-eluting coronary stents.
- Treatment of lymphangioleiomyomatosis, a rare lung disease driven by mTOR
  activation.

Typical adverse effects are hyperglycaemia, hyperlipidaemia, oral ulceration,
impaired wound healing, pneumonitis and increased infection risk — the last being
the direct consequence of the mechanism that makes it useful in transplantation.

## Evidence in aging

In genetically heterogeneous mice, rapamycin added to the diet extended median
and maximum lifespan in both sexes, and the effect was present even when
treatment began late in adult life. This is reported in
[[research/harrison-2009-rapamycin-mice|Harrison et al., 2009]] and underlies the
claim that
[[claims/rapamycin-extends-lifespan-in-mice|rapamycin extends lifespan in mice]].

In humans, the relevant evidence is indirect. The mTOR inhibitor everolimus
improved the response to influenza vaccination in older adults, which is a
measure of immune function rather than of aging rate. No trial has shown that any
mTOR inhibitor slows biological aging or extends lifespan in healthy people, and
the immunosuppressive effect is a genuine risk in exactly the population that
would be treated.

## What would change the picture

A trial in healthy older adults with a validated aging endpoint, adequate power
and a duration measured in years. Until such a trial exists, the mouse data are
the strongest evidence available, and they are data about mice.
