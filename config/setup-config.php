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

       "service_create",
       "service_update",
       "service_view",
       "service_delete",

       "fuel_station_create",
       "fuel_station_update",
       "fuel_station_view",
       "fuel_station_delete",

       "template_create",
       "template_update",
       "template_view",
       "template_delete",


       "payment_type_create",
       "payment_type_update",
       "payment_type_view",
       "payment_type_delete",



       "affiliation_create",
       "affiliation_update",
       "affiliation_view",
       "affiliation_delete",




// this permission may remove later
       "garage_affiliation_create",
       "garage_affiliation_update",
       "garage_affiliation_view",
       "garage_affiliation_delete",
// end this permission may remove later


            ],
        ],
        [
            "role" => "data_collector",
            "permissions" => [
                "garage_create",
                "garage_update",
                "garage_view",
                "garage_delete",


                "fuel_station_create",
                "fuel_station_update",
                "fuel_station_view",
                "fuel_station_delete",


                "affiliation_create",
                "affiliation_update",
                "affiliation_view",
                "affiliation_delete",


                "garage_affiliation_create",
                "garage_affiliation_update",
                "garage_affiliation_view",
                "garage_affiliation_delete",

            ],
        ],
        [
            "role" => "garage_owner",
            "permissions" => [

       "garage_times_update",
       "garage_times_view",


       "garage_gallery_create",
       "garage_gallery_view",
       "garage_gallery_delete",


    //    "booking_create",
       "booking_update",
       "booking_view",
       "booking_delete",

       "job_create",
       "job_update",
       "job_view",
       "job_delete",


       "coupon_create",
       "coupon_update",
       "coupon_view",
       "coupon_delete",


       "affiliation_create",
       "affiliation_update",
       "affiliation_view",
       "affiliation_delete",


       "garage_affiliation_create",
       "garage_affiliation_update",
       "garage_affiliation_view",
       "garage_affiliation_delete",




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


       "service_create",
       "service_update",
       "service_view",
       "service_delete",

       "fuel_station_create",
       "fuel_station_update",
       "fuel_station_view",
       "fuel_station_delete",


       "template_create",
       "template_update",
       "template_view",
       "template_delete",



       "garage_times_update",
       "garage_times_view",


       "garage_gallery_create",
       "garage_gallery_view",
       "garage_gallery_delete",







       "payment_type_create",
       "payment_type_update",
       "payment_type_view",
       "payment_type_delete",



    //    "booking_create",
       "booking_update",
       "booking_view",
       "booking_delete",



       "job_create",
       "job_update",
       "job_view",
       "job_delete",



       "coupon_create",
       "coupon_update",
       "coupon_view",
       "coupon_delete",


       "affiliation_create",
       "affiliation_update",
       "affiliation_view",
       "affiliation_delete",

       "garage_affiliation_create",
       "garage_affiliation_update",
       "garage_affiliation_view",
       "garage_affiliation_delete",



    ],

    "garage_gallery_location" => "garage_gallery",
    "affiliation_logo_location" => "affiliation_logo"

];
