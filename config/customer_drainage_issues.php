<?php

/**
 * Six customer report problem types (rproblem flow) used for nearby GIS alerts.
 */
return [

    'radius_km' => 5.0,

    'types' => [
        'Damaged pipelines',
        'Clogged Drains',
        'Street Pooling',
        'Manhole issues',
        'Sewage overflow',
        'Odor complaint',
    ],

    /**
     * Map free-text problem_type / issue_type values to a canonical label.
     */
    'matchers' => [
        'Damaged pipelines' => ['damaged pipeline', 'damaged pipelines', 'pipe', 'pipeline', 'cracked', 'broken pipe', 'collapsed'],
        'Clogged Drains' => ['clogged drain', 'clogged drains', 'blockage', 'blocked', 'debris', 'tersumbat'],
        'Street Pooling' => ['street pooling', 'pooling', 'standing water', 'flooding', 'banjir', 'flood'],
        'Manhole issues' => ['manhole', 'manholes', 'cover', 'missing cover'],
        'Sewage overflow' => ['sewage overflow', 'sewage', 'overflow', 'backflow', 'limpasan'],
        'Odor complaint' => ['odor', 'odour', 'smell', 'bau', 'foul'],
    ],

    'active_statuses' => [
        'pending',
        'under_review',
        'in_progress',
        'on_site',
        'on_the_way',
        'assigned',
        'new',
    ],

];
