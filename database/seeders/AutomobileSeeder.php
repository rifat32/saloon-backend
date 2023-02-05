<?php

namespace Database\Seeders;

use App\Models\AutomobileCategory;
use App\Models\AutomobileMake;
use App\Models\AutomobileModel;
use App\Models\AutomobileModelVariant;
use Illuminate\Database\Seeder;
use File;
use Illuminate\Support\Facades\DB;

class AutomobileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        AutomobileCategory::truncate();
        AutomobileMake::truncate();
DB::statement('SET FOREIGN_KEY_CHECKS=1;');

  $automobile_category_car =  AutomobileCategory::create([
    "name" => "car"
]);

        $car_json = File::get("database/data/automobiles_car_data.json");
        $automobiles_car_data = json_decode($car_json);
        foreach ($automobiles_car_data as $dummy_make_key => $values) {
            // $automobile_category_car->makes()->create();
            // error_log(json_encode($key));
            foreach($values as $make_key=>$make_values) {

          $make =    AutomobileMake::create([
                    "name" => $make_key,
                    "automobile_category_id" =>  $automobile_category_car->id
                ]);

                error_log(json_encode(($make_key . ".............")));

                foreach($make_values as $dummy_model_keys=>$dummy_model_values) {
                    // error_log(json_encode($model_keys));
                    foreach($dummy_model_values as $model_keys=>$model_values) {
                        error_log(json_encode(("*****" .$model_keys)));

                        $model = AutomobileModel::create([
                            "name" => $model_keys,
                            "automobile_make_id" =>  $make->id
                        ]);

                        foreach($model_values as $dummy_model_variant_keys=>$dummy_model_variant_values) {
                            $model_variant = AutomobileModelVariant::create([
                                "name" => $dummy_model_variant_values,
                                "automobile_model_id" =>  $model->id
                            ]);
                            error_log(json_encode(($dummy_model_variant_values. "#######")));

                        }

                    }

                }

            }

            // Country::create([
            //     "name" => $value->name,
            //     "code" => $value->code
            // ]);
        }
    }
}
