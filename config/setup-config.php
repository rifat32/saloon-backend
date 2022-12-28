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

       "role_create",
       "role_update",
       "role_view",
       "role_delete",

       "garage_create",
       "garage_update",
       "garage_view",
       "garage_delete",

       "automobile_create",
       "automobile_update",
       "automobile_view",
       "automobile_delete",


            ],
        ],
        [
            "role" => "data_collector",
            "permissions" => [
                "garage_create",
                "garage_update",
                "garage_view",
                "garage_delete",
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


       "role_create",
       "role_update",
       "role_view",
       "role_delete",

       "garage_create",
       "garage_update",
       "garage_view",
       "garage_delete",

       "automobile_create",
       "automobile_update",
       "automobile_view",
       "automobile_delete",

    ],

];
