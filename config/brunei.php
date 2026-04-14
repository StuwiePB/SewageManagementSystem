<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Brunei Darussalam – Districts & Mukims (Whereabouts)
    |--------------------------------------------------------------------------
    | Official administrative divisions. Location is Brunei only.
    | Source: Administrative divisions of Brunei Darussalam
    */

    'country' => 'Brunei Darussalam',
    'country_code' => 'BN',

    /** Approximate centre of Brunei (Bandar Seri Begawan) for maps */
    'map_center' => [
        'lat' => 4.9031,
        'lng' => 114.9398,
    ],

    /** Bounding box for validation (rough Brunei bounds) */
    'bounds' => [
        'lat_min' => 4.0,
        'lat_max' => 5.2,
        'lng_min' => 114.0,
        'lng_max' => 115.5,
    ],

    /**
     * Districts (daerah) and their mukims (whereabouts).
     * Keys are slugs for storage; values are display names.
     */
    'districts' => [
        'brunei-muara' => 'Brunei-Muara',
        'belait' => 'Belait',
        'tutong' => 'Tutong',
        'temburong' => 'Temburong',
    ],

    /**
     * Mukims per district (second-level whereabouts).
     */
    'mukims' => [
        'brunei-muara' => [
            'berakas-a' => "Berakas 'A'",
            'berakas-b' => "Berakas 'B'",
            'burong-pingai-ayer' => 'Burong Pingai Ayer',
            'gadong-a' => "Gadong 'A'",
            'gadong-b' => "Gadong 'B'",
            'kianggeh' => 'Kianggeh',
            'kilanas' => 'Kilanas',
            'kota-batu' => 'Kota Batu',
            'lumapas' => 'Lumapas',
            'mentiri' => 'Mentiri',
            'pangkalan-batu' => 'Pangkalan Batu',
            'peramu' => 'Peramu',
            'saba' => 'Saba',
            'sengkurong' => 'Sengkurong',
            'serasa' => 'Serasa',
            'sungai-kebun' => 'Sungai Kebun',
            'sungai-kedayan' => 'Sungai Kedayan',
            'tamoi' => 'Tamoi',
        ],
        'belait' => [
            'bukit-sawat' => 'Bukit Sawat',
            'kuala-balai' => 'Kuala Balai',
            'kuala-belait' => 'Kuala Belait',
            'labi' => 'Labi',
            'liang' => 'Liang',
            'melilas' => 'Melilas',
            'seria' => 'Seria',
            'sukang' => 'Sukang',
        ],
        'tutong' => [
            'keriam' => 'Keriam',
            'kiudang' => 'Kiudang',
            'lamunin' => 'Lamunin',
            'pekan-tutong' => 'Pekan Tutong',
            'rambai' => 'Rambai',
            'tanjong-maya' => 'Tanjong Maya',
            'telisai' => 'Telisai',
            'ukong' => 'Ukong',
        ],
        'temburong' => [
            'amo' => 'Amo',
            'bangar' => 'Bangar',
            'batu-apoi' => 'Batu Apoi',
            'bokok' => 'Bokok',
            'labu' => 'Labu',
        ],
    ],

];
