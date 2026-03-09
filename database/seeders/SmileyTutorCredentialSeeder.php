<?php

namespace Database\Seeders;

use App\Models\SmileyTutorCredential;
use Illuminate\Database\Seeder;

class SmileyTutorCredentialSeeder extends Seeder
{
    /**
     * Seed SmileyTutor credentials (username, password) from credentials.md or default set.
     */
    public function run(): void
    {
        $pairs = $this->getCredentialPairs();

        foreach ($pairs as [$username, $password]) {
            SmileyTutorCredential::firstOrCreate(
                ['username' => $username, 'password' => $password],
                ['username' => $username, 'password' => $password]
            );
        }
    }

    /**
     * @return array<int, array{0: string, 1: string}>
     */
    private function getCredentialPairs(): array
    {
        $path = base_path('credentials.md');

        if (is_file($path)) {
            $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $pairs = [];
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '') {
                    continue;
                }
                $parts = preg_split('/\s+/', $line, 2);
                if (count($parts) >= 2) {
                    $pairs[] = [$parts[0], $parts[1]];
                }
            }

            if ($pairs !== []) {
                return $pairs;
            }
        }

        // Default 50 credentials (same as credentials.md content)
        $defaults = [
            ['mwanza', 'mwanza000rrqx'], ['mwanza', 'mwanza001eqzv'], ['mwanza', 'mwanza002eyit'],
            ['mwanza', 'mwanza003mrqy'], ['mwanza', 'mwanza004qnxb'], ['mwanza', 'mwanza005fwyy'],
            ['mwanza', 'mwanza006vwsu'], ['mwanza', 'mwanza007gbey'], ['mwanza', 'mwanza008hatp'],
            ['mwanza', 'mwanza009vtgr'], ['mwanza', 'mwanza010pjnu'], ['mwanza', 'mwanza011hsca'],
            ['mwanza', 'mwanza012ugfw'], ['mwanza', 'mwanza013bdhc'], ['mwanza', 'mwanza014iwin'],
            ['mwanza', 'mwanza015fvdp'], ['mwanza', 'mwanza016guen'], ['mwanza', 'mwanza017ztgd'],
            ['mwanza', 'mwanza018fsxm'], ['mwanza', 'mwanza019bxnn'], ['mwanza', 'mwanza020fsrh'],
            ['mwanza', 'mwanza021kdwm'], ['mwanza', 'mwanza022uhcb'], ['mwanza', 'mwanza023hprf'],
            ['mwanza', 'mwanza024jsei'], ['mwanza', 'mwanza025xdrs'], ['mwanza', 'mwanza026baqd'],
            ['mwanza', 'mwanza027gdif'], ['mwanza', 'mwanza028fiij'], ['mwanza', 'mwanza029heqe'],
            ['mwanza', 'mwanza030bxrv'], ['mwanza', 'mwanza031scda'], ['mwanza', 'mwanza032uvpn'],
            ['mwanza', 'mwanza033wspr'], ['mwanza', 'mwanza034cryk'], ['mwanza', 'mwanza035jwun'],
            ['mwanza', 'mwanza036nhzw'], ['mwanza', 'mwanza037uhdb'], ['mwanza', 'mwanza038bquh'],
            ['mwanza', 'mwanza039gywu'], ['mwanza', 'mwanza040pfqt'], ['mwanza', 'mwanza041hacz'],
            ['mwanza', 'mwanza042tjyc'], ['mwanza', 'mwanza043gkqp'], ['mwanza', 'mwanza044gskc'],
            ['mwanza', 'mwanza045gsfy'], ['mwanza', 'mwanza046frjb'], ['mwanza', 'mwanza047ycpg'],
            ['mwanza', 'mwanza048kgjs'], ['mwanza', 'mwanza049kzfz'],
        ];

        return $defaults;
    }
}
