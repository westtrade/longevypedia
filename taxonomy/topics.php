<?php

declare(strict_types=1);

/**
 * Каталог тематик Longevypedia.
 *
 * Слаг тематики — то, что попадает в `topics` Frontmatter документа. Список
 * живёт в PHP, а не в базе: тематик десятки, они меняются вместе с контентом
 * и должны проходить ревью в том же Pull Request.
 *
 * Тематика, которой здесь нет, но которая используется в документе, не
 * теряется — она попадёт в каталог с подписью-слагом и будет видна как
 * недоработка таксономии.
 */
return [
    'aging-mechanisms' => [
        'label' => 'Aging mechanisms',
        'description' => 'Molecular and cellular processes that drive biological aging.',
    ],
    'interventions' => [
        'label' => 'Interventions',
        'description' => 'Pharmacological, dietary and lifestyle approaches studied for their effect on healthspan.',
    ],
    'biomarkers' => [
        'label' => 'Biomarkers',
        'description' => 'Measurable indicators used to estimate biological age and aging trajectories.',
    ],
    'genetics' => [
        'label' => 'Genetics',
        'description' => 'Genes, variants and inheritance patterns associated with lifespan and healthspan.',
    ],
    'longevity-populations' => [
        'label' => 'Longevity populations',
        'description' => 'Human cohorts, centenarian studies and epidemiological observations.',
    ],
    'research-methods' => [
        'label' => 'Research methods',
        'description' => 'Study designs, model organisms and statistical approaches used in aging research.',
    ],
    'clinical-practice' => [
        'label' => 'Clinical practice',
        'description' => 'Guidelines, screening and clinical translation of aging research.',
    ],
];
