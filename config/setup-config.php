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

       "fuel_station_service_create",
       "fuel_station_service_update",
       "fuel_station_service_view",
       "fuel_station_service_delete",

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

       "questions_create",
       "questions_update",
       "questions_view",
       "questions_delete",

       "review_create",
       "review_update",
       "review_view",
       "review_delete",


// this permission may remove later
       "garage_affiliation_create",
       "garage_affiliation_update",
       "garage_affiliation_view",
       "garage_affiliation_delete",
// end this permission may remove later

"garage_gallery_create",
"garage_gallery_view",
"garage_gallery_delete",

"fuel_station_gallery_create",
"fuel_station_gallery_view",
"fuel_station_gallery_delete",




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

                "fuel_station_service_create",
                "fuel_station_service_update",
                "fuel_station_service_view",
                "fuel_station_service_delete",


                "affiliation_create",
                "affiliation_update",
                "affiliation_view",
                "affiliation_delete",


                "garage_affiliation_create",
                "garage_affiliation_update",
                "garage_affiliation_view",
                "garage_affiliation_delete",

"garage_gallery_create",
"garage_gallery_view",
"garage_gallery_delete",

"fuel_station_gallery_create",
"fuel_station_gallery_view",
"fuel_station_gallery_delete",

            ],
        ],
        [
            "role" => "garage_owner",
            "permissions" => [

       "garage_times_update",
       "garage_times_view",

       "garage_rules_update",
       "garage_rules_view",

       "garage_gallery_create",
       "garage_gallery_view",
       "garage_gallery_delete",

       "garage_services_create",
       "garage_services_update",
       "garage_services_view",
       "garage_services_delete",

       "garage_automobile_create",
       "garage_automobile_update",
       "garage_automobile_view",
       "garage_automobile_delete",


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



       "garage_service_price_create",
       "garage_service_price_update",
       "garage_service_price_view",
       "garage_service_price_delete",


       "garage_package_create",
       "garage_package_update",
       "garage_package_view",
       "garage_package_delete",

       "job_bids_create",
       "job_bids_update",
       "job_bids_view",
       "job_bids_delete",




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

       "fuel_station_service_create",
       "fuel_station_service_update",
       "fuel_station_service_view",
       "fuel_station_service_delete",


       "template_create",
       "template_update",
       "template_view",
       "template_delete",



       "garage_times_update",
       "garage_times_view",

       "garage_rules_update",
       "garage_rules_view",


       "garage_gallery_create",
       "garage_gallery_view",
       "garage_gallery_delete",

"fuel_station_gallery_create",
"fuel_station_gallery_view",
"fuel_station_gallery_delete",

       "garage_services_create",
       "garage_services_update",
       "garage_services_view",
       "garage_services_delete",





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



       "garage_service_price_create",
       "garage_service_price_update",
       "garage_service_price_view",
       "garage_service_price_delete",

       "garage_package_create",
       "garage_package_update",
       "garage_package_view",
       "garage_package_delete",


       "job_bids_create",
       "job_bids_update",
       "job_bids_view",
       "job_bids_delete",

       "questions_create",
       "questions_update",
       "questions_view",
       "questions_delete",

       "review_create",
       "review_update",
       "review_view",
       "review_delete",

       "garage_automobile_create",
       "garage_automobile_update",
       "garage_automobile_view",
       "garage_automobile_delete",

    ],

    "user_image_location" => "user_image",
    "garage_gallery_location" => "garage_gallery",
    "fuel_station_gallery_location" => "fuel_station_gallery",

    "affiliation_logo_location" => "affiliation_logo"

];
