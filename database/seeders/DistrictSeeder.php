<?php

namespace Database\Seeders;

use App\Models\District;
use App\Models\Region;
use Illuminate\Database\Seeder;

class DistrictSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $districtsByRegion = [
            'Arusha' => [
                'Arusha City',
                'Arusha District',
                'Karatu',
                'Longido',
                'Meru',
                'Monduli',
                'Ngorongoro',
            ],
            'Dar es Salaam' => [
                'Ilala',
                'Kinondoni',
                'Kigamboni',
                'Temeke',
                'Ubungo',
            ],
            'Dodoma' => [
                'Bahi',
                'Chamwino',
                'Chemba',
                'Dodoma City',
                'Kondoa',
                'Kongwa',
                'Mpwapwa',
            ],
            'Geita' => [
                'Bukombe',
                'Chato',
                'Geita',
                'Geita Town',
                'Mbogwe',
                "Nyang'hwale",
            ],
            'Iringa' => [
                'Iringa District',
                'Iringa Municipal',
                'Kilolo',
                'Mufindi',
            ],
            'Kagera' => [
                'Biharamulo',
                'Bukoba District',
                'Bukoba Municipal',
                'Karagwe',
                'Kyerwa',
                'Missenyi',
                'Muleba',
                'Ngara',
            ],
            'Katavi' => [
                'Mlele',
                'Mpanda District',
                'Mpanda Municipal',
                'Tanganyika',
            ],
            'Kigoma' => [
                'Buhigwe',
                'Kasulu District',
                'Kasulu Town',
                'Kibondo',
                'Kigoma District',
                'Kigoma-Ujiji Municipal',
                'Kakonko',
                'Uvinza',
            ],
            'Kilimanjaro' => [
                'Hai',
                'Moshi District',
                'Moshi Municipal',
                'Mwanga',
                'Rombo',
                'Same',
                'Siha',
            ],
            'Lindi' => [
                'Kilwa',
                'Liwale',
                'Lindi District',
                'Lindi Municipal',
                'Nachingwea',
                'Ruangwa',
            ],
            'Manyara' => [
                'Babati District',
                'Babati Town',
                'Hanang',
                'Kiteto',
                'Mbulu',
                'Simanjiro',
            ],
            'Mara' => [
                'Bunda District',
                'Bunda Town',
                'Butiama',
                'Musoma District',
                'Musoma Municipal',
                'Rorya',
                'Serengeti',
                'Tarime District',
                'Tarime Town',
            ],
            'Mbeya' => [
                'Busokelo',
                'Chunya',
                'Kyela',
                'Mbeya City',
                'Mbeya District',
                'Mbarali',
                'Rungwe',
            ],
            'Morogoro' => [
                'Gairo',
                'Ifakara Town',
                'Kilombero',
                'Kilosa',
                'Malinyi',
                'Morogoro District',
                'Morogoro Municipal',
                'Mvomero',
                'Ulanga',
            ],
            'Mtwara' => [
                'Masasi District',
                'Masasi Town',
                'Mtwara District',
                'Mtwara Municipal',
                'Nanyumbu',
                'Newala District',
                'Newala Town',
                'Tandahimba',
            ],
            'Mwanza' => [
                'Ilemela',
                'Kwimba',
                'Magu',
                'Misungwi',
                'Nyamagana',
                'Sengerema',
                'Ukerewe',
            ],
            'Njombe' => [
                'Ludewa',
                'Makambako Town',
                'Makete',
                'Njombe District',
                'Njombe Town',
                "Wanging'ombe",
            ],
            'Pwani (Coast)' => [
                'Bagamoyo',
                'Kibaha District',
                'Kibaha Town',
                'Kisarawe',
                'Mafia',
                'Mkuranga',
                'Rufiji',
            ],
            'Rukwa' => [
                'Kalambo',
                'Nkasi',
                'Sumbawanga District',
                'Sumbawanga Municipal',
            ],
            'Ruvuma' => [
                'Mbinga District',
                'Mbinga Town',
                'Namtumbo',
                'Nyasa',
                'Songea District',
                'Songea Municipal',
                'Tunduru',
                'Madaba',
            ],
            'Shinyanga' => [
                'Kahama District',
                'Kahama Municipal',
                'Kishapu',
                'Shinyanga District',
                'Shinyanga Municipal',
                'Ushetu',
            ],
            'Simiyu' => [
                'Bariadi District',
                'Bariadi Town',
                'Busega',
                'Itilima',
                'Maswa',
                'Meatu',
            ],
            'Singida' => [
                'Ikungi',
                'Iramba',
                'Manyoni',
                'Mkalama',
                'Singida District',
                'Singida Municipal',
            ],
            'Songwe' => [
                'Ileje',
                'Mbozi',
                'Momba',
                'Songwe',
                'Tunduma Town',
            ],
            'Tabora' => [
                'Igunga',
                'Kaliua',
                'Nzega District',
                'Nzega Town',
                'Sikonge',
                'Tabora Municipal',
                'Uyui',
                'Urambo',
            ],
            'Tanga' => [
                'Bumbuli',
                'Handeni District',
                'Handeni Town',
                'Kilindi',
                'Korogwe District',
                'Korogwe Town',
                'Lushoto',
                'Muheza',
                'Mkinga',
                'Pangani',
                'Tanga City',
            ],
        ];

        foreach ($districtsByRegion as $regionName => $districtNames) {
            $region = Region::query()->where('name', $regionName)->first();

            if (! $region) {
                continue;
            }

            foreach ($districtNames as $districtName) {
                District::updateOrCreate(
                    [
                        'region_id' => $region->id,
                        'name' => $districtName,
                    ],
                    [
                        'region_id' => $region->id,
                        'name' => $districtName,
                    ]
                );
            }
        }
    }
}
