<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CitiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cities = [
            [
                'name' => 'Lahore',
                'status' => true,
            ],
            [
                'name' => 'Karachi',
                'status' => true,
            ],
            [
                'name' => 'Islamabad',
                'status' => true,
            ],
            [
                'name' => 'Faisalabad',
                'status' => true,
            ],
            [
                'name' => 'Multan',
                'status' => true,
            ],
            [
                'name' => 'Rawalpindi',
                'status' => true,
            ],
            [
                'name' => 'Sialkot',
                'status' => true,
            ],
            [
                'name' => 'Gujranwala',
                'status' => true,
            ],
            [
                'name' => 'Bahawalpur',
                'status' => true,
            ],
            [
                'name' => 'Sargodha',
                'status' => true,
            ],
            [
                'name' => 'Sukkur',
                'status' => true,
            ],
            [
                'name' => 'Hyderabad',
                'status' => true,
            ],
            [
                'name' => 'Quetta',
                'status' => true,
            ],
            [
                'name' => 'Peshawar',
                'status' => true,
            ],
            [
                'name' => 'Abbottabad',
                'status' => true,
            ],
            [
                'name' => 'Mirpur Khas',
                'status' => true,
            ],
            [
                'name' => 'Dera Ghazi Khan',
                'status' => true,
            ],
            [
                'name' => 'Sahiwal',
                'status' => true,
            ],
            [
                'name' => 'Jhang',
                'status' => true,
            ],
            [
                'name' => 'Mardan',
                'status' => true,
            ],
            [
                'name' => 'Rahim Yar Khan',
                'status' => true,
            ],
            [
                'name' => 'Sialkot',
                'status' => true,
            ],
            [
                'name' => 'Gujranwala',
                'status' => true,
            ],
            [
                'name' => 'Bahawalpur',
                'status' => true,
            ],
            [
                'name' => 'Sargodha',
                'status' => true,
            ],
            [
                'name' => 'Sukkur',
                'status' => true,
            ],
            [
                'name' => 'Hyderabad',
                'status' => true,
            ],
            [
                'name' => 'Quetta',
                'status' => true,
            ],
            [
                'name' => 'Peshawar',
                'status' => true,
            ],
            [
                'name' => 'Abbottabad',
                'status' => true,
            ],
            [
                'name' => 'Mirpur Khas',
                'status' => true,
            ],
            [
                'name' => 'Dera Ghazi Khan',
                'status' => true,
            ],
            [
                'name' => 'Sahiwal',
                'status' => true,
            ],
            [
                'name' => 'Jhang',
                'status' => true,
            ],
            [
                'name' => 'Mardan',
                'status' => true,
            ],
            [
                'name' => 'Rahim Yar Khan',
                'status' => true,
            ],
            [
                'name' => 'Sargodha',
                'status' => true,
            ],
            [
                'name' => 'Sukkur',
                'status' => true,
            ],
            [
                'name' => 'Hyderabad',
                'status' => true,
            ],
            [
                'name' => 'Quetta',
                'status' => true,
            ],
            [
                'name' => 'Peshawar',
                'status' => true,
            ],
            [
                'name' => 'Abbottabad',
                'status' => true,
            ],
            [
                'name' => 'Mirpur Khas',
                'status' => true,
            ],
            [
                'name' => 'Dera Ghazi Khan',
                'status' => true,
            ],
            [
                'name' => 'Sahiwal',
                'status' => true,
            ],
            [
                'name' => 'Jhang',
                'status' => true,
            ],
            [
                'name' => 'Mardan',
                'status' => true,
            ],
            [
                'name' => 'Rahim Yar Khan',
                'status' => true,
            ]
        ];

        foreach ($cities as $city) {
            City::firstOrCreate(
                ['name' => $city['name']],
                ['status' => $city['status']]
            );
        }
    }
}
