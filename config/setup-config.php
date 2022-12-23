<?php

return [
    "roles_permission" => [
        [
            "role" => "superadmin",
            "permissions" => [
       "user_create",
       "user_update",
       "user_view",
       "user_delete",
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
        "customer",

    ],
    "permissions" => [
       "user_create",
       "user_update",
       "user_view",
       "user_delete",

      "create_garages",
      "view_garages",
    ],

];
