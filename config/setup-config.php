<?php

return [
    "roles_permission" => [
        [
            "role" => "superadmin",
            "permissions" => [
                "create_garages",
                "view_garages"
            ],
        ],
        [
            "role" => "data_collector",
            "permissions" => [
                "create_garages",
                "view_garages"
            ],
        ],


    ],
    "roles" => [
        "superadmin",
        "data_collector",
        "garage_owner",
        "shop_owner",
        "customers",

    ],
    "permissions" => [
      "create_garages",
      "view_garages",
    ],

];
