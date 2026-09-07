<?php

/**
 * Editable planning defaults for the Canadian baby-cost calculator.
 * These are not a claim that "a baby costs $X".
 */
return [
    'last_reviewed' => '2026-09-07',
    'ccb' => [
        'period_label' => 'July 2026 to June 2027',
        'under_6_annual_maximum' => '8157.00',
        'under_6_monthly_maximum' => '679.75',
        'note' => 'Maximum Canada Child Benefit for a child under 6. Actual payment depends on adjusted family net income and family circumstances.',
    ],
    'startup' => [
        ['id' => 'crib', 'category' => 'Sleeping', 'label' => 'Crib or bassinet', 'planned' => '400'],
        ['id' => 'mattress', 'category' => 'Sleeping', 'label' => 'Mattress', 'planned' => '150'],
        ['id' => 'sheets', 'category' => 'Sleeping', 'label' => 'Sheets and sleep sacks', 'planned' => '80'],
        ['id' => 'monitor', 'category' => 'Sleeping', 'label' => 'Baby monitor', 'planned' => '120'],
        ['id' => 'bottles', 'category' => 'Feeding', 'label' => 'Bottles and sterilizing', 'planned' => '60'],
        ['id' => 'pump', 'category' => 'Feeding', 'label' => 'Breast pump', 'planned' => '200'],
        ['id' => 'nursing', 'category' => 'Feeding', 'label' => 'Nursing supplies', 'planned' => '80'],
        ['id' => 'formula', 'category' => 'Feeding', 'label' => 'Initial formula supply', 'planned' => '80'],
        ['id' => 'diapers', 'category' => 'Diapering', 'label' => 'Starter diapers', 'planned' => '70'],
        ['id' => 'wipes', 'category' => 'Diapering', 'label' => 'Wipes', 'planned' => '25'],
        ['id' => 'changing', 'category' => 'Diapering', 'label' => 'Changing station', 'planned' => '150'],
        ['id' => 'newborn_clothes', 'category' => 'Clothing', 'label' => 'Newborn clothing', 'planned' => '80'],
        ['id' => 'size_03', 'category' => 'Clothing', 'label' => '0–3 month clothing', 'planned' => '80'],
        ['id' => 'size_36', 'category' => 'Clothing', 'label' => '3–6 month clothing', 'planned' => '80'],
        ['id' => 'seasonal', 'category' => 'Clothing', 'label' => 'Seasonal clothing', 'planned' => '60'],
        ['id' => 'car_seat', 'category' => 'Transportation', 'label' => 'Car seat', 'planned' => '250'],
        ['id' => 'stroller', 'category' => 'Transportation', 'label' => 'Stroller', 'planned' => '300'],
        ['id' => 'carrier', 'category' => 'Transportation', 'label' => 'Carrier', 'planned' => '80'],
        ['id' => 'thermometer', 'category' => 'Health / care', 'label' => 'Thermometer', 'planned' => '25'],
        ['id' => 'grooming', 'category' => 'Health / care', 'label' => 'Grooming kit', 'planned' => '20'],
        ['id' => 'bath', 'category' => 'Health / care', 'label' => 'Bath supplies', 'planned' => '40'],
        ['id' => 'first_aid', 'category' => 'Health / care', 'label' => 'Medications and first aid', 'planned' => '40'],
        ['id' => 'furniture', 'category' => 'Nursery', 'label' => 'Furniture', 'planned' => '250'],
        ['id' => 'decor', 'category' => 'Nursery', 'label' => 'Decor', 'planned' => '50'],
        ['id' => 'storage', 'category' => 'Nursery', 'label' => 'Storage', 'planned' => '60'],
        ['id' => 'other', 'category' => 'Other', 'label' => 'Other custom items', 'planned' => '0'],
    ],
    'recurring' => [
        ['id' => 'diapers', 'label' => 'Diapers', 'monthly' => '80'],
        ['id' => 'wipes', 'label' => 'Wipes', 'monthly' => '20'],
        ['id' => 'feeding', 'label' => 'Formula / feeding', 'monthly' => '0'],
        ['id' => 'clothing', 'label' => 'Clothing replacements', 'monthly' => '25'],
        ['id' => 'toiletries', 'label' => 'Toiletries', 'monthly' => '15'],
        ['id' => 'health', 'label' => 'Medication / health supplies', 'monthly' => '10'],
        ['id' => 'toys', 'label' => 'Toys and books', 'monthly' => '15'],
        ['id' => 'subscriptions', 'label' => 'Subscriptions / memberships', 'monthly' => '0'],
        ['id' => 'misc', 'label' => 'Miscellaneous', 'monthly' => '20'],
    ],
    'disclaimer' => 'Baby expenses vary substantially by household. Defaults are planning estimates and can be edited. This is not a claim that a baby costs a fixed amount in Canada.',
];
