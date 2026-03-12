<?php

namespace Database\Seeders;

use App\Models\MetricsFeature;
use Illuminate\Database\Seeder;

class MetricsFeatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $features = config('metrics.features', []);

        foreach ($features as $feature) {
            $record = MetricsFeature::firstOrNew(['key' => $feature['key']]);
            $record->name = $feature['name'];
            $record->description = $feature['description'] ?? null;
            $record->sample_rate = $feature['sample_rate'] ?? 1.0;

            if (!$record->exists) {
                $record->enabled = (bool) ($feature['default_enabled'] ?? false);
            }

            $record->save();
        }
    }
}
